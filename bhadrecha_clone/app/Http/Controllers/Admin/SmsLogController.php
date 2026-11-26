<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SmsLog;
use App\Models\Customer;
use Validator, Exception;

class SmsLogController extends Controller
{
    public function index()
    {
        $logs = SmsLog::with('customer')->latest()->paginate(50);
        return view('admin.crm.sms.index', compact('logs'));
    }

    public function send(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'message' => 'required',
                'type' => 'required|in:appointment_reminder,birthday,promotional',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors());
            }

            $customer = Customer::find($request->customer_id);

            SmsLog::create([
                'customer_id' => $customer->id,
                'mobile' => $customer->mobile,
                'message' => $request->message,
                'type' => $request->type,
                'status' => 'sent',
            ]);

            logActivity('Sent SMS', 'CRM', "Sent SMS to {$customer->name}");
            return redirect()->back()->with('success', 'SMS sent successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
