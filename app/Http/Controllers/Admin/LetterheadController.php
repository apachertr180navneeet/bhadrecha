<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Letterhead;
use App\Models\ActivityLog;
use App\Mail\LetterheadMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class LetterheadController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('view letterheads')) {
            abort(403, 'Unauthorized action.');
        }

        $query = Letterhead::with('company', 'creator');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('letter_no', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('recipient_email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('letter_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('letter_date', '<=', $request->date_to);
        }

        $letterheads = $query->orderBy('id', 'desc')->paginate(15);
        $companies = Company::where('status', 'active')->orderBy('name')->get();

        return view('admin.letterheads.index', compact('letterheads', 'companies'));
    }

    public function create()
    {
        if (!auth()->user()->can('create letterheads')) {
            abort(403, 'Unauthorized action.');
        }

        $companies = Company::where('status', 'active')->orderBy('name')->get();
        
        // Auto generate default letter_no
        $latest = Letterhead::withTrashed()->max('id') + 1;
        $defaultLetterNo = 'LTR-' . date('Y') . '-' . str_pad($latest, 4, '0', STR_PAD_LEFT);

        return view('admin.letterheads.create', compact('companies', 'defaultLetterNo'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('create letterheads')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'letter_no' => 'required|string|max:100|unique:letterheads,letter_no',
            'letter_date' => 'required|date',
            'recipient_name' => 'required|string|max:255',
            'recipient_designation' => 'nullable|string|max:255',
            'recipient_company' => 'nullable|string|max:255',
            'recipient_address' => 'nullable|string',
            'recipient_email' => 'nullable|email|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'signatory_name' => 'nullable|string|max:255',
            'signatory_designation' => 'nullable|string|max:255',
        ]);

        $validated['created_by'] = Auth::id();

        $letterhead = Letterhead::create($validated);

        ActivityLog::log('letterhead_created', "Created Letterhead #{$letterhead->letter_no} for {$letterhead->recipient_name}", $letterhead);

        if ($request->has('send_mail_now') && !empty($letterhead->recipient_email)) {
            try {
                Mail::to($letterhead->recipient_email)->send(new LetterheadMail($letterhead));
                return redirect()->route('admin.letterheads.index')
                    ->with('success', 'Letterhead saved and email sent successfully to ' . $letterhead->recipient_email);
            } catch (\Exception $e) {
                return redirect()->route('admin.letterheads.index')
                    ->with('warning', 'Letterhead saved, but failed to send email: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.letterheads.index')->with('success', 'Letterhead saved successfully.');
    }

    public function show(Letterhead $letterhead)
    {
        $letterhead->load('company', 'creator');
        return view('admin.letterheads.show', compact('letterhead'));
    }

    public function edit(Letterhead $letterhead)
    {
        if (!auth()->user()->can('edit letterheads')) {
            abort(403, 'Unauthorized action.');
        }

        $companies = Company::where('status', 'active')->orderBy('name')->get();
        return view('admin.letterheads.edit', compact('letterhead', 'companies'));
    }

    public function update(Request $request, Letterhead $letterhead)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'letter_no' => 'required|string|max:100|unique:letterheads,letter_no,' . $letterhead->id,
            'letter_date' => 'required|date',
            'recipient_name' => 'required|string|max:255',
            'recipient_designation' => 'nullable|string|max:255',
            'recipient_company' => 'nullable|string|max:255',
            'recipient_address' => 'nullable|string',
            'recipient_email' => 'nullable|email|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'signatory_name' => 'nullable|string|max:255',
            'signatory_designation' => 'nullable|string|max:255',
        ]);

        $letterhead->update($validated);

        ActivityLog::log('letterhead_updated', "Updated Letterhead #{$letterhead->letter_no}", $letterhead);

        return redirect()->route('admin.letterheads.index')->with('success', 'Letterhead updated successfully.');
    }

    public function destroy(Letterhead $letterhead)
    {
        $letterNo = $letterhead->letter_no;
        $letterhead->delete();

        ActivityLog::log('letterhead_deleted', "Deleted Letterhead #{$letterNo}");

        return back()->with('success', 'Letterhead deleted successfully.');
    }

    public function pdf(Letterhead $letterhead, Request $request)
    {
        $letterhead->load('company');
        $pdf = Pdf::loadView('admin.letterheads.pdf', compact('letterhead'));
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'chroot' => public_path(),
        ]);

        $filename = "Letter-{$letterhead->letter_no}.pdf";

        if ($request->get('action') === 'download') {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }

    public function sendMail(Request $request, Letterhead $letterhead)
    {
        if (!auth()->user()->can('send letterheads mail') && !auth()->user()->can('view letterheads')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $letterhead->load('company');
        $targetEmail = $request->email;

        // Update recipient email if changed
        if ($letterhead->recipient_email !== $targetEmail) {
            $letterhead->recipient_email = $targetEmail;
            $letterhead->save();
        }

        try {
            Mail::to($targetEmail)->send(new LetterheadMail($letterhead));
            ActivityLog::log('letterhead_email_sent', "Sent Letterhead #{$letterhead->letter_no} to {$targetEmail}", $letterhead);

            return response()->json([
                'success' => true,
                'message' => 'Letterhead email sent successfully to ' . $targetEmail,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getCompanyDetails(Company $company)
    {
        $cleanLogo = $company->logo ? ltrim($company->logo, '/') : null;
        $logoUrl = null;
        if ($cleanLogo) {
            $logoUrl = \Illuminate\Support\Str::startsWith($cleanLogo, ['http://', 'https://'])
                ? $cleanLogo
                : asset(\Illuminate\Support\Str::startsWith($cleanLogo, 'uploads/') ? $cleanLogo : 'uploads/' . $cleanLogo);
        }

        $cleanSig = $company->digital_signature ? ltrim($company->digital_signature, '/') : null;
        $sigUrl = null;
        if ($cleanSig) {
            $sigUrl = \Illuminate\Support\Str::startsWith($cleanSig, ['http://', 'https://'])
                ? $cleanSig
                : asset(\Illuminate\Support\Str::startsWith($cleanSig, 'uploads/') ? $cleanSig : 'uploads/' . $cleanSig);
        }

        return response()->json([
            'success' => true,
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'email' => $company->email,
                'phone' => $company->phone,
                'address' => $company->address,
                'state' => $company->state,
                'gst_number' => $company->gst_number,
                'pan_number' => $company->pan_number,
                'disclaimer' => $company->disclaimer,
                'declaration' => $company->declaration,
                'logo_url' => $logoUrl,
                'digital_signature_url' => $sigUrl,
                'bank_holder_name' => $company->bank_holder_name,
                'bank_name' => $company->bank_name,
                'bank_account_no' => $company->bank_account_no,
                'bank_ifsc' => $company->bank_ifsc,
                'bank_branch' => $company->bank_branch,
                'owner_name' => $company->owner_name,
            ]
        ]);
    }
}
