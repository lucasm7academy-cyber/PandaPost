<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class LandingController extends Controller
{
    public function __invoke(): Response|RedirectResponse
    {
        // Redirect authenticated users to the app dashboard
        if (Auth::check()) {
            return redirect()->route('app.home');
        }

        return Inertia::render('Landing');
    }
}
