<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Group;
use App\Models\ExpenseGroup;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ExpenseGroupResource;
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
    $group = Group::find($group_id);

    if (!$group) {
        return response()->json([
            'error' => 'Le groupe spécifié est introuvable.',
        ], 404);
    }

    $expenseGroups = ExpenseGroup::where('group_id', $group_id->id)->with('users')->get();

    if ($expenseGroups->isEmpty()) {
        return response()->json([
            'error' => 'Aucune dépense trouvée pour ce groupe.',
        ], 404);
    }

    return ExpenseGroupResource::collection($expenseGroups);
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

    public function balances($group_id)
    {
    $group = Group::find($group_id);

    if (!$group) {
        return response()->json([
            'error' => 'Le groupe spécifié est introuvable.',
        ], 404);
    }

    $expenseGroups = ExpenseGroup::where('group_id', $group_id->id)->with('users')->get();

    if ($expenseGroups->isEmpty()) {
        return response()->json([
            'error' => 'Aucune dépense trouvée pour ce groupe.',
        ], 404);
    }
                
            $totalPrise = $expenseGroups->sum('total_prix');

            
            $groups = $group->load('users');

            
            $nusers = $groups->sum(function($group) {
                return $group->users->count();  
            });
            $dettes=$nusers > 0 ? $totalPrise / $nusers : 0;
            $results = DB::table('users as u')
                ->join('group_user as gu', 'u.id', '=', 'gu.user_id')
                ->join('expenses_users as eu', 'u.id', '=', 'eu.user_id')
                ->where('gu.group_id', 4)
                ->select('u.*', 'gu.*', 'eu.*')
                ->get();

            return response()->json([
                'data' => $results ,  
            ], 200);
                }
}
