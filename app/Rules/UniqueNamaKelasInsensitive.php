<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueNamaKelasInsensitive implements ValidationRule
{
    protected ?int $ignoreId;

    public function __construct(?int $ignoreId = null)
    {
        $this->ignoreId = $ignoreId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $nama = $value;
        $kelas = request()->get('kelas');
        if (!$kelas) return;

        $query = DB::table('siswas')
            ->whereRaw('LOWER(nama) = LOWER(?)', [$nama])
            ->whereRaw('LOWER(kelas) = LOWER(?)', [$kelas]);
        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }
        $exists = $query->exists();
        if ($exists) {
            $fail('Nama sudah ada di kelas tersebut.');
        }
    }
}