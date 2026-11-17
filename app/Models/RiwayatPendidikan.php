<?php

namespace App\Models;

use App\Models\Pendidikan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class RiwayatPendidikan extends Model
{
    /// tabel akan terisi secara otomatis dengan data acak
    use HasFactory;

    // ID akan secara otomatis berisi kode acak
    use HasUuids;

    //Nama tabel adalah riwayat pendidikan
    protected $table = 'riwayat_pendidikan';

    // Kolom yang tidak boleh di isi
    protected $guarded = ['id'];

    public function getTanggalIjazahAttribute($value)
    {
        return is_numeric($value) && (float) $value >= 1
            ? ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d')
            : $value;
    }

    public function scopePendidikan()
    {
        return $this->belongsTo(Pendidikan::class, 'pendidikan_id');
    }
}
