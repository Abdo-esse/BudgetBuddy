<?php

namespace App\Http\Controllers;

use App\Models\ExpenseGroup;
use App\Http\Requests\StoreExpenseGroupRequest;
use App\Http\Requests\UpdateExpenseGroupRequest;

class ExpenseGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreExpenseGroupRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreExpenseGroupRequest $request)
{
    // Validation des données entrantes
    $validated = $request->validate();

    // Récupérer le groupe
    $group = Group::findOrFail($validated['expense_group']['group_id']);

    // Calculer les contributions si la méthode de division est "égal"
    if ($validated['expense_group']['methode_division'] == 'égal') {
        $totalUsers = count($validated['expenses_users']);
        $amountPerUser = $validated['expense_group']['total_prix'] / $totalUsers;

        // Vérification et calcul des contributions
        foreach ($validated['expenses_users'] as $index => $userExpense) {
            if ($userExpense['montant_contribution'] !== $amountPerUser) {
                return response()->json([
                    'error' => 'La contribution de chaque utilisateur doit être égale.',
                ], 400);
            }
        }
    }

    // Vérification de la somme des pourcentages si la méthode est "pourcentage"
    if ($validated['expense_group']['methode_division'] == 'pourcentage') {
        $totalPercentage = 0;
        foreach ($validated['expenses_users'] as $userExpense) {
            $totalPercentage += $userExpense['pourcentage'];
        }

        if ($totalPercentage != 100) {
            return response()->json([
                'error' => 'La somme des pourcentages doit être égale à 100%.',
            ], 400);
        }

        // Calcul des contributions basées sur les pourcentages
        foreach ($validated['expenses_users'] as $userExpense) {
            $userExpense['montant_contribution'] = ($userExpense['pourcentage'] / 100) * $validated['expense_group']['total_prix'];
        }
    }

    // Création de la dépense de groupe
    $expenseGroup = ExpenseGroup::create([
        'group_id' => $validated['expense_group']['group_id'],
        'title' => $validated['expense_group']['title'],
        'total_prix' => $validated['expense_group']['total_prix'],
        'description' => $validated['expense_group']['description'],
        'methode_division' => $validated['expense_group']['methode_division'],
    ]);

    // Enregistrement des utilisateurs et de leurs contributions
    foreach ($validated['expenses_users'] as $userExpense) {
        $expenseGroup->users()->attach(
            $userExpense['user_id'],
            [
                'montant_contribution' => $userExpense['montant_contribution'],
                'is_payer' => $userExpense['is_payer'],
                'pourcentage' => $userExpense['pourcentage'] ?? null,
            ]
        );
    }

    return response()->json($expenseGroup, 201);
}


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ExpenseGroup  $expenseGroup
     * @return \Illuminate\Http\Response
     */
    public function show(ExpenseGroup $expenseGroup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateExpenseGroupRequest  $request
     * @param  \App\Models\ExpenseGroup  $expenseGroup
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateExpenseGroupRequest $request, ExpenseGroup $expenseGroup)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ExpenseGroup  $expenseGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(ExpenseGroup $expenseGroup)
    {
        //
    }
}
