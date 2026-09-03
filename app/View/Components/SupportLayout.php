<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class SupportLayout extends Component
{
    public function render(): View
    {
        return view('support.layouts.app');
    }
}
