<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\UniqueNamaKelasInsensitive;

class UpdateSiswaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
    $routeParam = request()->route('siswa');
        $id = is_object($routeParam) ? ($routeParam->id ?? null) : $routeParam;
        return [
            'nama'  => ['required','string','max:255', new UniqueNamaKelasInsensitive($id)],
            'kelas' => ['required','string','max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.unique' => 'Nama sudah ada di kelas tersebut.',
        ];
    }
}
