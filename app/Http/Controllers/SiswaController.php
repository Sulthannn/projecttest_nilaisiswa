<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSiswaRequest;
use App\Http\Requests\UpdateSiswaRequest;
use App\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SiswaController extends Controller
{
    public function index(Request $r): Response
    {
        $q = $r->query('q');
        $perPageQ = (int)$r->query('per_page', 10);
        $allowed = [10,25,50,100];
        $perPage = in_array($perPageQ, $allowed, true) ? $perPageQ : 10;

        $query = Siswa::query();
        if ($q) {
            $driver = Siswa::query()->getModel()->getConnection()->getDriverName();
            if ($driver === 'pgsql') {
                $query->where('nama', 'ILIKE', "%$q%");
            } else {
                $query->whereRaw('LOWER(nama) LIKE ?', ['%'.strtolower($q).'%']);
            }
        }

        $data = $query->orderBy('id')->paginate($perPage)->withQueryString();

        return Inertia::render('Siswa/Index', [
            'data' => $data,
            'filters' => [
                'q' => $q,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function store(StoreSiswaRequest $req): RedirectResponse
    {
        Siswa::create($req->validated());
        return back()->with('success', 'Siswa ditambahkan');
    }

    public function update(UpdateSiswaRequest $req, Siswa $siswa): RedirectResponse
    {
        $siswa->update($req->validated());
        return back()->with('success', 'Siswa diupdate');
    }

    public function destroy(Siswa $siswa): RedirectResponse
    {
        $siswa->delete();
        return back()->with('success', 'Siswa dihapus');
    }

    public function options(Request $r)
    {
        $q = $r->query('q');
        $builder = Siswa::query()->select('id','nama','kelas')->orderBy('nama');
        if($q){
            $driver = Siswa::query()->getModel()->getConnection()->getDriverName();
            if($driver==='pgsql'){
                $builder->where('nama','ILIKE',"%$q%");
            } else {
                $builder->whereRaw('LOWER(nama) LIKE ?', ['%'.strtolower($q).'%']);
            }
        }
        return response()->json($builder->limit(20)->get());
    }
}