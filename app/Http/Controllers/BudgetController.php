<?php

namespace App\Http\Controllers;

use App\ExpenseCategory;
use App\Http\Requests\BudgetRequest;
use App\Models\Budget;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $budgets = Auth::user()->budgets()->get();
        return view('dashboard', [
            'budgets' => $budgets
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('budgets.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BudgetRequest $request)
    {
        $data = $request->validated();

        //$user_id = Auth::id();

        // $budget = Budget::create([
        //     'name' => $data['name'],
        //     'amount' => $data['amount'],
        //     'type' => $data['type'],
        //     'user_id' => $user_id
        // ]);

        $budget = Auth::user()->budgets()->create($data);

        return redirect()->route('dashboard')->with('success', 'Presupuesto creado correctamente');
    }

    /**
     * Display the specified resource.
     */
     #[Authorize('view', 'budget')]
    public function show(Budget $budget)
    {
        // Consulta    
        // $expenses = Expense::where('budget_id', $budget->id)->latest()->get();

        $budget->load([
            'expenses' => fn($query) => $query->latest()
        ]);

        return Inertia::render('Budgets/Show', [
            'budget' => $budget,
            'categories' => collect(ExpenseCategory::cases())->map(fn ($category) => [
                'value' => $category->value,
                'label' => $category->label()
            ]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    #[Authorize('update', 'budget')]
    public function edit(Budget $budget)
    {
        return view('budgets.edit', [
            'budget' => $budget
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    #[Authorize('update', 'budget')]
    public function update(BudgetRequest $request, Budget $budget)
    {
        $budget->update($request->validated());
        return redirect()->route('dashboard')->with('success', 'Presupuesto actualizado correctamente');
    }

    #[Authorize('delete', 'budget')]
    public function destroy(Budget $budget)
    {
        $budget->delete();
        return redirect()->route('dashboard')->with('success', 'Presupuesto eliminado correctamente');
    }
}
