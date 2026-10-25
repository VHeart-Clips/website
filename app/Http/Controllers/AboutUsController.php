<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\FeatureFlag;
use App\Support\FeatureFlag\Feature;
use Illuminate\Http\Request;

class AboutUsController extends Controller
{
    public function __invoke(Request $request)
    {
        if (Feature::isActive(FeatureFlag::AboutUsAsIndex)) {
            return to_route('home');
        }

        return view('about-us');
    }
}
