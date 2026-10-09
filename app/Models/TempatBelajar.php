<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class TempatBelajar extends Model
{
    protected $table = 'tempat_belajar';

    protected $primaryKey = 'id_tempat';

    protected $fillable = [
        'nama_tempat', 'alamat', 'deskripsi', 'karakteristik', 'aktivitas',
        'latitude', 'longitude', 'biaya', 'jam_operasional', 'foto', 'status', 'is_prototype',
    ];

    protected function casts(): array
    {
        return ['latitude' => 'float', 'longitude' => 'float', 'biaya' => 'decimal:2', 'is_prototype' => 'boolean'];
    }

    public function mapel(): BelongsToMany
    {
        return $this->belongsToMany(MataPelajaran::class, 'tempat_mapel', 'id_tempat', 'id_mapel')
            ->withPivot('nilai_kesesuaian')->withTimestamps();
    }

    public function jenjang(): BelongsToMany
    {
        return $this->belongsToMany(JenjangPendidikan::class, 'tempat_jenjang', 'id_tempat', 'id_jenjang')
            ->withPivot('nilai_kesesuaian')->withTimestamps();
    }

    public function penilaian(): HasMany
    {
        return $this->hasMany(PenilaianTempat::class, 'id_tempat');
    }

    public function getFotoUrlAttribute(): string
    {
        if (! $this->foto) {
            return asset('images/place-placeholder.svg');
        }

        if (str_starts_with(ltrim($this->foto, '/'), 'images/')) {
            return asset(ltrim($this->foto, '/'));
        }

        return Storage::disk('public')->url($this->foto);
    }
}
