<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:settings.view', only: ['edit']),
            new Middleware('permission:settings.edit', only: ['update']),
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

        // Checkboxes omit the field entirely when unchecked, so read it directly.
        $validated['ecommerce_enabled'] = $request->boolean('ecommerce_enabled');

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
}
