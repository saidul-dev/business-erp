<?php

namespace App\Http\Controllers;

class LanguageController extends Controller
{
    /**
     * Switch the UI language, persisted in session (see SetLocale middleware).
     */
    public function switch(string $locale)
    {
        abort_unless(array_key_exists($locale, config('app.available_locales')), 404);

        session(['locale' => $locale]);

        return redirect()->back();
    }
}
