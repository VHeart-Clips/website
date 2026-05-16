<?php

declare(strict_types=1);

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TermsController extends Controller
{
    public function __invoke(Request $request): View
    {
        $locale = app()->getLocale();

        return view('legal', ['locale' => $locale, 'type' => 'terms']);
    }
}
