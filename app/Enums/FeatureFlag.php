<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Traits\FeatureFlagMagic;
use App\Enums\Traits\HasHeadlineLabel;
use App\Support\FeatureFlag\Attributes\DefaultFeatureFlagState;
use App\Support\FeatureFlag\Attributes\Description;
use App\Support\FeatureFlag\Attributes\Environment;
use Filament\Support\Contracts\HasLabel;

enum FeatureFlag: string implements HasLabel
{
    use FeatureFlagMagic;
    use HasHeadlineLabel;

    #[Description('Controls the Clip Submission feature')]
    #[DefaultFeatureFlagState(true)]
    case ClipSubmission = 'clip_submission';

    #[Description('Controls the Clip Voting feature')]
    #[DefaultFeatureFlagState(true)]
    case ClipVoting = 'clip_voting';

    #[Description('Controls the Reporting feature')]
    #[DefaultFeatureFlagState(true)]
    case Reports = 'reporting';

    #[Description('Bypasses the Broadcaster Consent scope on Clips, useful for debugging locally')]
    #[Environment(['local', 'staging'])]
    case IgnoreBroadcasterConsentOnClipScope = 'bypass_broadcaster_consent';
}
