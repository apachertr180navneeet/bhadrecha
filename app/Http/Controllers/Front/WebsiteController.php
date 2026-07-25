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

        $request->validate([
            'material_document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        if ($bulty->material_document) {
            $relativePath = str_replace(asset('uploads/'), '', $bulty->material_document);
            Storage::disk('uploads')->delete($relativePath);
        }

        $path = $request->file('material_document')->store('material-documents', 'uploads');

        $bulty->material_document = asset('uploads/' . $path);
        $bulty->status = 'planned';
        $bulty->save();

        return back()->with('success', 'Material document uploaded successfully. Status updated to Planned.');
    }

    public function uploadPodDocument(Request $request, $shareToken)
    {
        $bulty = Bulty::where('share_token', $shareToken)->firstOrFail();

        if ($bulty->pod_document_status) {
            return back()->with('error', 'POD is already approved. Cannot upload a new one.');
        }

        $request->validate([
            'pod_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($bulty->pod_document) {
            $relativePath = str_replace(asset('uploads/'), '', $bulty->pod_document);
            Storage::disk('uploads')->delete($relativePath);
        }

        $path = $request->file('pod_file')->store('pods', 'uploads');
        $bulty->pod_document = asset('uploads/' . $path);
        $bulty->status = 'partially_delivered';
        $bulty->save();

        return back()->with('success', 'POD uploaded successfully. Awaiting admin approval. Status updated to Partially Delivered.');
    }
}
