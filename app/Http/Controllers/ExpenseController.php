<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Models\Budget;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function store(ExpenseRequest $request, Budget $budget)
    {
        // $data = $request->validated();
        // Expense::create([
        //     'name' => $data['name'],
        //     'amount' => $data['amount'],
        //     'category' => $data['category'],
        //     'budget_id' => $budget->id
        // ]);

        $budget->expenses()->create($request->validated());
        
        return redirect()->route(
            'budgets.show', $budget
        )->with('success', 'Gasto Registrado Correctamente');
    }

    public function update(Request $request, Expense $expense)
    {
        //
    }

    public function destroy(Expense $expense)
    {
        //
    }
}
