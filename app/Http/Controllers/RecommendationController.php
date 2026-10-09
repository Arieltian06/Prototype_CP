<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecommendationRequest;
use App\Models\JenjangPendidikan;
use App\Models\MataPelajaran;
use App\Models\PerhitunganSaw;
use App\Models\Sekolah;
use App\Services\RecommendationService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class RecommendationController extends Controller
{
    public function index(): View
    {
        return view('public.recommendation.index', [
            'jenjang' => JenjangPendidikan::with(['mapel', 'sekolah'])->get(),
            'mapel' => MataPelajaran::orderBy('nama_mapel')->get(),
            'sekolah' => Sekolah::orderBy('nama_sekolah')->get(),
        ]);
    }

    public function store(RecommendationRequest $request, RecommendationService $service): RedirectResponse
    {
        $data = $request->validated();
        $jenjang = JenjangPendidikan::findOrFail($data['id_jenjang']);
        $mapel = MataPelajaran::findOrFail($data['id_mapel']);
        $sekolah = Sekolah::findOrFail($data['id_sekolah']);
        $origin = $request->boolean('use_location') ? [
            'latitude' => (float) $data['latitude'],
            'longitude' => (float) $data['longitude'],
            'label' => 'Lokasi perangkat',
        ] : null;

        try {
            $hasil = $service->recommend($jenjang, $mapel, $sekolah, $origin);
            $perhitungan = PerhitunganSaw::create([
                'input' => [
                    'id_jenjang' => $jenjang->getKey(), 'jenjang' => $jenjang->nama_jenjang,
                    'id_mapel' => $mapel->getKey(), 'mapel' => $mapel->nama_mapel,
                    'id_sekolah' => $sekolah->getKey(), 'sekolah' => $sekolah->nama_sekolah,
                    'use_location' => $origin !== null,
                ],
                'hasil' => $hasil,
            ]);
        } catch (DomainException $exception) {
            return back()->withInput()->withErrors(['recommendation' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->withErrors(['recommendation' => 'Perhitungan belum dapat diselesaikan. Silakan coba kembali atau hubungi admin.']);
        }

        return redirect()->route('recommendation.show', $perhitungan);
    }

    public function show(PerhitunganSaw $perhitungan): View
    {
        return view('public.recommendation.results', compact('perhitungan'));
    }
}
