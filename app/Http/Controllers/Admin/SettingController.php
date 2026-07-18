<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;

/**
 * Separate pages over the same single CompanySetting row, split by who
 * should touch what: General (accounting/invoice profile), Website
 * (public homepage copy), and Attendance are all fine for a client's own
 * Admin role to edit.
 */
class SettingController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:settings.view', only: ['edit', 'editWebsite', 'editAttendance']),
            new Middleware('permission:settings.edit', only: ['update', 'updateWebsite', 'updateAttendance']),
        ];
    }

    public function edit()
    {
        return view('admin.settings.edit', ['company' => CompanySetting::current()]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'currency' => ['required', 'string', 'max:10'],
            'vat_registration_no' => ['nullable', 'string', 'max:100'],
            'bin_no' => ['nullable', 'string', 'max:100'],
            'financial_year_start_month' => ['required', 'integer', 'between:1,12'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        $company = CompanySetting::current();

        if ($request->boolean('remove_logo') && $company->logo_path) {
            Storage::disk('public')->delete($company->logo_path);
            $validated['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('company', 'public');
        }

        $company->update(collect($validated)->except(['logo', 'remove_logo'])->all());

        return redirect()->route('settings.edit')->with('success', 'Business profile updated.');
    }

    public function editWebsite()
    {
        return view('admin.settings.website', ['company' => CompanySetting::current()]);
    }

    public function updateWebsite(Request $request)
    {
        $validated = $request->validate([
            'tagline' => ['nullable', 'string', 'max:255'],
            'about_text' => ['nullable', 'string', 'max:2000'],
            'mission_text' => ['nullable', 'string', 'max:1000'],
            'vision_text' => ['nullable', 'string', 'max:1000'],
            'values_text' => ['nullable', 'string', 'max:1000'],
            'hero_image' => ['nullable', 'image', 'max:4096'],
            'remove_hero_image' => ['nullable', 'boolean'],
        ]);

        $company = CompanySetting::current();

        if ($request->boolean('remove_hero_image') && $company->hero_image_path) {
            Storage::disk('public')->delete($company->hero_image_path);
            $validated['hero_image_path'] = null;
        }

        if ($request->hasFile('hero_image')) {
            if ($company->hero_image_path) {
                Storage::disk('public')->delete($company->hero_image_path);
            }
            $validated['hero_image_path'] = $request->file('hero_image')->store('company', 'public');
        }

        $company->update(collect($validated)->except(['hero_image', 'remove_hero_image'])->all());

        return redirect()->route('settings.website.edit')->with('success', 'Website content updated.');
    }

    public function editAttendance()
    {
        return view('admin.settings.attendance', ['company' => CompanySetting::current()]);
    }

    public function updateAttendance(Request $request)
    {
        $validated = $request->validate([
            'default_shift_start_time' => ['required', 'date_format:H:i'],
            'late_grace_minutes' => ['required', 'integer', 'min:0', 'max:180'],
        ]);

        CompanySetting::current()->update($validated);

        return redirect()->route('settings.attendance.edit')->with('success', 'Attendance settings updated.');
    }
}
