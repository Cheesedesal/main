<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        $settings = Setting::whereIn('key', ['system_name', 'contact_email', 'processing_days'])->pluck('value', 'key');

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate(['system_name' => ['required', 'string', 'max:120'], 'contact_email' => ['nullable', 'email', 'max:255'], 'processing_days' => ['required', 'integer', 'min:1', 'max:90']]);
        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        ActivityLogger::record('updated_settings', "{$request->user()->name} updated system settings.", null, $request);

        return back()->with('success', 'System settings updated.');
    }
}