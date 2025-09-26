<?php

namespace App\Imports;

use App\Models\Nilai;
use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class NilaiImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Header: no, nama, kelas, mapel, nilai
        $siswa = Siswa::firstOrCreate([
            'nama' => $row['nama'],
            'kelas'=> $row['kelas'],
        ]);

        return new Nilai([
            'siswa_id' => $siswa->id,
            'kelas'    => $row['kelas'],
            'mapel'    => $row['mapel'],
            'nilai'    => (int)$row['nilai'],
        ]);
    }
}