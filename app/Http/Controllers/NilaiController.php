<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNilaiRequest;
use App\Http\Requests\UpdateNilaiRequest;
use App\Models\Nilai;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Imports\NilaiImport;
use App\Exports\NilaiExport;

class NilaiController extends Controller
{

    public function store(StoreNilaiRequest $req): RedirectResponse
    {
        Nilai::create($req->validated());
        return back()->with('success', 'Nilai ditambahkan');
    }

    public function update(UpdateNilaiRequest $req, Nilai $nilai): RedirectResponse
    {
        $nilai->update($req->validated());
        return back()->with('success', 'Nilai diupdate');
    }

    public function destroy(Nilai $nilai): RedirectResponse
    {
        $nilai->delete();
        return back()->with('success', 'Nilai dihapus');
    }

    public function import(Request $r): RedirectResponse
    {
        $r->validate(['file' => 'required|file|mimes:xlsx,xls']);
        Excel::import(new NilaiImport, $r->file('file'));
        return back()->with('success', 'Import berhasil');
    }

    public function export(): BinaryFileResponse
    {
        return Excel::download(new NilaiExport, 'nilai.xlsx');
    }
}