<?php

namespace App\Exports;

use App\Models\Nilai;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NilaiExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Nilai::with('siswa:id,nama,kelas')
            ->get()
            ->map(fn($n) => [
                'no'     => $n->id,
                'nama'   => $n->siswa->nama,
                'kelas'  => $n->kelas,
                'mapel'  => $n->mapel,
                'nilai'  => $n->nilai,
            ]);
    }
    public function headings(): array
    {
        return ['no','nama','kelas','mapel','nilai'];
    }
}