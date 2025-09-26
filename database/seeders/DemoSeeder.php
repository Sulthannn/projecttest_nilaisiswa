<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Nilai;
use App\Models\Siswa;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $s1 = Siswa::create(['nama'=>'Andi','kelas'=>'7A']);
        $s2 = Siswa::create(['nama'=>'Budi','kelas'=>'7A']);
        $s3 = Siswa::create(['nama'=>'Citra','kelas'=>'7B']);

        $rows = [
            ['siswa_id'=>$s1->id,'kelas'=>'7A','mapel'=>'Matematika','nilai'=>80],
            ['siswa_id'=>$s1->id,'kelas'=>'7A','mapel'=>'Bahasa','nilai'=>70],
            ['siswa_id'=>$s2->id,'kelas'=>'7A','mapel'=>'Matematika','nilai'=>60],
            ['siswa_id'=>$s2->id,'kelas'=>'7A','mapel'=>'Bahasa','nilai'=>75],
            ['siswa_id'=>$s3->id,'kelas'=>'7B','mapel'=>'Matematika','nilai'=>90],
            ['siswa_id'=>$s3->id,'kelas'=>'7B','mapel'=>'Bahasa','nilai'=>85],
        ];
        foreach ($rows as $r) { Nilai::create($r); }
    }
}