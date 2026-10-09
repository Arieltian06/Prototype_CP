<?php

namespace App\Http\Requests\Admin;

use App\Models\JenjangPendidikan;
use App\Models\Kriteria;
use App\Models\MataPelajaran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TempatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'nama_tempat' => ['required', 'string', 'max:150'],
            'alamat' => ['required', 'string', 'max:1000'],
            'deskripsi' => ['required', 'string', 'max:10000'],
            'karakteristik' => ['required', 'string', 'max:3000'],
            'aktivitas' => ['required', 'string', 'max:5000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'biaya' => ['required', 'numeric', 'between:0,999999999'],
            'jam_operasional' => ['required', 'string', 'max:150'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'hapus_foto' => ['sometimes', 'boolean'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'is_prototype' => ['required', 'boolean'],
            'mapel' => ['sometimes', 'array'],
            'mapel.*' => ['nullable', 'numeric', Rule::exists('nilai_kesesuaian', 'nilai')],
            'jenjang' => ['sometimes', 'array'],
            'jenjang.*' => ['nullable', 'numeric', Rule::exists('nilai_kesesuaian', 'nilai')],
            'penilaian' => ['sometimes', 'array'],
            'penilaian.*' => ['nullable', 'numeric', 'between:0,100000', 'decimal:0,4'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            foreach (['mapel' => [MataPelajaran::class, 'id_mapel'], 'jenjang' => [JenjangPendidikan::class, 'id_jenjang'], 'penilaian' => [Kriteria::class, 'id_kriteria']] as $field => [$class, $key]) {
                $values = $this->input($field, []);
                if (! is_array($values)) {
                    continue;
                }
                $ids = array_keys(array_filter($values, fn ($value) => $value !== null && $value !== ''));
                $query = $class::query()->whereIn($key, $ids);
                if ($field === 'penilaian') {
                    $query->where('sumber_nilai', 'manual');
                }
                $existing = $query->pluck($key)->map(fn ($id) => (string) $id)->all();
                foreach ($ids as $id) {
                    if (! in_array((string) $id, $existing, true)) {
                        $validator->errors()->add($field, 'Relasi yang dipilih tidak valid. Muat ulang halaman lalu coba lagi.');
                        break;
                    }
                }
            }
        }];
    }

    public function messages(): array
    {
        return ['required' => ':attribute wajib diisi.', 'numeric' => ':attribute harus berupa angka.', 'between' => ':attribute harus berada di antara :min dan :max.', 'max' => ':attribute melebihi batas :max.', 'foto.image' => 'Foto harus berupa gambar.', 'foto.mimes' => 'Gunakan foto JPG, JPEG, PNG, atau WEBP.', 'foto.max' => 'Ukuran foto maksimal 3 MB.', 'mapel.*.exists' => 'Gunakan nilai mata pelajaran dari skala kesesuaian.', 'jenjang.*.exists' => 'Gunakan nilai jenjang dari skala kesesuaian.', 'decimal' => 'Nilai manual maksimal memiliki 4 angka desimal.'];
    }

    public function attributes(): array
    {
        return ['nama_tempat' => 'nama tempat', 'jam_operasional' => 'jam operasional', 'is_prototype' => 'penanda data prototype', 'penilaian.*' => 'nilai manual', 'mapel.*' => 'kesesuaian mata pelajaran', 'jenjang.*' => 'kesesuaian jenjang'];
    }
}
