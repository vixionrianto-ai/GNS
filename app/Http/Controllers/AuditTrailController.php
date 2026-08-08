<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
    /**
     * Display audit trail.
     */
    public function index(Request $request)
    {
        $query = AuditTrail::with('user')
            ->latest();

        /*
        |--------------------------------------------------------------------------
        | Filter Module
        |--------------------------------------------------------------------------
        */

        if ($request->filled('module')) {

            $query->where(
                'module',
                $request->module
            );

        }

        /*
        |--------------------------------------------------------------------------
        | Filter Action
        |--------------------------------------------------------------------------
        */

        if ($request->filled('action')) {

            $query->where(
                'action',
                $request->action
            );

        }

        /*
        |--------------------------------------------------------------------------
        | Filter User
        |--------------------------------------------------------------------------
        */

        if ($request->filled('user')) {

            $query->where(
                'user_id',
                $request->user
            );

        }

        /*
        |--------------------------------------------------------------------------
        | Filter Date
        |--------------------------------------------------------------------------
        */

        if ($request->filled('tanggal')) {

            $query->whereDate(
                'created_at',
                $request->tanggal
            );

        }

        $audits = $query
            ->paginate(30)
            ->withQueryString();

        $modules = AuditTrail::select('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        $actions = AuditTrail::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view(

            'audit.index',

            compact(

                'audits',

                'modules',

                'actions'

            )

        );
    }
}