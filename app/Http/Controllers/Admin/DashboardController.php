<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenjangPendidikan;
use App\Models\Kriteria;
use App\Models\MataPelajaran;
use App\Models\PerhitunganSaw;
use App\Models\Sekolah;
use App\Models\TempatBelajar;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('admin.dashboard', [
            'stats' => [
                ['label' => 'Tempat belajar', 'value' => TempatBelajar::count(), 'icon' => 'geo-alt', 'route' => 'admin.tempat.index'],
                ['label' => 'Sekolah', 'value' => Sekolah::count(), 'icon' => 'buildings', 'route' => 'admin.sekolah.index'],
                ['label' => 'Mata pelajaran', 'value' => MataPelajaran::count(), 'icon' => 'book', 'route' => 'admin.mapel.index'],
                ['label' => 'Jenjang pendidikan', 'value' => JenjangPendidikan::count(), 'icon' => 'mortarboard', 'route' => 'admin.jenjang.index'],
                ['label' => 'Kriteria SAW', 'value' => Kriteria::count(), 'icon' => 'sliders', 'route' => 'admin.kriteria.index'],
            ],
            'places' => TempatBelajar::latest()->limit(5)->get(),
            'criteria' => Kriteria::orderBy('kode_kriteria')->get(),
            'historyCount' => PerhitunganSaw::count(),
            'activeCount' => TempatBelajar::where('status', 'aktif')->count(),
        ]);
    }
}
