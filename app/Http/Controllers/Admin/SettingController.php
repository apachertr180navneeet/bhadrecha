<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Exception;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        try {
            foreach ($request->except('_token') as $key => $value) {
                Setting::set($key, $value);
            }

            logActivity('Updated', 'Settings', 'Updated system settings');
            return redirect()->route('admin.settings.index')->with('success', 'Settings updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
