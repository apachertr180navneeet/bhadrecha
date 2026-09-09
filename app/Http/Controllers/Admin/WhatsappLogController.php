<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WhatsappLog;
use App\Models\Customer;
use Validator, Exception;

class WhatsappLogController extends Controller
{
    public function index()
    {
        $logs = WhatsappLog::with('customer')->latest()->paginate(50);
        return view('admin.crm.whatsapp.index', compact('logs'));
    }

    public function send(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'message' => 'required',
                'type' => 'required',
            ]);

            if ($validator->fails()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $validator->errors()
                    ], 422);
                }
                return redirect()->back()->withErrors($validator->errors());
            }

            $customer = Customer::find($request->customer_id);

            $whatsappUrl = null;
            if ($customer && $customer->mobile) {
                $cleanMobile = preg_replace('/[^0-9]/', '', $customer->mobile);
                if (strlen($cleanMobile) == 10) {
                    $cleanMobile = '91' . $cleanMobile;
                }
                $whatsappUrl = "https://wa.me/{$cleanMobile}?text=" . urlencode($request->message);
            }

            WhatsappLog::create([
                'customer_id' => $customer->id,
                'mobile' => $customer->mobile,
                'message' => $request->message,
                'type' => $request->type,
                'status' => 'sent',
            ]);

            logActivity('Sent WhatsApp', 'CRM', "Sent WhatsApp to {$customer->name}");

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'WhatsApp message logged successfully',
                    'whatsapp_url' => $whatsappUrl
                ]);
            }

            return redirect()->back()->with('success', 'WhatsApp message sent successfully');
        } catch (Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
