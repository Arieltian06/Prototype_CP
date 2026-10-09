<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sekolah extends Model
{
    protected $table = 'sekolah';

    protected $primaryKey = 'id_sekolah';

    protected $fillable = ['nama_sekolah', 'alamat', 'latitude', 'longitude', 'id_jenjang', 'is_prototype'];

    protected function casts(): array
    {
        return ['latitude' => 'float', 'longitude' => 'float', 'is_prototype' => 'boolean'];
    }

    public function jenjang(): BelongsTo
    {
        return $this->belongsTo(JenjangPendidikan::class, 'id_jenjang');
    }
}
