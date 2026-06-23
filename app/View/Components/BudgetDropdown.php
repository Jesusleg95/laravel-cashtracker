<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BudgetDropdown extends Component
{
    public $budget;

    public function __construct($budget)
    {
        $this->budget = $budget;
    }

    public function render(): View|Closure|string
    {
        return view('components.budget-dropdown');
    }
}
