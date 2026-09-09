<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Lead;
use App\Models\Tracking;
use App\Models\Bulty;
use Barryvdh\DomPDF\Facade\Pdf;

class WebsiteController extends Controller
{
    public function landing()
    {
        return view('front.landing');
    }

    public function home()
    {
        return view('front.home');
    }

    public function about()
    {
        return view('front.about');
    }

    public function services()
    {
        return view('front.services');
    }

    public function contact()
    {
        return view('front.contact');
    }

    public function submitContact(Request $request)
    {
        $request->validate([
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        Lead::create([
            'name' => $request->fname . ' ' . $request->lname,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        return back()->with('success', 'Thank you for contacting us. We will get back to you shortly.');
    }

    public function tracking(Request $request)
    {
        $trackingResult = null;
        if ($request->has('lr_number')) {
            $trackingResult = Tracking::where('lr_number', $request->lr_number)->first();
            if (!$trackingResult) {
                return back()->with('error', 'LR Number not found.');
            }
        }
        return view('front.tracking', compact('trackingResult'));
    }

    public function showBilty($shareToken)
    {
        $bulty = Bulty::with([
            'consignor', 'consignee', 'vehicle', 'driver',
            'originCity', 'destinationCity', 'bultyItems',
        ])->where('share_token', $shareToken)->firstOrFail();

        return view('front.bilty-detail', compact('bulty'));
    }

    public function downloadBiltyPdf($shareToken)
    {
        $bulty = Bulty::with([
            'branch', 'consignor', 'consignee', 'vehicle', 'driver',
            'originCity', 'destinationCity', 'bultyItems', 'company', 'bultyDetail',
        ])->where('share_token', $shareToken)->firstOrFail();

        $pdf = Pdf::loadView('admin.transport.bulties.pdf', compact('bulty'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download("Bulty-{$bulty->lr_no}.pdf");
    }

    public function refreshShareToken(Bulty $bulty)
    {
        $bulty->share_token = (string) \Illuminate\Support\Str::uuid();
        $bulty->save();

        return back()->with('success', 'Share link regenerated.');
    }

    public function uploadMaterialDocument(Request $request, $shareToken)
    {
        $bulty = Bulty::where('share_token', $shareToken)->firstOrFail();

        if ($bulty->material_document_status) {
            return back()->with('error', 'Document is already approved. Cannot upload a new document.');
        }

        // Support both single and multiple files input
        $request->validate([
            'material_documents' => 'nullable|array',
            'material_documents.*' => 'file|mimes:jpg,jpeg,png,pdf|max:10240',
            'material_document' => 'nullable',
        ]);

        $files = [];
        if ($request->hasFile('material_documents')) {
            $files = $request->file('material_documents');
        } elseif ($request->hasFile('material_document')) {
            $f = $request->file('material_document');
            $files = is_array($f) ? $f : [$f];
        }

        if (empty($files)) {
            return back()->with('error', 'Please select at least one photo or document to upload.');
        }

        // Delete existing files
        Bulty::deleteStoredDocumentFiles($bulty->material_document);

        $uploadedUrls = [];
        foreach ($files as $file) {
            if ($file && $file->isValid()) {
                $path = $file->store('material-documents', 'uploads');
                $uploadedUrls[] = asset('uploads/' . $path);
            }
        }

        if (empty($uploadedUrls)) {
            return back()->with('error', 'Failed to upload document photos. Please try again.');
        }

        $bulty->material_document = count($uploadedUrls) === 1 ? $uploadedUrls[0] : json_encode($uploadedUrls);
        $bulty->material_document_status = false;
        $bulty->status = 'planned';
        $bulty->save();

        $count = count($uploadedUrls);
        $msg = $count > 1 
            ? "{$count} material photos uploaded successfully. Status updated to Planned." 
            : "Material document uploaded successfully. Status updated to Planned.";

        return back()->with('success', $msg);
    }

    public function uploadPodDocument(Request $request, $shareToken)
    {
        $bulty = Bulty::where('share_token', $shareToken)->firstOrFail();

        if ($bulty->pod_document_status) {
            return back()->with('error', 'POD is already approved. Cannot upload a new one.');
        }

        // Support both single and multiple files input
        $request->validate([
            'pod_files' => 'nullable|array',
            'pod_files.*' => 'file|mimes:jpg,jpeg,png,pdf|max:10240',
            'pod_file' => 'nullable',
        ]);

        $files = [];
        if ($request->hasFile('pod_files')) {
            $files = $request->file('pod_files');
        } elseif ($request->hasFile('pod_file')) {
            $f = $request->file('pod_file');
            $files = is_array($f) ? $f : [$f];
        }

        if (empty($files)) {
            return back()->with('error', 'Please select at least one photo or document to upload.');
        }

        // Delete existing files
        Bulty::deleteStoredDocumentFiles($bulty->pod_document);

        $uploadedUrls = [];
        foreach ($files as $file) {
            if ($file && $file->isValid()) {
                $path = $file->store('pods', 'uploads');
                $uploadedUrls[] = asset('uploads/' . $path);
            }
        }

        if (empty($uploadedUrls)) {
            return back()->with('error', 'Failed to upload POD photos. Please try again.');
        }

        $bulty->pod_document = count($uploadedUrls) === 1 ? $uploadedUrls[0] : json_encode($uploadedUrls);
        $bulty->pod_document_status = false;
        $bulty->status = 'partially_delivered';
        $bulty->save();

        $count = count($uploadedUrls);
        $msg = $count > 1 
            ? "{$count} POD photos uploaded successfully. Awaiting admin approval. Status updated to Partially Delivered." 
            : "POD uploaded successfully. Awaiting admin approval. Status updated to Partially Delivered.";

        return back()->with('success', $msg);
    }
}
