<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\KartuIdentitasTemplate;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Services\CardIdentityFields;
use App\Services\CardIdentityImage;
use App\Services\CardIdentityPrintPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class KartuIdentitasController extends Controller
{
    private const BATCH_LIMIT = 500;

    public function index(Request $request)
    {
        $this->authorizePrinting();

        $filters = $request->validate([
            'jenis' => ['nullable', 'in:pelajar,guru'],
            'q' => ['nullable', 'string', 'max:100'],
            'kelas' => ['nullable', 'uuid'],
        ]);
        $jenis = $filters['jenis'] ?? 'pelajar';
        $keyword = trim($filters['q'] ?? '');
        $kelasList = Kelas::tampilKelas()
            ->join('kelola_siswa', 'kelola_siswa.kelas_id', '=', 'kelas.id')
            ->where('kelola_siswa.tahun_ajar_id', session('tahun_ajar_id'))
            ->select('kelas.id', 'kelas.nama_kelas', 'tingkat.tingkat', 'jurusan.nama_jurusan')
            ->distinct()
            ->orderBy('tingkat')
            ->orderBy('nama_jurusan')
            ->orderBy('nama_kelas')
            ->get();
        $kelasId = $filters['kelas'] ?? null;
        if ($jenis === 'pelajar' && ! $kelasId) {
            $kelasId = $kelasList->first()?->id;
        }

        if ($jenis === 'pelajar') {
            $people = $this->studentsQuery()
                ->when($kelasId, fn($query) => $query->where('kelola_siswa.kelas_id', $kelasId))
                ->when($keyword !== '', fn($query) => $query->where('siswa.nama_lengkap', 'like', "%{$keyword}%"))
                ->orderBy('siswa.nama_lengkap')
                ->paginate(30)
                ->withQueryString();
        } else {
            $people = Guru::query()
                ->with('namaJabatan:id,nama_jabatan')
                ->when($keyword !== '', fn($query) => $query->where('nama_guru', 'like', "%{$keyword}%"))
                ->orderBy('nama_guru')
                ->paginate(30)
                ->withQueryString();
        }

        $people->getCollection()->each(function ($person) use ($jenis): void {
            $path = $jenis === 'pelajar' ? $person->foto : $person->foto_pegawai;
            $person->setAttribute('kartu_foto_tersedia', $this->findPhoto($jenis, $path) !== null);
        });

        return view('kartu-identitas.index', [
            'jenis' => $jenis,
            'keyword' => $keyword,
            'kelasId' => $kelasId,
            'kelasList' => $kelasList,
            'people' => $people,
            'templates' => $this->activeTemplates(),
        ]);
    }

    public function preview(Request $request)
    {
        $this->authorizePrinting();
        $data = $request->validate([
            'jenis' => ['required', 'in:pelajar,guru'],
            'ids' => ['required', 'array', 'min:1', 'max:' . self::BATCH_LIMIT],
            'ids.*' => ['required', 'uuid', 'distinct'],
        ]);

        return $this->showPreview($data['jenis'], $data['ids']);
    }

    public function generateAll(Request $request)
    {
        $this->authorizePrinting();
        $data = $request->validate([
            'jenis' => ['required', 'in:pelajar,guru'],
            'kelas' => ['nullable', 'uuid'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $query = $data['jenis'] === 'pelajar' ? $this->studentsQuery() : Guru::query()->with('namaJabatan:id,nama_jabatan');
        if ($data['jenis'] === 'pelajar') {
            $query->when($data['kelas'] ?? null, fn($query, $kelas) => $query->where('kelola_siswa.kelas_id', $kelas));
        }

        $query->when(($data['q'] ?? '') !== '', function ($query) use ($data) {
            $column = $data['jenis'] === 'pelajar' ? 'siswa.nama_lengkap' : 'nama_guru';
            $query->where($column, 'like', '%' . trim($data['q']) . '%');
        });

        $idColumn = $data['jenis'] === 'pelajar' ? 'siswa.id' : 'id';
        $nameColumn = $data['jenis'] === 'pelajar' ? 'siswa.nama_lengkap' : 'nama_guru';
        $ids = $query->orderBy($nameColumn)->limit(self::BATCH_LIMIT + 1)->pluck($idColumn)->all();
        abort_if(count($ids) > self::BATCH_LIMIT, 422, 'Jumlah melebihi 500 kartu. Persempit filter kelas atau pencarian.');
        abort_if(! $ids, 422, 'Tidak ada data yang cocok untuk dibuat kartunya.');

        return $this->showPreview($data['jenis'], $ids);
    }

    private function showPreview(string $jenis, array $ids)
    {
        if ($jenis === 'pelajar') {
            $peopleById = $this->studentsQuery()->whereIn('siswa.id', $ids)->get()->keyBy('id');
        } else {
            $peopleById = Guru::query()->with('namaJabatan:id,nama_jabatan')->whereIn('id', $ids)->get()->keyBy('id');
        }

        abort_unless($peopleById->count() === count($ids), 404);
        $people = collect($ids)->map(fn(string $id) => $peopleById->get($id));

        $people->each(function ($person) use ($jenis): void {
            $identity = CardIdentityFields::map($jenis, $person);
            $path = $identity['photo'];
            $person->setAttribute('kartu_identitas', $identity);
            $person->setAttribute('kartu_foto_url', $this->photoUrl($jenis, $person->id, $path));
            $person->setAttribute('kartu_foto_tersedia', $person->getAttribute('kartu_foto_url') !== null);
            $qrPayload = $jenis === 'pelajar'
                ? URL::signedRoute('kartu-identitas.siswa.detail', ['siswa' => $person->id])
                : null;
            $person->setAttribute('kartu_qr', $qrPayload ? base64_encode(QrCode::format('svg')->size(240)->margin(1)->generate($qrPayload)) : null);
        });

        $templates = $this->activeTemplates();

        return view('kartu-identitas.preview', [
            'jenis' => $jenis,
            'people' => $people,
            'peopleById' => $peopleById,
            'templates' => $templates,
            'sides' => CardIdentityPrintPlan::sides($jenis, isset($templates[$jenis . '.belakang'])),
            'printSheets' => CardIdentityPrintPlan::forSheets(
                $ids,
                $jenis,
                isset($templates[$jenis . '.belakang']),
            ),
        ]);
    }

    public function studentDetail(Siswa $siswa)
    {
        $siswa->load([
            'kelas.tingkat',
            'kelas.jurusan',
            'keteranganKeterima.kelas.tingkat',
            'keteranganKeterima.kelas.jurusan',
        ]);
        $kelasSiswa = $siswa->kelas ?? $siswa->keteranganKeterima?->kelas;

        return view('kartu-identitas.siswa-detail', compact('siswa', 'kelasSiswa'));
    }

    public function image(KartuIdentitasTemplate $template): Response
    {
        $this->authorizePrinting();
        if (! $template->aktif) {
            Gate::authorize('admin');
        }

        abort_unless(Storage::disk($template->disk)->exists($template->path), 404);

        return $this->privateImageResponse($template->disk, $template->path);
    }

    public function photo(string $jenis, string $id): Response
    {
        $this->authorizePrinting();
        abort_unless(in_array($jenis, ['pelajar', 'guru'], true), 404);

        // The photo endpoint only needs the stored photo path. Avoid repeating
        // the class/year joins used by the card list for every <img> request;
        // browsers load many card photos concurrently during generation.
        $person = $jenis === 'pelajar'
            ? Siswa::query()->findOrFail($id)
            : Guru::query()->findOrFail($id);
        $path = $jenis === 'pelajar' ? $person->foto : $person->foto_pegawai;

        $photo = $this->findPhoto($jenis, $path);
        abort_unless($photo, 404);

        return $this->privateImageResponse($photo['disk'], $photo['path']);
    }

    public function templates()
    {
        Gate::authorize('admin');
        $templates = KartuIdentitasTemplate::query()->orderBy('jenis')->orderBy('sisi')->orderByDesc('aktif')->latest('id')->get();

        return view('kartu-identitas.templates', ['templates' => $templates]);
    }

    public function uploadTemplate(Request $request, string $jenis, string $sisi)
    {
        Gate::authorize('admin');
        abort_unless($this->validSlot($jenis, $sisi), 404);

        $validator = Validator::make($request->all(), [
            'template' => ['required', 'file', 'max:5120', 'mimes:jpg,jpeg,png', 'extensions:jpg,jpeg,png'],
        ], [
            'template.required' => 'Pilih file template terlebih dahulu.',
            'template.max' => 'Ukuran template maksimal 5 MB.',
            'template.mimes' => 'Format gambar harus JPG, JPEG, atau PNG.',
            'template.extensions' => 'Ekstensi file harus JPG, JPEG, atau PNG.',
        ]);
        $validator->after(function ($validator) use ($request): void {
            $file = $request->file('template');
            if (! $file || ! $file->isValid()) {
                return;
            }

            $extension = strtolower($file->getClientOriginalExtension());
            if ($error = CardIdentityImage::validationError($file->getRealPath(), $extension, (int) $file->getSize())) {
                $validator->errors()->add('template', $error);
            }
        });
        $validated = $validator->validate();

        $file = $validated['template'];
        $extension = strtolower($file->getClientOriginalExtension());
        $path = "card-templates/{$jenis}-{$sisi}/" . Str::uuid() . ".{$extension}";
        if (! Storage::disk('local')->put($path, file_get_contents($file->getRealPath()))) {
            return back()->withErrors(['template' => 'Template tidak dapat disimpan. Template yang sedang aktif tetap digunakan.']);
        }

        try {
            $pending = KartuIdentitasTemplate::create([
                'jenis' => $jenis,
                'sisi' => $sisi,
                'disk' => 'local',
                'path' => $path,
                'aktif' => false,
                'diunggah_oleh' => $request->user()?->id,
            ]);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        return to_route('kartu-identitas.template.preview', $pending)->with('sukses', 'Template siap dipratinjau. Template aktif belum diubah.');
    }

    public function previewTemplate(KartuIdentitasTemplate $template)
    {
        Gate::authorize('admin');
        if (
            ! in_array($template->disk, ['local', 'card_assets'], true)
            || ! Storage::disk($template->disk)->exists($template->path)
        ) {
            return to_route('kartu-identitas.templates')->withErrors([
                'template' => 'File template tidak ditemukan. Unggah ulang template ini lalu coba lagi.',
            ]);
        }

        return view('kartu-identitas.template-preview', [
            'template' => $template,
            'active' => KartuIdentitasTemplate::query()->where('jenis', $template->jenis)->where('sisi', $template->sisi)->where('aktif', true)->first(),
        ]);
    }

    public function activateTemplate(string $templateId)
    {
        Gate::authorize('admin');
        $template = KartuIdentitasTemplate::find($templateId);
        if (
            ! $template || ! $this->validSlot($template->jenis, $template->sisi)
            || ! in_array($template->disk, ['local', 'card_assets'], true)
            || ! Storage::disk($template->disk)->exists($template->path)
        ) {
            return to_route('kartu-identitas.templates')->withErrors([
                'template' => 'Template tidak ditemukan atau file-nya sudah tidak tersedia. Unggah ulang template lalu coba aktifkan kembali.',
            ]);
        }

        $result = DB::transaction(function () use ($templateId) {
            $pending = KartuIdentitasTemplate::query()->whereKey($templateId)->lockForUpdate()->first();
            if (! $pending) {
                return ['error' => 'Template tidak ditemukan. Muat ulang daftar template lalu coba kembali.'];
            }
            if ($pending->aktif) {
                return ['error' => 'Template ini sudah aktif.'];
            }
            $old = KartuIdentitasTemplate::query()
                ->where('jenis', $pending->jenis)
                ->where('sisi', $pending->sisi)
                ->where('aktif', true)
                ->lockForUpdate()
                ->first();

            KartuIdentitasTemplate::query()
                ->where('jenis', $pending->jenis)
                ->where('sisi', $pending->sisi)
                ->update(['aktif' => false]);
            $pending->update(['aktif' => true]);

            return ['old' => $old];
        });

        if (isset($result['error'])) {
            return to_route('kartu-identitas.templates')->withErrors(['template' => $result['error']]);
        }

        $oldTemplate = $result['old'];
        if ($oldTemplate) {
            if ($oldTemplate->disk === 'local') {
                Storage::disk('local')->delete($oldTemplate->path);
            }
            $oldTemplate->delete();
        }

        return to_route('kartu-identitas.templates')->with('sukses', 'Template berhasil diaktifkan.');
    }

    public function confirmActivateTemplate(string $templateId)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }
        abort_unless(Auth::user()->role === 'admin', 403);

        Gate::authorize('admin');
        $template = KartuIdentitasTemplate::find($templateId);
        if (! $template) {
            return to_route('kartu-identitas.templates')->withErrors(['template' => 'Template tidak ditemukan.']);
        }

        return $this->previewTemplate($template);
    }

    public function deletePendingTemplate(KartuIdentitasTemplate $template)
    {
        Gate::authorize('admin');
        [$disk, $path] = DB::transaction(function () use ($template): array {
            $pending = KartuIdentitasTemplate::query()->whereKey($template->id)->lockForUpdate()->firstOrFail();
            abort_if($pending->aktif, 422, 'Template aktif tidak dapat dihapus. Aktifkan penggantinya terlebih dahulu.');
            $disk = $pending->disk;
            $path = $pending->path;
            $pending->delete();

            return [$disk, $path];
        });
        if ($disk === 'local') {
            Storage::disk('local')->delete($path);
        }

        return to_route('kartu-identitas.templates')->with('sukses', 'Template pratinjau dihapus.');
    }

    private function authorizePrinting(): void
    {
        Gate::authorize('staff_dan_admin');
    }

    private function validSlot(string $jenis, string $sisi): bool
    {
        return in_array($jenis, ['pelajar', 'guru'], true) && in_array($sisi, ['depan', 'belakang'], true);
    }

    private function activeTemplates(): array
    {
        return KartuIdentitasTemplate::query()->where('aktif', true)->get()->keyBy(fn($item) => "{$item->jenis}.{$item->sisi}")->all();
    }

    private function studentsQuery()
    {
        return Siswa::query()
            ->join('kelola_siswa', 'kelola_siswa.siswa_id', '=', 'siswa.id')
            ->join('kelas', 'kelas.id', '=', 'kelola_siswa.kelas_id')
            ->join('tingkat', 'tingkat.id', '=', 'kelas.tingkat_id')
            ->leftJoin('jurusan', 'jurusan.id', '=', 'kelas.jurusan_id')
            ->where('kelola_siswa.tahun_ajar_id', session('tahun_ajar_id'))
            ->select('siswa.*', 'kelas.nama_kelas as kartu_kelas', 'jurusan.nama_jurusan as kartu_jurusan', 'tingkat.tingkat as kartu_tingkat');
    }

    private function photoUrl(string $jenis, string $id, ?string $path): ?string
    {
        if (! $this->findPhoto($jenis, $path)) {
            return null;
        }

        return route('kartu-identitas.foto', ['jenis' => $jenis, 'id' => $id]);
    }

    private function privateImageResponse(string $disk, string $path): Response
    {
        $storage = Storage::disk($disk);
        $mime = $storage->mimeType($path) ?: 'application/octet-stream';
        abort_unless(in_array($mime, ['image/jpeg', 'image/png', 'image/gif'], true), 404);

        // Student photos can be large. Stream them instead of loading each file
        // fully into PHP memory, which can exhaust memory on shared hosting.
        $stream = $storage->readStream($path);
        abort_unless(is_resource($stream), 404, 'File gambar tidak dapat dibaca.');

        return response()->stream(function () use ($stream): void {
            try {
                fpassthru($stream);
            } finally {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function findPhoto(string $jenis, ?string $path): ?array
    {
        if (! is_string($path) || str_contains($path, '..') || str_starts_with($path, '/') || str_contains($path, '\\')) {
            return null;
        }
        $path = ltrim($path, '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }
        $prefixes = $jenis === 'pelajar'
            ? ['image/siswa/awal/', 'spmb/foto/', 'spmb/berkas/foto/', 'image/']
            : ['image/tenaga-pendidik/', 'image/'];
        foreach (['public', 'local'] as $disk) {
            foreach ($prefixes as $prefix) {
                if (
                    str_starts_with($path, $prefix) && Storage::disk($disk)->exists($path)
                    && in_array(Storage::disk($disk)->mimeType($path), ['image/jpeg', 'image/png', 'image/gif'], true)
                ) {
                    return ['disk' => $disk, 'path' => $path];
                }
            }
        }

        return null;
    }
}
