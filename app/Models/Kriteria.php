<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kriteria extends Model
{
    protected $table = 'kriteria';

    protected $primaryKey = 'id_kriteria';

    protected $fillable = ['kode_kriteria', 'nama_kriteria', 'jenis_kriteria', 'bobot', 'sumber_nilai'];

    protected function casts(): array
    {
        return ['bobot' => 'float'];
    }

    public function penilaian(): HasMany
    {
        return $this->hasMany(PenilaianTempat::class, 'id_kriteria');
    }
}
