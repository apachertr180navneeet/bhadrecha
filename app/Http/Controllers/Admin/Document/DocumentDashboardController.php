<?php

namespace App\Http\Controllers\Admin\Document;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Company;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentDashboardController extends Controller
{
    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $companyId = $request->get('company_id', session('active_company_id', $user->company_id));

        if (!$user->isSuperAdmin()) {
            $companyId = $user->company_id;
        }

        $metrics = $this->documentService->getStorageMetrics($companyId);

        // Category-wise Document Distribution
        $categoriesDistribution = DocumentCategory::forCompany($companyId)
            ->withCount(['documents' => function ($q) use ($companyId) {
                if ($companyId) {
                    $q->where('company_id', $companyId);
                }
            }])
            ->get();

        // File Extension Distribution
        $fileTypeDistribution = Document::forCompany($companyId)
            ->select('file_extension', DB::raw('count(*) as total'))
            ->groupBy('file_extension')
            ->orderBy('total', 'desc')
            ->get();

        // Monthly Uploads (Last 6 Months)
        $monthlyUploads = Document::forCompany($companyId)
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Recent Uploads
        $recentUploads = Document::forCompany($companyId)
            ->with(['category', 'uploader', 'company'])
            ->latest()
            ->take(5)
            ->get();

        // Most Downloaded Documents
        $mostDownloaded = Document::forCompany($companyId)
            ->with(['category', 'uploader'])
            ->orderBy('downloads_count', 'desc')
            ->take(5)
            ->get();

        // Expiring Documents (Today, 7, 15, 30 days)
        $expiringTodayDocs = Document::forCompany($companyId)
            ->whereDate('expiry_date', today())
            ->with('category')
            ->take(5)
            ->get();

        $expiring7DaysDocs = Document::forCompany($companyId)
            ->whereBetween('expiry_date', [today(), today()->addDays(7)])
            ->with('category')
            ->take(5)
            ->get();

        $companies = $user->isSuperAdmin() ? Company::where('status', 'active')->get() : collect();

        return view('admin.documents.dashboard', compact(
            'metrics',
            'categoriesDistribution',
            'fileTypeDistribution',
            'monthlyUploads',
            'recentUploads',
            'mostDownloaded',
            'expiringTodayDocs',
            'expiring7DaysDocs',
            'companies',
            'companyId'
        ));
    }
}
