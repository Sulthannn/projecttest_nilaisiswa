<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{

    public function __invoke(Request $r): Response
    {
        $kelas = $r->query('kelas');
        $mapel = $r->query('mapel');
        $search = $r->query('q');
        $perPageQ = (int) $r->query('per_page', 10);
        $allowed = [10, 25, 50, 100];
        $perPage = in_array($perPageQ, $allowed, true) ? $perPageQ : 10;

        $query = Nilai::with('siswa');

        if ($kelas) {
            $query->where('kelas', $kelas);
        }
        if ($mapel) {
            $query->where('mapel', $mapel);
        }
        if ($search) {
            $query->whereHas('siswa', function ($q) use ($search) {
                $driver = $q->getModel()->getConnection()->getDriverName();
                if ($driver === 'pgsql') {
                    $q->where('nama', 'ILIKE', "%{$search}%");
                } else {
                    $q->whereRaw('LOWER(nama) LIKE ?', ['%' . strtolower($search) . '%']);
                }
            });
        }

        $data = $query->orderBy('id')->paginate($perPage)->withQueryString();

        $listKelas = Siswa::query()->distinct()->orderBy('kelas')->pluck('kelas');
        $listMapel = Nilai::query()->distinct()->orderBy('mapel')->pluck('mapel');

        return Inertia::render('Dashboard/Index', [
            'data' => $data,
            'rowStart' => ($data->currentPage() - 1) * $data->perPage(),
            'listKelas' => $listKelas,
            'listMapel' => $listMapel,
            'filters' => [
                'kelas' => $kelas,
                'mapel' => $mapel,
                'q' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }
}