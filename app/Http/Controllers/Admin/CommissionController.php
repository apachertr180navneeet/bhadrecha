<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Commission;
use App\Models\Staff;

class CommissionController extends Controller
{
    public function index()
    {
        $storeId = auth()->user()->store_id;
        $isAdmin = auth()->user()->hasRole('Admin');

        $commissions = Commission::with(['staff', 'appointment', 'service'])
            ->whereHas('staff', function ($q) use ($storeId, $isAdmin) {
                if ($storeId && !$isAdmin) {
                    $q->where('store_id', $storeId);
                }
            })
            ->latest()->paginate(10);

        return view('admin.commissions.index', compact('commissions'));
    }
}
