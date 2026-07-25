<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function company()
    {
        return view('admin.loan.company');
    }

    public function vehicle()
    {
        return view('admin.loan.vehicle');
    }
}
