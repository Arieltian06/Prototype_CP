<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NilaiKesesuaian extends Model
{
    protected $table = 'nilai_kesesuaian';

    protected $fillable = ['nilai', 'label'];

    protected function casts(): array
    {
        return ['nilai' => 'float'];
    }
}
