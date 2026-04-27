<?php

declare(strict_types=1);

namespace App\Http\Controllers\Broadcaster;

use App\Enums\Clips\ClipStatus;
use App\Enums\FeatureFlag;
use App\Http\Controllers\Controller;
use App\Http\Requests\Broadcaster\OnboardingRequest;
use App\Models\Broadcaster\Broadcaster;
use App\Models\Broadcaster\BroadcasterConsentLog;
use App\Support\FeatureFlag\Feature;
use Filament\Facades\Filament;

class OnboardingSubmitController extends Controller
{
    public function __invoke(OnboardingRequest $request)
    {
        $broadcaster = Broadcaster::withTrashed()->updateOrCreate(['id' => auth()->id()], [
            'consent' => $request->array('consent'),
            'submit_user_allowed' => $request->boolean('everyone'),
            'submit_vip_allowed' => $request->boolean('vips'),
            'submit_mods_allowed' => $request->boolean('moderators'),
            'default_clip_status' => $request->enum('default_clip_status', ClipStatus::class, ClipStatus::Unknown),
            'onboarded_at' => now(),
            'deleted_at' => null,
        ]);

        BroadcasterConsentLog::create([
            'broadcaster_id' => $broadcaster->id,
            'state' => $broadcaster->consent?->values(),
            'changed_by' => auth()->id(),
            'change_reason' => 'Self Onboarding',
            'changed_at' => now(),
        ]);

        $fallbackRoute = Feature::isActive(FeatureFlag::UserDashboard)
            ? Filament::getPanel('dashboard')->getUrl($broadcaster)
            : route('home');

        return redirect()->intended($fallbackRoute);
    }
}
