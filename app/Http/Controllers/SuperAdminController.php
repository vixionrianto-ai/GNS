<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    /**
     * Halaman Reset Data
     */
    public function index()
    {
        return view('superadmin.reset');
    }

    /**
     * Proses Reset Data
     */
    public function reset(Request $request)
    {
        return back()->with(
            'success',
            'Fitur reset belum diaktifkan.'
        );
    }
}