<?php

namespace App\Http\Requests\Admin;

use App\Models\Kriteria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        $resource = explode('.', $this->route()->getName())[1];
        $id = $this->route($resource);
        $levelId = is_scalar($this->input('id_jenjang')) ? $this->input('id_jenjang') : null;

        return match ($resource) {
            'jenjang' => [
                'nama_jenjang' => ['required', 'string', 'max:50', Rule::unique('jenjang_pendidikan', 'nama_jenjang')->ignore($id, 'id_jenjang')],
            ],
            'mapel' => [
                'nama_mapel' => ['bail', 'required', 'string', 'max:100', Rule::unique('mata_pelajaran', 'nama_mapel')->where('id_jenjang', $levelId)->ignore($id, 'id_mapel')],
                'id_jenjang' => ['bail', 'required', 'integer', 'min:1', 'exists:jenjang_pendidikan,id_jenjang'],
            ],
            'sekolah' => [
                'nama_sekolah' => ['required', 'string', 'max:150'],
                'alamat' => ['required', 'string', 'max:1000'],
                'latitude' => ['required', 'numeric', 'between:-90,90'],
                'longitude' => ['required', 'numeric', 'between:-180,180'],
                'id_jenjang' => ['bail', 'required', 'integer', 'min:1', 'exists:jenjang_pendidikan,id_jenjang'],
            ],
            'kriteria' => [
                'kode_kriteria' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z][A-Za-z0-9_-]*$/', Rule::unique('kriteria', 'kode_kriteria')->ignore($id, 'id_kriteria')],
                'nama_kriteria' => ['required', 'string', 'max:150'],
                'jenis_kriteria' => ['required', Rule::in(['benefit', 'cost'])],
                'bobot' => ['required', 'numeric', 'between:0,100000', 'decimal:0,4'],
                'sumber_nilai' => ['required', Rule::in(['jenjang', 'mapel', 'jarak', 'manual'])],
            ],
            'skala' => [
                'nilai' => ['required', 'numeric', 'gt:0', 'max:100000', 'decimal:0,4', Rule::unique('nilai_kesesuaian', 'nilai')->ignore($id)],
                'label' => ['required', 'string', 'max:100'],
            ],
            default => [],
        };
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if (! $this->routeIs('admin.kriteria.*') || $validator->errors()->isNotEmpty()) {
                return;
            }
            $other = Kriteria::query()->when($this->route('kriteria'), fn ($query, $id) => $query->where('id_kriteria', '!=', $id));
            if ((float) (clone $other)->sum('bobot') + (float) $this->input('bobot') <= 0) {
                $validator->errors()->add('bobot', 'Jumlah seluruh bobot harus lebih dari nol agar rekomendasi dapat dihitung.');
            }
            if ($this->input('sumber_nilai') !== 'manual' && (clone $other)->where('sumber_nilai', $this->input('sumber_nilai'))->exists()) {
                $validator->errors()->add('sumber_nilai', 'Sumber perhitungan ini sudah digunakan oleh kriteria lain. Pilih nilai manual untuk kriteria tambahan.');
            }
        }];
    }

    public function attributes(): array
    {
        return ['nama_jenjang' => 'nama jenjang', 'nama_mapel' => 'nama mata pelajaran', 'id_jenjang' => 'jenjang pendidikan', 'nama_sekolah' => 'nama sekolah', 'kode_kriteria' => 'kode kriteria', 'nama_kriteria' => 'nama kriteria', 'jenis_kriteria' => 'jenis kriteria', 'sumber_nilai' => 'sumber nilai'];
    }

    public function messages(): array
    {
        return ['required' => ':attribute wajib diisi.', 'unique' => ':attribute sudah digunakan.', 'exists' => ':attribute tidak ditemukan.', 'numeric' => ':attribute harus berupa angka.', 'between' => ':attribute harus berada di antara :min dan :max.', 'max' => ':attribute melebihi batas :max.', 'in' => 'Pilihan :attribute tidak valid.', 'decimal' => ':attribute maksimal memiliki 4 angka desimal.', 'gt' => ':attribute harus lebih dari :value.', 'regex' => ':attribute harus diawali huruf dan hanya berisi huruf, angka, tanda hubung atau garis bawah.'];
    }
}
