<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;

/**
 * Three separate pages over the same single CompanySetting row, split by
 * who should touch what: General (accounting/invoice profile) and Website
 * (public homepage copy) are both fine for a client's own Admin role to
 * edit — Ecommerce is the one page gated to Super Admin only (see
 * editEcommerce()), since turning that on/off is a vendor decision, not a
 * client one.
 */
class SettingController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:settings.view', only: ['edit', 'editWebsite', 'editEcommerce']),
            new Middleware('permission:settings.edit', only: ['update', 'updateWebsite', 'updateEcommerce']),
            new Middleware('role:Super Admin', only: ['editEcommerce', 'updateEcommerce']),
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

    public function editEcommerce()
    {
        return view('admin.settings.ecommerce', [
            'company' => CompanySetting::current(),
            'sites' => Site::where('status', true)->orderBy('name')->get(),
        ]);
    }

    public function updateEcommerce(Request $request)
    {
        $validated = $request->validate([
            'online_site_id' => ['nullable', 'integer', 'exists:sites,id'],
            'side_banner_1' => ['nullable', 'image', 'max:2048'],
            'remove_side_banner_1' => ['nullable', 'boolean'],
            'side_banner_2' => ['nullable', 'image', 'max:2048'],
            'remove_side_banner_2' => ['nullable', 'boolean'],
        ]);

        $company = CompanySetting::current();

        $data = [
            // Checkboxes omit the field entirely when unchecked, so read it directly.
            'ecommerce_enabled' => $request->boolean('ecommerce_enabled'),
            'online_site_id' => $validated['online_site_id'] ?? null,
        ];

        foreach (['side_banner_1', 'side_banner_2'] as $field) {
            $pathField = "{$field}_path";

            if ($request->boolean("remove_{$field}") && $company->$pathField) {
                Storage::disk('public')->delete($company->$pathField);
                $data[$pathField] = null;
            }

            if ($request->hasFile($field)) {
                if ($company->$pathField) {
                    Storage::disk('public')->delete($company->$pathField);
                }
                $data[$pathField] = $request->file($field)->store('company', 'public');
            }
        }

        $company->update($data);

        return redirect()->route('settings.ecommerce.edit')->with('success', 'E-commerce setting updated.');
    }
}
