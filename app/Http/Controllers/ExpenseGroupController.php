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
    public function store(StoreExpenseGroupRequest $request,$group_id)
    {
        // Validation des données entrantes
        $validated = $request->validate();
    
        // Vérification que le groupe existe
        $group = Group::findOrFail($group_id);
    
        // Récupérer les données de la dépense
        $expenseGroup = ExpenseGroup::create([
            'group_id' => $group_id,
            'title' => $validated['expense_group']['title'],
            'total_prix' => $validated['expense_group']['total_prix'],
            'description' => $validated['expense_group']['description'],
            'methode_division' => $validated['expense_group']['methode_division'],
        ]);
    
        // Si la méthode de division est "égal", on ne vérifie pas que tout le monde paye également
        if ($validated['expense_group']['methode_division'] == 'égal') {
            $payers = array_filter($validated['expenses_users'], fn($user) => $user['is_payer'] == true);
    
            // Cas où un seul utilisateur paie tout
            if (count($payers) == 1) {
                // Dans ce cas, on ne vérifie pas les montants, car une seule personne paye tout
                $payer = reset($payers);
                // La contribution de l'utilisateur qui paie sera égale à tout le total
                $payer['montant_contribution'] = $validated['expense_group']['total_prix'];
                $validated['expenses_users'] = [$payer];
            } else {
                // Si plusieurs utilisateurs payent, la somme des contributions doit être égale au montant total
                $totalContributions = array_sum(array_column($validated['expenses_users'], 'montant_contribution'));
            
                // Vérification que la somme des contributions correspond au montant total
                if ($totalContributions !== $validated['expense_group']['total_prix']) {
                    return response()->json([
                        'error' => 'La somme des contributions des utilisateurs doit être égale au montant total de la dépense.',
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
