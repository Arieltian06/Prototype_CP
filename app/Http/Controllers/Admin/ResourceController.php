<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResourceRequest;
use App\Models\JenjangPendidikan;
use App\Models\Kriteria;
use App\Models\MataPelajaran;
use App\Models\NilaiKesesuaian;
use App\Models\Sekolah;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResourceController extends Controller
{
    protected function definition(Request $request): array
    {
        $key = explode('.', $request->route()->getName())[1];
        $definitions = [
            'jenjang' => ['title' => 'Jenjang Pendidikan', 'singular' => 'Jenjang', 'model' => JenjangPendidikan::class, 'name' => 'nama_jenjang', 'icon' => 'mortarboard', 'description' => 'Kelola jenjang untuk menghubungkan siswa dengan pembelajaran yang sesuai.', 'fields' => [
                ['key' => 'nama_jenjang', 'label' => 'Nama jenjang', 'type' => 'text', 'placeholder' => 'Contoh: SD, SMP, SMA', 'max' => 50],
            ]],
            'mapel' => ['title' => 'Mata Pelajaran', 'singular' => 'Mata Pelajaran', 'model' => MataPelajaran::class, 'name' => 'nama_mapel', 'icon' => 'book', 'description' => 'Susun mata pelajaran sesuai jenjang pendidikan.', 'fields' => [
                ['key' => 'nama_mapel', 'label' => 'Nama mata pelajaran', 'type' => 'text', 'placeholder' => 'Contoh: Ilmu Pengetahuan Alam', 'max' => 100],
                ['key' => 'id_jenjang', 'label' => 'Jenjang pendidikan', 'type' => 'jenjang'],
            ]],
            'sekolah' => ['title' => 'Sekolah', 'singular' => 'Sekolah', 'model' => Sekolah::class, 'name' => 'nama_sekolah', 'icon' => 'buildings', 'description' => 'Koordinat sekolah menjadi lokasi asal perhitungan jarak rekomendasi.', 'fields' => [
                ['key' => 'nama_sekolah', 'label' => 'Nama sekolah', 'type' => 'text', 'max' => 150],
                ['key' => 'id_jenjang', 'label' => 'Jenjang pendidikan', 'type' => 'jenjang'],
                ['key' => 'alamat', 'label' => 'Alamat sekolah', 'type' => 'textarea', 'max' => 1000],
                ['key' => 'latitude', 'label' => 'Latitude', 'type' => 'number', 'min' => -90, 'max' => 90, 'step' => 'any', 'placeholder' => 'Contoh: -3.8000'],
                ['key' => 'longitude', 'label' => 'Longitude', 'type' => 'number', 'min' => -180, 'max' => 180, 'step' => 'any', 'placeholder' => 'Contoh: 102.2600'],
            ]],
            'kriteria' => ['title' => 'Kriteria & Bobot SAW', 'singular' => 'Kriteria', 'model' => Kriteria::class, 'name' => 'nama_kriteria', 'icon' => 'sliders', 'description' => 'Bobot dinormalisasi otomatis terhadap jumlah seluruh bobot saat rekomendasi dihitung.', 'fields' => [
                ['key' => 'kode_kriteria', 'label' => 'Kode kriteria', 'type' => 'text', 'placeholder' => 'Contoh: C4', 'max' => 20],
                ['key' => 'nama_kriteria', 'label' => 'Nama kriteria', 'type' => 'text', 'max' => 150],
                ['key' => 'jenis_kriteria', 'label' => 'Jenis kriteria', 'type' => 'select', 'options' => ['benefit' => 'Benefit — nilai lebih besar lebih baik', 'cost' => 'Cost — nilai lebih kecil lebih baik']],
                ['key' => 'bobot', 'label' => 'Bobot awal', 'type' => 'number', 'min' => 0, 'max' => 100000, 'step' => '0.0001', 'placeholder' => 'Contoh: 0.30'],
                ['key' => 'sumber_nilai', 'label' => 'Sumber nilai', 'type' => 'select', 'options' => ['manual' => 'Nilai manual per tempat', 'jenjang' => 'Kesesuaian jenjang', 'mapel' => 'Kesesuaian mata pelajaran', 'jarak' => 'Jarak Haversine (km)']],
            ]],
            'skala' => ['title' => 'Skala Kesesuaian', 'singular' => 'Skala', 'model' => NilaiKesesuaian::class, 'name' => 'label', 'icon' => 'bar-chart-steps', 'description' => 'Skala ini digunakan pada relasi tempat dengan jenjang dan mata pelajaran.', 'fields' => [
                ['key' => 'nilai', 'label' => 'Nilai skala', 'type' => 'number', 'min' => '0.0001', 'max' => 100000, 'step' => '0.0001'],
                ['key' => 'label', 'label' => 'Label kesesuaian', 'type' => 'text', 'max' => 100, 'placeholder' => 'Contoh: Sangat sesuai'],
            ]],
        ];
        abort_unless(isset($definitions[$key]), 404);

        return $definitions[$key] + ['key' => $key, 'route' => 'admin.'.$key];
    }

    public function index(Request $request)
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100'], 'page' => ['nullable', 'integer', 'min:1']]);
        $definition = $this->definition($request);
        $query = $definition['model']::query();
        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where($definition['name'], 'like', '%'.mb_substr($search, 0, 100).'%');
        }
        if (in_array($definition['key'], ['mapel', 'sekolah'])) {
            $query->with('jenjang');
        }

        return view('admin.master.index', [
            'definition' => $definition,
            'items' => $query->orderBy($definition['key'] === 'kriteria' ? 'kode_kriteria' : ($definition['key'] === 'skala' ? 'nilai' : $definition['name']))->paginate(12)->withQueryString(),
            'weightTotal' => $definition['key'] === 'kriteria' ? Kriteria::sum('bobot') : null,
        ]);
    }

    public function create(Request $request)
    {
        $definition = $this->definition($request);

        return view('admin.master.form', ['definition' => $definition, 'item' => new $definition['model'], 'levels' => JenjangPendidikan::orderBy('nama_jenjang')->get()]);
    }

    public function store(ResourceRequest $request)
    {
        $definition = $this->definition($request);
        $definition['model']::create($request->validated());

        return redirect()->route($definition['route'].'.index')->with('success', $definition['singular'].' berhasil ditambahkan.');
    }

    public function show(Request $request, string $id)
    {
        $definition = $this->definition($request);

        return view('admin.master.show', ['definition' => $definition, 'item' => $definition['model']::findOrFail($id)]);
    }

    public function edit(Request $request, string $id)
    {
        $definition = $this->definition($request);

        return view('admin.master.form', ['definition' => $definition, 'item' => $definition['model']::findOrFail($id), 'levels' => JenjangPendidikan::orderBy('nama_jenjang')->get()]);
    }

    public function update(ResourceRequest $request, string $id)
    {
        $definition = $this->definition($request);
        $item = $definition['model']::findOrFail($id);
        DB::transaction(function () use ($definition, $item, $request) {
            if ($definition['key'] === 'skala' && (float) $item->nilai !== (float) $request->validated('nilai')) {
                foreach (['tempat_mapel', 'tempat_jenjang'] as $table) {
                    DB::table($table)->where('nilai_kesesuaian', $item->nilai)->update(['nilai_kesesuaian' => $request->validated('nilai'), 'updated_at' => now()]);
                }
            }
            $item->update($request->validated());
        });

        return redirect()->route($definition['route'].'.index')->with('success', $definition['singular'].' berhasil diperbarui.');
    }

    public function destroy(Request $request, string $id)
    {
        $definition = $this->definition($request);
        $item = $definition['model']::findOrFail($id);
        if ($definition['key'] === 'jenjang' && ($item->mapel()->exists() || $item->sekolah()->exists() || DB::table('tempat_jenjang')->where('id_jenjang', $id)->exists())) {
            return back()->with('error', 'Jenjang masih digunakan oleh sekolah, mata pelajaran, atau penilaian tempat. Pindahkan atau hapus relasinya terlebih dahulu.');
        }
        if ($definition['key'] === 'kriteria' && Kriteria::where('id_kriteria', '!=', $id)->sum('bobot') <= 0) {
            return back()->with('error', 'Kriteria tidak dapat dihapus karena jumlah bobot yang tersisa harus lebih dari nol.');
        }
        if ($definition['key'] === 'skala') {
            foreach (['tempat_mapel', 'tempat_jenjang'] as $table) {
                if (DB::table($table)->where('nilai_kesesuaian', $item->nilai)->exists()) {
                    return back()->with('error', 'Nilai skala ini masih digunakan. Ubah kesesuaian tempat sebelum menghapusnya.');
                }
            }
        }
        try {
            $item->delete();
        } catch (QueryException $exception) {
            if (in_array((string) $exception->getCode(), ['23000', '23503'])) {
                return back()->with('error', 'Data masih digunakan oleh data lain. Periksa dan ubah relasinya terlebih dahulu.');
            }
            throw $exception;
        }

        return redirect()->route($definition['route'].'.index')->with('success', $definition['singular'].' berhasil dihapus.');
    }
}
