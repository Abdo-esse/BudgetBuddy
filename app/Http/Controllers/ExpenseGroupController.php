<?php

namespace App\Http\Controllers;

use App\Models\Group;
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
        
        $validated = $request->validated();
        
        $group = Group::find($group_id);
        if (!$group) {
            return response()->json([
                'error' => 'Le groupe spécifié est introuvable.',
            ], 404);
        }
        // dd($group_id);
        $expenseGroup = ExpenseGroup::create([
            'group_id' => $group_id,
            'title' => $validated['expense_group']['title'],
            'total_prix' => $validated['expense_group']['total_prix'],
            'description' => $validated['expense_group']['description'],
            'methode_division' => $validated['expense_group']['methode_division'],
        ]);

        if ($validated['expense_group']['methode_division'] == 'égal') {
            $payers = array_filter($validated['expenses_users'], fn($user) => $user['is_payer'] == true);
            if (count($payers) == 1) {
                $payer = reset($payers);
                
                
                if ( $payer['montant_contribution'] !== $validated['expense_group']['total_prix']) {
                    return response()->json([
                        'error' => 'Le montant_contribution doit être égale au montant total de la dépense.',
                    ], 400);
                }
                $validated['expenses_users'] = [$payer];

            } else {
                $totalContributions = array_sum(array_column($validated['expenses_users'], 'montant_contribution'));
            
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
            $totalContributions = array_sum(array_column($validated['expenses_users'], 'montant_contribution'));
            
                if ($totalContributions !== $validated['expense_group']['total_prix']) {
                    return response()->json([
                        'error' => 'La somme des contributions des utilisateurs doit être égale au montant total de la dépense.',
                    ], 400);
                }
    
            
        }
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
