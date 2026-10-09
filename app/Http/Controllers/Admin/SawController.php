<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PerhitunganSaw;
use Illuminate\Http\Request;

class SawController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['q' => ['nullable', 'string', 'max:80'], 'page' => ['nullable', 'integer', 'min:1']]);
        $query = PerhitunganSaw::query();
        if ($request->filled('q')) {
            $query->where('id', 'like', '%'.mb_substr((string) $request->query('q'), 0, 80).'%');
        }

        return view('admin.saw.index', ['calculations' => $query->latest()->paginate(12)->withQueryString()]);
    }

    public function show(PerhitunganSaw $perhitungan)
    {
        return view('admin.saw.show', ['calculation' => $perhitungan, 'result' => $perhitungan->hasil, 'input' => $perhitungan->input]);
    }
}
