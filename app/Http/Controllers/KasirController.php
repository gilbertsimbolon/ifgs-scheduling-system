<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class KasirController extends Controller
{
    /**
     * Tampilkan antarmuka tunggal Kasir / Front Desk (tablet-friendly).
     */
    public function index(Request $request): View
    {
        $roles = Role::pluck('name');
        $today = Carbon::today()->locale('id')->isoFormat('dddd, D MMMM Y');

        return view('kasir.index', compact('roles', 'today'));
    }
}
