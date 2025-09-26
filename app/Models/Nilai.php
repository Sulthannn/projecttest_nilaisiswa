<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Nilai extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function siswa() { return $this->belongsTo(Siswa::class); }
}