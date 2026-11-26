<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmailLog;
use App\Models\Customer;
use Validator, Exception, Mail;

class EmailLogController extends Controller
{
    public function index()
    {
        $logs = EmailLog::with('customer')->latest()->paginate(50);
        return view('admin.crm.email.index', compact('logs'));
    }

    public function send(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'subject' => 'required',
                'message' => 'required',
                'type' => 'required|in:appointment_reminder,promotional,feedback',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors());
            }

            $customer = Customer::find($request->customer_id);

            if ($customer->email) {
                Mail::raw($request->message, function ($message) use ($customer, $request) {
                    $message->to($customer->email)
                        ->subject($request->subject);
                });
            }

            EmailLog::create([
                'customer_id' => $customer->id,
                'email' => $customer->email,
                'subject' => $request->subject,
                'message' => $request->message,
                'type' => $request->type,
                'status' => 'sent',
            ]);

            logActivity('Sent Email', 'CRM', "Sent email to {$customer->name}");
            return redirect()->back()->with('success', 'Email sent successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
