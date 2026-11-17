<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PresensiExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private readonly Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows->map(fn(array $row) => [
            $row['tanggal'],
            $row['guru'],
            $row['jam_masuk'] ?? '-',
            $row['jam_pulang'] ?? '-',
            $row['status'],
            $row['keterangan'] ?? '-',
        ]);
    }

    public function headings(): array
    {
        return ['Tanggal', 'Guru', 'Jam Masuk', 'Jam Pulang', 'Status', 'Keterangan'];
    }
}
