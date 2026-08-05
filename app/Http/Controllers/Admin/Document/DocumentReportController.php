<?php

namespace App\Http\Controllers\Admin\Document;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Company;
use App\Models\Branch;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentReportController extends Controller
{
    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    public function expiry(Request $request)
    {
        if (!auth()->user()->can('view document reports') && !auth()->user()->can('view documents') && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $user = auth()->user();
        $companyId = $request->get('company_id', session('active_company_id', $user->company_id));

        if (!$user->isSuperAdmin()) {
            $companyId = $user->company_id;
        }

        $filter = $request->get('timeframe', 'all');

        $query = Document::forCompany($companyId)
            ->whereNotNull('expiry_date')
            ->with(['category', 'uploader', 'company', 'branch']);

        switch ($filter) {
            case 'today':
                $query->whereDate('expiry_date', today());
                break;
            case '7_days':
                $query->whereBetween('expiry_date', [today(), today()->addDays(7)]);
                break;
            case '15_days':
                $query->whereBetween('expiry_date', [today(), today()->addDays(15)]);
                break;
            case '30_days':
                $query->whereBetween('expiry_date', [today(), today()->addDays(30)]);
                break;
            case 'expired':
                $query->where('expiry_date', '<', today());
                break;
        }

        $documents = $query->orderBy('expiry_date', 'asc')->get();
        $companies = $user->isSuperAdmin() ? Company::where('status', 'active')->get() : collect();

        return view('admin.documents.reports.expiry', compact('documents', 'filter', 'companies', 'companyId'));
    }

    public function storage(Request $request)
    {
        if (!auth()->user()->can('view document reports') && !auth()->user()->can('view documents') && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $user = auth()->user();
        $companyId = $request->get('company_id', session('active_company_id', $user->company_id));

        if (!$user->isSuperAdmin()) {
            $companyId = $user->company_id;
        }

        $metrics = $this->documentService->getStorageMetrics($companyId);
        $companies = $user->isSuperAdmin() ? Company::where('status', 'active')->get() : collect();

        return view('admin.documents.reports.storage', compact('metrics', 'companies', 'companyId'));
    }
}
