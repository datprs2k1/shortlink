<?php

namespace App\View\Components\Layouts;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class App extends Component
{
    public string $title;
    public ?string $actions;
    public ?array $breadcrumbs;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $title = 'ShortLink',
        ?string $actions = null,
        ?array $breadcrumbs = null
    ) {
        $this->title = $title;
        $this->actions = $actions;
        $this->breadcrumbs = $breadcrumbs;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.layouts.app');
    }
}
