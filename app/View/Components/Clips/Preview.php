<?php

declare(strict_types=1);

namespace App\View\Components\Clips;

use App\Models\Clip;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Preview extends Component
{
    public function __construct(
        public Clip $clip,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.clips.preview', ['clip' => $this->clip]);
    }
}
