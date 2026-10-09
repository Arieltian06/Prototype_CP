<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kriteria;
use App\Models\NilaiKesesuaian;
use App\Models\TempatBelajar;
use Illuminate\Http\Request;

class KesesuaianController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100'], 'page' => ['nullable', 'integer', 'min:1']]);

        return view('admin.kesesuaian.index', [
            'places' => TempatBelajar::with(['mapel.jenjang', 'jenjang', 'penilaian.kriteria'])
                ->when($request->filled('q'), fn ($query) => $query->where('nama_tempat', 'like', '%'.mb_substr((string) $request->query('q'), 0, 100).'%'))
                ->orderBy('nama_tempat')->paginate(10)->withQueryString(),
            'scales' => NilaiKesesuaian::orderBy('nilai')->get(),
            'manualCriteria' => Kriteria::where('sumber_nilai', 'manual')->orderBy('kode_kriteria')->get(),
        ]);
    }
}
