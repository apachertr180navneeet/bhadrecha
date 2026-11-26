<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function vehicle()
    {
        if (!auth()->user()->can('view vehicle loans') && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        return view('admin.loan.vehicle');
    }
}
