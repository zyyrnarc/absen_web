<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function showProfile()
    {
        return redirect()->route('setting');
    }

    public function updateProfile(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$admin->id,
            'phone' => 'nullable|string|max:30',
            'avatar' => 'nullable|image|max:4096',
        ]);

        if ($request->hasFile('avatar')) {
            if ($admin->avatar_path) {
                Storage::disk('public')->delete($admin->avatar_path);
            }

            $validated['avatar_path'] = $request->file('avatar')->store('admin-avatars', 'public');
        }

        unset($validated['avatar']);

        $admin->update($validated);

        return back()->with('success', 'Profil admin berhasil diperbarui.');
    }

    public function showSetting()
    {
        $admin = Auth::guard('admin')->user();
        $setting = AppSetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'company_name' => 'EduAdmin Company',
                'company_address' => '',
                'company_logo_path' => null,
            ]
        );

        return view('admin.setting.index', compact('admin', 'setting'));
    }

    public function updateSetting(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'required|string|max:1000',
            'company_logo' => 'nullable|image|max:2048',
        ]);

        $setting = AppSetting::query()->firstOrCreate(['id' => 1]);

        if ($request->hasFile('company_logo')) {
            if ($setting->company_logo_path) {
                Storage::disk('public')->delete($setting->company_logo_path);
            }

            $validated['company_logo_path'] = $request->file('company_logo')->store('company-logos', 'public');
        }

        unset($validated['company_logo']);

        $setting->update($validated);

        return back()->with('success', 'Company settings updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if (! Hash::check($validated['current_password'], $admin->password)) {
            return back()->withErrors([
                'current_password' => 'Your current password is not correct.',
            ])->withInput();
        }

        $admin->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password admin berhasil diperbarui.');
    }
}
