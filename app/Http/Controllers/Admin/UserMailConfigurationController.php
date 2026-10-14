<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\UserMailConfiguration;
use App\Services\UserMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserMailConfigurationController extends Controller
{
    public function index(Request $request)
    {
        $authUser = Auth::user();
        $targetUserId = $authUser->id;

        $isSuperAdmin = $authUser->isSuperAdmin();
        $userList = [];

        if ($isSuperAdmin) {
            $userList = User::where('status', 'active')->orderBy('first_name')->get();
            if ($request->filled('user_id')) {
                $targetUserId = (int) $request->user_id;
            }
        }

        $user = User::findOrFail($targetUserId);
        $config = UserMailConfiguration::firstOrNew(['user_id' => $user->id]);

        return view('admin.mail_configuration.index', compact('user', 'config', 'isSuperAdmin', 'userList'));
    }

    public function update(Request $request)
    {
        $authUser = Auth::user();
        $targetUserId = $authUser->id;

        if ($authUser->isSuperAdmin() && $request->filled('user_id')) {
            $targetUserId = (int) $request->user_id;
        }

        $user = User::findOrFail($targetUserId);

        $validated = $request->validate([
            'provider' => 'required|string|in:smtp,gmail,outlook,custom',
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer|min:1|max:65535',
            'mail_encryption' => 'nullable|string|in:tls,ssl,none,null',
            'mail_username' => 'required|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $config = UserMailConfiguration::firstOrNew(['user_id' => $user->id]);
        $config->provider = $validated['provider'];
        $config->mail_host = $validated['mail_host'];
        $config->mail_port = $validated['mail_port'];
        $config->mail_encryption = $validated['mail_encryption'] === 'none' ? null : $validated['mail_encryption'];
        $config->mail_username = $validated['mail_username'];
        $config->mail_from_address = $validated['mail_from_address'] ?: $validated['mail_username'];
        $config->mail_from_name = $validated['mail_from_name'] ?: $user->full_name;
        $config->is_active = $request->has('is_active') ? (bool) $request->is_active : true;

        if (!empty($validated['mail_password'])) {
            $config->mail_password = $validated['mail_password'];
        }

        $config->save();

        ActivityLog::log('user_mail_config_updated', "Updated mail configuration for user: {$user->full_name}", $user);

        return redirect()->route('admin.mail-configuration.index', ['user_id' => $user->id])
            ->with('success', 'Email configuration saved successfully.');
    }

    public function test(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'test_email' => 'required|email',
            'mail_host' => 'nullable|string',
            'mail_port' => 'nullable|integer',
            'mail_encryption' => 'nullable|string',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
        ]);

        $authUser = Auth::user();
        $targetUserId = $authUser->id;

        if ($authUser->isSuperAdmin() && $request->filled('user_id')) {
            $targetUserId = (int) $request->user_id;
        }

        $user = User::findOrFail($targetUserId);
        $config = UserMailConfiguration::firstOrNew(['user_id' => $user->id]);

        // If user submitted on-the-fly parameters for testing before saving
        if ($request->filled('mail_host')) {
            $config->mail_host = $request->mail_host;
            $config->mail_port = $request->mail_port ?: 587;
            $config->mail_encryption = $request->mail_encryption === 'none' ? null : $request->mail_encryption;
            $config->mail_username = $request->mail_username;
        }

        $rawPassword = $request->input('mail_password');
        if (empty($rawPassword) && !$config->has_password) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Please enter an SMTP/App password to test connection.'], 422);
            }
            return back()->with('error', 'Please enter an SMTP/App password to test connection.');
        }

        $result = UserMailService::testConnection($config, $request->test_email, $rawPassword);

        if ($request->ajax()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }
}
