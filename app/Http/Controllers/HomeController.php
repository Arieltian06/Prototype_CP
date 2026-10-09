<?php

namespace App\Http\Controllers;

use App\Models\JenjangPendidikan;
use App\Models\MataPelajaran;
use App\Models\Sekolah;
use App\Models\TempatBelajar;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('public.home', [
            'counts' => [
                'tempat' => TempatBelajar::where('status', 'aktif')->count(),
                'mapel' => MataPelajaran::count(),
                'sekolah' => Sekolah::count(),
                'jenjang' => JenjangPendidikan::count(),
            ],
            'places' => TempatBelajar::with('mapel')->where('status', 'aktif')->orderBy('id_tempat')->take(3)->get(),
            'subjects' => MataPelajaran::with('jenjang')
                ->withCount(['tempat' => fn ($query) => $query->where('status', 'aktif')])
                ->whereHas('tempat', fn ($query) => $query->where('status', 'aktif'))
                ->orderBy('nama_mapel')->get()->unique('nama_mapel')->take(5),
        ]);
    }

    public function about(): View
    {
        return view('public.about');
    }
}
