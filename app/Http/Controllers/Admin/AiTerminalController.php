<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AiTerminalService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;

class AiTerminalController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:accounts.view'),
        ];
    }

    public function index()
    {
        return view('admin.ai-terminal.index');
    }

    public function ask(Request $request, AiTerminalService $service)
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'max:300'],
        ]);

        $reply = $service->answer($validated['query'], Auth::user()->current_site_id);

        return response()->json(['reply' => $reply]);
    }
}
