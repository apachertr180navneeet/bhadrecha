<?php

namespace App\Http\Controllers\Admin\Document;

use App\Http\Controllers\Controller;
use App\Models\DocumentActivityLog;
use App\Models\Company;
use Illuminate\Http\Request;

class DocumentActivityController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $companyId = $request->get('company_id', session('active_company_id', $user->company_id));

        if (!$user->isSuperAdmin()) {
            $companyId = $user->company_id;
        }

        $query = DocumentActivityLog::where('company_id', $companyId)
            ->with(['document', 'user'])
            ->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $activities = $query->paginate(20);
        $companies = $user->isSuperAdmin() ? Company::where('status', 'active')->get() : collect();

        return view('admin.documents.activity', compact('activities', 'companies', 'companyId'));
    }
}
