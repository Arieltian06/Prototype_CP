<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TempatRequest;
use App\Models\JenjangPendidikan;
use App\Models\Kriteria;
use App\Models\MataPelajaran;
use App\Models\NilaiKesesuaian;
use App\Models\TempatBelajar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class TempatBelajarController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', 'in:aktif,nonaktif'], 'page' => ['nullable', 'integer', 'min:1']]);
        $query = TempatBelajar::with('mapel')->withCount(['mapel', 'jenjang']);
        if ($search = trim((string) $request->query('q', ''))) {
            $query->where(function ($query) use ($search) {
                $query->where('nama_tempat', 'like', '%'.mb_substr($search, 0, 100).'%')->orWhere('alamat', 'like', '%'.mb_substr($search, 0, 100).'%');
            });
        }
        if (in_array($request->query('status'), ['aktif', 'nonaktif'], true)) {
            $query->where('status', $request->query('status'));
        }

        return view('admin.tempat.index', ['places' => $query->latest()->paginate(10)->withQueryString()]);
    }

    private function form(TempatBelajar $tempat)
    {
        $tempat->load(['mapel', 'jenjang', 'penilaian']);

        return view('admin.tempat.form', [
            'place' => $tempat,
            'levels' => JenjangPendidikan::orderBy('id_jenjang')->get(),
            'subjects' => MataPelajaran::with('jenjang')->orderBy('id_jenjang')->orderBy('nama_mapel')->get(),
            'scales' => NilaiKesesuaian::orderBy('nilai')->get(),
            'manualCriteria' => Kriteria::where('sumber_nilai', 'manual')->orderBy('kode_kriteria')->get(),
        ]);
    }

    public function create()
    {
        return $this->form(new TempatBelajar(['status' => 'aktif', 'is_prototype' => true, 'biaya' => 0]));
    }

    public function store(TempatRequest $request)
    {
        $place = $this->save($request, new TempatBelajar);

        return redirect()->route('admin.tempat.show', $place)->with('success', 'Tempat belajar berhasil ditambahkan.');
    }

    public function show(TempatBelajar $tempat)
    {
        return view('admin.tempat.show', ['place' => $tempat->load(['mapel.jenjang', 'jenjang', 'penilaian.kriteria'])]);
    }

    public function edit(TempatBelajar $tempat)
    {
        return $this->form($tempat);
    }

    public function update(TempatRequest $request, TempatBelajar $tempat)
    {
        $this->save($request, $tempat);

        return redirect()->route('admin.tempat.show', $tempat)->with('success', 'Tempat belajar dan nilai kesesuaiannya berhasil diperbarui.');
    }

    private function save(TempatRequest $request, TempatBelajar $place): TempatBelajar
    {
        $data = $request->safe()->except(['foto', 'hapus_foto', 'mapel', 'jenjang', 'penilaian']);
        $oldPhoto = $place->foto;
        $newPhoto = null;
        if ($request->hasFile('foto')) {
            $newPhoto = $request->file('foto')->store('tempat', 'public');
            $data['foto'] = $newPhoto;
        } elseif ($request->boolean('hapus_foto')) {
            $data['foto'] = null;
        }
        try {
            DB::transaction(function () use ($request, $place, $data) {
                $place->fill($data)->save();
                foreach (['mapel', 'jenjang'] as $relation) {
                    $scores = [];
                    foreach ($request->validated($relation, []) as $id => $score) {
                        if ($score !== null && $score !== '') {
                            $scores[$id] = ['nilai_kesesuaian' => $score];
                        }
                    }
                    $place->{$relation}()->sync($scores);
                }
                $manual = array_filter($request->validated('penilaian', []), fn ($value) => $value !== null && $value !== '');
                $place->penilaian()->whereNotIn('id_kriteria', array_keys($manual))->delete();
                foreach ($manual as $id => $value) {
                    $place->penilaian()->updateOrCreate(['id_kriteria' => $id], ['nilai' => $value]);
                }
            });
        } catch (Throwable $exception) {
            $this->deletePhoto($newPhoto);
            throw $exception;
        }
        if ($oldPhoto && ($newPhoto || $request->boolean('hapus_foto'))) {
            $this->deletePhoto($oldPhoto);
        }

        return $place;
    }

    private function deletePhoto(?string $path): void
    {
        if ($path && str_starts_with($path, 'tempat/') && ! str_contains($path, '..')) {
            Storage::disk('public')->delete($path);
        }
    }

    public function destroy(TempatBelajar $tempat)
    {
        $photo = $tempat->foto;
        DB::transaction(fn () => $tempat->delete());
        $this->deletePhoto($photo);

        return redirect()->route('admin.tempat.index')->with('success', 'Tempat belajar berhasil dihapus. Riwayat perhitungan tetap tersimpan.');
    }
}
