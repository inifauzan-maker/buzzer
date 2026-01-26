<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AkademikController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        if (! $user || $user->role !== 'superadmin') {
            abort(403, 'Akses ditolak.');
        }

        return view('akademik.index');
    }
}
