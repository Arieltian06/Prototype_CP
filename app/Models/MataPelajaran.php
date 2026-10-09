<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MataPelajaran extends Model
{
    protected $table = 'mata_pelajaran';

    protected $primaryKey = 'id_mapel';

    protected $fillable = ['nama_mapel', 'id_jenjang'];

    public function jenjang(): BelongsTo
    {
        return $this->belongsTo(JenjangPendidikan::class, 'id_jenjang');
    }

    public function tempat(): BelongsToMany
    {
        return $this->belongsToMany(TempatBelajar::class, 'tempat_mapel', 'id_mapel', 'id_tempat')
            ->withPivot('nilai_kesesuaian')->withTimestamps();
    }
}
