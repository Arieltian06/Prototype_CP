<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PerhitunganSaw extends Model
{
    use HasUuids;

    protected $table = 'perhitungan_saw';

    protected $fillable = ['input', 'hasil'];

    protected function casts(): array
    {
        return ['input' => 'array', 'hasil' => 'array'];
    }
}
