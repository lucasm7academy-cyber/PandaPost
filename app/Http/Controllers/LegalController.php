<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class LegalController extends Controller
{
    public function terms(): Response
    {
        return Inertia::render('legal/Terms', [
            'lastUpdated' => '2026-05-29',
            'contactEmail' => config('mail.from.address'),
            'sections' => __('legal.terms.sections'),
        ]);
    }

    public function privacy(): Response
    {
        return Inertia::render('legal/Privacy', [
            'lastUpdated' => '2026-05-29',
            'contactEmail' => config('mail.from.address'),
            'sections' => __('legal.privacy.sections'),
        ]);
    }
}
