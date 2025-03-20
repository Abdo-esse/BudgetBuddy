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
    public function index($group_id)
    {
      // Récupérer le groupe par ID
    $group = Group::find($group_id);

    // Vérifier si le groupe existe
    if (!$group) {
        return response()->json([
            'error' => 'Le groupe spécifié est introuvable.',
        ], 404);
    }

    // Récupérer toutes les dépenses associées à ce groupe
    $expenseGroups = ExpenseGroup::where('group_id', $group_id->id)->with('users')->get();

    // Vérifier si des dépenses sont trouvées
    if ($expenseGroups->isEmpty()) {
        return response()->json([
            'error' => 'Aucune dépense trouvée pour ce groupe.',
        ], 404);
    }

    // Préparer la réponse avec les dépenses et les utilisateurs associés
    $response = [];

    foreach ($expenseGroups as $expenseGroup) {
        $groupData = [
            'expense_group' => [
                'title' => $expenseGroup->title,
                'total_prix' => $expenseGroup->total_prix,
                'description' => $expenseGroup->description,
                'methode_division' => $expenseGroup->methode_division,
            ],
            'expenses_users' => []
        ];

        // Ajouter les utilisateurs associés à chaque dépense
        foreach ($expenseGroup->users as $user) {
            $groupData['expenses_users'][] = [
                'user_id' => $user->id,
                'montant_contribution' => $user->pivot->montant_contribution ?? null,
                'is_payer' => $user->pivot->is_payer,
            ];
        }

        $response[] = $groupData;
    }

    return response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreExpenseGroupRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreExpenseGroupRequest $request, $group_id)
    {
        $validated = $request->validated();

        $group = Group::validateGroup($group_id);
        if (!$group) {
            return response()->json(['error' => 'Le groupe spécifié est introuvable.'], 404);
        }

        $expenseGroup = ExpenseGroup::createExpenseGroup($group_id, $validated);

        ExpenseGroup::validateUserContributions($validated);

        if ($validated['expense_group']['methode_division'] == 'pourcentage') {
            ExpenseGroup::validatePercentages($validated);
        }

        ExpenseGroup::attachUsersToExpenseGroup($expenseGroup, $validated);

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
