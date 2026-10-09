<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenilaianTempat extends Model
{
    protected $table = 'penilaian_tempat';

    protected $fillable = ['id_tempat', 'id_kriteria', 'nilai'];

    protected function casts(): array
    {
        return ['nilai' => 'float'];
    }

    public function tempat(): BelongsTo
    {
        return $this->belongsTo(TempatBelajar::class, 'id_tempat');
    }

    public function kriteria(): BelongsTo
    {
        return $this->belongsTo(Kriteria::class, 'id_kriteria');
    }
}
