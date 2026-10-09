<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenjangPendidikan extends Model
{
    protected $table = 'jenjang_pendidikan';

    protected $primaryKey = 'id_jenjang';

    protected $fillable = ['nama_jenjang'];

    public function mapel(): HasMany
    {
        return $this->hasMany(MataPelajaran::class, 'id_jenjang');
    }

    public function sekolah(): HasMany
    {
        return $this->hasMany(Sekolah::class, 'id_jenjang');
    }

    public function tempat(): BelongsToMany
    {
        return $this->belongsToMany(TempatBelajar::class, 'tempat_jenjang', 'id_jenjang', 'id_tempat')
            ->withPivot('nilai_kesesuaian')->withTimestamps();
    }
}
