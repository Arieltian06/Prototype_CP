<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecommendationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Scope dependent choices only with a scalar; malformed arrays must fail
        // validation rather than reaching the database query builder.
        $levelId = $this->input('id_jenjang');
        $levelId = is_scalar($levelId) ? $levelId : null;

        return [
            'id_jenjang' => ['bail', 'required', 'integer', 'min:1', 'exists:jenjang_pendidikan,id_jenjang'],
            'id_mapel' => ['bail', 'required', 'integer', 'min:1', Rule::exists('mata_pelajaran', 'id_mapel')->where('id_jenjang', $levelId)],
            'id_sekolah' => ['bail', 'required', 'integer', 'min:1', Rule::exists('sekolah', 'id_sekolah')->where('id_jenjang', $levelId)],
            'use_location' => ['sometimes', 'boolean'],
            'latitude' => [Rule::requiredIf($this->boolean('use_location')), 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => [Rule::requiredIf($this->boolean('use_location')), 'nullable', 'numeric', 'between:-180,180'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_jenjang.required' => 'Pilih jenjang pendidikan terlebih dahulu.',
            'id_mapel.required' => 'Pilih mata pelajaran.',
            'id_mapel.exists' => 'Mata pelajaran harus sesuai dengan jenjang yang dipilih.',
            'id_sekolah.required' => 'Pilih sekolah sebagai lokasi asal.',
            'id_sekolah.exists' => 'Sekolah harus sesuai dengan jenjang yang dipilih.',
            'latitude.required' => 'Lokasi belum tersedia. Pilih sekolah atau coba akses lokasi kembali.',
            'longitude.required' => 'Lokasi belum tersedia. Pilih sekolah atau coba akses lokasi kembali.',
        ];
    }
}
