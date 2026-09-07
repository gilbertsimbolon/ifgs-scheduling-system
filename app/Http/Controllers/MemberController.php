<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberController extends Controller
{
    /**
     * Menampilkan daftar member yang terdaftar pada sistem.
     */
    public function index(Request $request): View
    {
        $members = Member::with('user')
            ->latest()
            ->paginate(10);

        return view('member.index', compact('members'));
    }
}
