<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\WhatsappLog;
use App\Models\Appointment;
use Validator, Exception, DB;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view customers')->only(['index', 'show', 'ajaxSearch']);
        $this->middleware('permission:create customers')->only(['create', 'store']);
        $this->middleware('permission:edit customers')->only(['edit', 'update']);
        $this->middleware('permission:delete customers')->only('destroy');
        $this->middleware('permission:view birthday customers')->only('getBirthdayCustomers');
        $this->middleware('permission:view birthday msg logs')->only('birthdayWhatsappLogs');
        $this->middleware('permission:send birthday whatsapp')->only('storeBirthdayWhatsappLog');
    }

    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('gender', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();
        return view('admin.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('admin.customers.create');
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'mobile' => 'required|unique:customers,mobile',
                'email' => 'nullable|email|unique:customers,email',
                'dob' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->errors());
            }

            Customer::create($request->all());

            logActivity('Created', 'Customer', "Created customer {$request->name}");
            return redirect()->route('admin.customers.index')->with('success', 'Customer created successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        $customer = Customer::with(['appointments' => function ($q) {
            $q->with(['service', 'staff', 'feedback'])->latest();
        }])->findOrFail($id);

        $adjustedBalances = Appointment::calculateAdjustedBalancesForAppointments($customer->appointments);

        return view('admin.customers.show', compact('customer', 'adjustedBalances'));
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        try {
            $customer = Customer::findOrFail($id);
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'mobile' => 'required|unique:customers,mobile,' . $id,
                'email' => 'nullable|email|unique:customers,email,' . $id,
                'dob' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->errors());
            }

            $customer->update($request->all());
            logActivity('Updated', 'Customer', "Updated customer {$customer->name}");
            return redirect()->route('admin.customers.index')->with('success', 'Customer updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $customer = Customer::findOrFail($id);
            $customer->delete();
            logActivity('Deleted', 'Customer', "Deleted customer {$customer->name}");
            return redirect()->route('admin.customers.index')->with('success', 'Customer deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function getBirthdayCustomers()
    {
        $currentMonth = now()->month;
        $today = now()->format('m-d');
        $birthdays = Customer::whereMonth('dob', $currentMonth)
            ->orderByRaw("DAY(dob) ASC")
            ->get();
        return view('admin.customers.birthdays', compact('birthdays', 'today'));
    }

    public function storeBirthdayWhatsappLog(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customers' => 'required|array',
                'customers.*.name' => 'required|string',
                'customers.*.mobile' => 'required|string',
                'customers.*.message' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            $count = 0;
            foreach ($request->customers as $data) {
                $customer = Customer::where('mobile', $data['mobile'])->first();

                WhatsappLog::create([
                    'customer_id' => $customer ? $customer->id : null,
                    'mobile' => $data['mobile'],
                    'message' => $data['message'],
                    'type' => 'birthday',
                    'status' => 'sent',
                ]);
                $count++;
            }

            logActivity('Sent WhatsApp', 'Birthday', "Sent birthday WhatsApp to {$count} customer(s)");
            return response()->json(['success' => true, 'message' => "Message log saved for {$count} customer(s)"]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function birthdayWhatsappLogs()
    {
        $logs = WhatsappLog::with('customer')->where('type', 'birthday')->latest()->paginate(20);
        return view('admin.customers.birthday_msg_logs', compact('logs'));
    }

    public function ajaxSearch(Request $request)
    {
        $q = trim($request->get('q', ''));
        $customers = Customer::when($q, function ($query) use ($q) {
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'LIKE', "%{$q}%")
                    ->orWhere('mobile', 'LIKE', "%{$q}%")
                    ->orWhere('email', 'LIKE', "%{$q}%");
            });
        })->latest()->limit(30)->get();

        return response()->json($customers->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'mobile' => $c->mobile ?? '',
                'text' => $c->name . ($c->mobile ? ' (' . $c->mobile . ')' : '')
            ];
        }));
    }
}
