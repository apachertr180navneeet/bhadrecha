<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Feedback;
use App\Models\Staff;
use App\Models\Service;
use DB;

class FeedbackController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view feedbacks')->only(['index', 'show']);
        $this->middleware('permission:delete feedbacks')->only('destroy');
    }

    public function index()
    {
        $storeId = auth()->user()->store_id;
        $isAdmin = auth()->user()->hasRole('Admin');

        $feedbacks = Feedback::with(['customer', 'appointment', 'staff', 'service'])
            ->when(!$isAdmin, function ($q) use ($storeId) {
                $q->whereHas('staff', fn($sq) => $sq->where('store_id', $storeId));
            })
            ->latest()->paginate(10);

        $positiveCount = (clone $feedbacks)->where('rating', '>=', 4)->count();
        $negativeCount = (clone $feedbacks)->where('rating', '<=', 2)->count();

        $staffRatings = Feedback::select(
            'staff_id',
            DB::raw('AVG(rating) as avg_rating'),
            DB::raw('COUNT(*) as total')
        )->whereNotNull('staff_id')
            ->groupBy('staff_id')
            ->with('staff')
            ->get();

        $serviceRatings = Feedback::select(
            'service_id',
            DB::raw('AVG(rating) as avg_rating'),
            DB::raw('COUNT(*) as total')
        )->whereNotNull('service_id')
            ->groupBy('service_id')
            ->with('service')
            ->get();

        return view('admin.feedbacks.index', compact(
            'feedbacks', 'positiveCount', 'negativeCount',
            'staffRatings', 'serviceRatings'
        ));
    }
}
