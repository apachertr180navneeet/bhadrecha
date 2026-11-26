<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        $employeeId = $request->input('user_id');

        $authUser = auth()->user();

        $query = User::with(['company:id,name', 'branch:id,name', 'roles']);

        if ($authUser->isSuperAdmin()) {
            $query->whereDoesntHave('roles', function ($q) {
                $q->whereIn('name', ['Super Admin']);
            });
            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }
            if ($employeeId) {
                $query->where('id', $employeeId);
            }
        } else {
            // Non-super admins see ONLY their own logged-in employee record
            $query->where('id', $authUser->id);
        }

        $users = $query->orderBy('first_name')->get();

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, intval($month), intval($year));

        $attendances = Attendance::whereIn('user_id', $users->pluck('id'))
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->groupBy('user_id');

        $todayStats = Attendance::whereIn('user_id', $users->pluck('id'))
            ->whereDate('date', today())
            ->get()
            ->groupBy('status')
            ->map->count();

        return view('admin.attendance.index', compact(
            'users', 'attendances', 'month', 'year', 'daysInMonth', 'employeeId', 'todayStats'
        ));
    }

    public function checkIn(Request $request)
    {
        $request->validate(['date' => "required|date|before_or_equal:9999-12-31"]);

        $attendance = Attendance::firstOrCreate(
            ['user_id' => auth()->id(), 'date' => $request->date],
            ['status' => 'present']
        );

        if (!$attendance->check_in) {
            $tz = auth()->user()->timezone ?? 'Asia/Kolkata';
            $attendance->update([
                'check_in' => Carbon::now($tz)->format('H:i:s'),
                'status' => 'present',
            ]);
            return back()->with('success', 'Check-in recorded successfully.');
        }

        return back()->with('info', 'Already checked in today.');
    }

    public function checkOut(Request $request)
    {
        $request->validate(['date' => "required|date|before_or_equal:9999-12-31"]);

        $attendance = Attendance::where('user_id', auth()->id())
            ->where('date', $request->date)
            ->first();

        if (!$attendance) {
            return back()->with('error', 'Please check in first.');
        }

        if ($attendance->check_out) {
            return back()->with('info', 'Already checked out today.');
        }

        $tz = auth()->user()->timezone ?? 'Asia/Kolkata';
        $attendance->update(['check_out' => Carbon::now($tz)->format('H:i:s')]);

        return back()->with('success', 'Check-out recorded successfully.');
    }

    public function markAttendance(Request $request)
    {
        $authUser = auth()->user();
        if (!$authUser->isSuperAdmin() && $request->user_id != $authUser->id) {
            return back()->with('error', 'You are only authorized to mark attendance for yourself.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => "required|date|before_or_equal:9999-12-31",
            'status' => 'required|in:present,absent,half-day,leave',
            'remarks' => 'nullable|string|max:500',
        ]);

        Attendance::updateOrCreate(
            ['user_id' => $request->user_id, 'date' => $request->date],
            [
                'status' => $request->status,
                'remarks' => $request->remarks,
                'check_in' => $request->status === 'present' ? ($request->check_in ?? '09:00:00') : null,
                'check_out' => $request->status === 'present' ? ($request->check_out ?? '18:00:00') : null,
            ]
        );

        return back()->with('success', 'Attendance marked successfully.');
    }
}
