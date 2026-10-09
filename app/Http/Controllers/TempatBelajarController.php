<?php

namespace App\Http\Controllers;

use App\Models\MataPelajaran;
use App\Models\TempatBelajar;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TempatBelajarController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'id_mapel' => ['nullable', 'integer', 'exists:mata_pelajaran,id_mapel'],
        ]);

        $places = TempatBelajar::with('mapel')->where('status', 'aktif')
            ->when($filters['q'] ?? null, function ($query, $term) {
                $query->where(function ($query) use ($term) {
                    $query->where('nama_tempat', 'like', '%'.$term.'%')
                        ->orWhere('alamat', 'like', '%'.$term.'%')
                        ->orWhere('karakteristik', 'like', '%'.$term.'%');
                });
            })
            ->when($filters['id_mapel'] ?? null, function ($query, $subject) {
                $query->whereHas('mapel', fn ($mapel) => $mapel->where('mata_pelajaran.id_mapel', $subject));
            })
            ->orderBy('nama_tempat')->paginate(9)->withQueryString();

        return view('public.places.index', [
            'places' => $places,
            'mapel' => MataPelajaran::with('jenjang')->orderBy('nama_mapel')->get(),
        ]);
    }

    public function show(TempatBelajar $tempat): View
    {
        abort_unless($tempat->status === 'aktif', 404);
        $tempat->load(['mapel.jenjang', 'jenjang']);

        return view('public.places.show', compact('tempat'));
    }
}
