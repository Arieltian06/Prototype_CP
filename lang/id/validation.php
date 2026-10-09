<?php

return [
    'required' => ':attribute wajib diisi.',
    'required_if' => ':attribute wajib diisi ketika :other bernilai :value.',
    'email' => ':attribute harus berupa alamat email yang valid.',
    'numeric' => ':attribute harus berupa angka.',
    'integer' => ':attribute harus berupa bilangan bulat.',
    'string' => ':attribute harus berupa teks.',
    'boolean' => ':attribute harus berupa pilihan ya atau tidak.',
    'exists' => ':attribute tidak tersedia atau tidak sesuai.',
    'unique' => ':attribute sudah digunakan.',
    'in' => ':attribute tidak valid.',
    'image' => ':attribute harus berupa gambar.',
    'mimes' => ':attribute harus berupa berkas :values.',
    'max' => ['numeric' => ':attribute maksimal :max.', 'string' => ':attribute maksimal :max karakter.', 'file' => ':attribute maksimal :max KB.', 'array' => ':attribute maksimal :max item.'],
    'min' => ['numeric' => ':attribute minimal :min.', 'string' => ':attribute minimal :min karakter.'],
    'between' => ['numeric' => ':attribute harus di antara :min dan :max.'],
    'array' => ':attribute harus berupa daftar.',
    'regex' => 'Format :attribute tidak valid.',
    'attributes' => [
        'id_jenjang' => 'Jenjang', 'id_mapel' => 'Mata pelajaran', 'id_sekolah' => 'Sekolah',
        'nama_tempat' => 'Nama tempat', 'nama_jenjang' => 'Nama jenjang', 'nama_mapel' => 'Nama mata pelajaran',
        'nama_sekolah' => 'Nama sekolah', 'kode_kriteria' => 'Kode kriteria', 'nama_kriteria' => 'Nama kriteria',
        'jenis_kriteria' => 'Jenis kriteria', 'sumber_nilai' => 'Sumber nilai', 'bobot' => 'Bobot',
        'latitude' => 'Latitude', 'longitude' => 'Longitude', 'foto' => 'Foto', 'password' => 'Kata sandi',
    ],
];
