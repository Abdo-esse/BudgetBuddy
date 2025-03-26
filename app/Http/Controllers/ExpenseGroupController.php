<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Group;
use App\Models\ExpenseUser;
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
        $group = Group::find($group_id->id);
    
        if (!$group) {
            return response()->json([
                'error' => 'Le groupe spécifié est introuvable.',
            ], 404);
        }
    
        $expenseGroups = ExpenseGroup::where('group_id', $group_id->id)
            ->with('users')->get();
    
        if ($expenseGroups->isEmpty()) {
            return response()->json([
                'error' => 'Aucune dépense trouvée pour ce groupe.',
            ], 404);
        }
    
        $totalPrise = $expenseGroups->sum('total_prix');
        // return $totalPrise;
        $nusers = $group->users->count();
    
        $AmontTotal_Contribution = DB::table('users as u')
        ->join('expenses_users as eu', 'u.id', '=', 'eu.user_id')
        ->join('expenses_groups as eg', 'eu.expense_group_id', '=', 'eg.id')
        ->where('eg.group_id', $group_id->id)
        ->groupBy('u.id', 'u.name')
        ->select('u.id', 'u.name', DB::raw('SUM(eu.montant_contribution) AS total_contribution'))
        ->get();
    
        $dettes = $nusers > 0 ? $totalPrise / $nusers : 0;
        $newli = [];
    
        foreach ($group->users as $user) {
            $newli[$user->name] = $this->CalculateAmount($user, round($dettes, 2), $AmontTotal_Contribution);
        }
    
        return response()->json([
            'balances' => $newli,
            'dettes' => round($dettes, 2)
        ], 200);
    }
    
    public function CalculateAmount($user, ?float $account_per_person, $AmontTotal_Contribution)
    {
        foreach ($AmontTotal_Contribution as $person) {
            if ($user->name == $person->name) {
                $difference = round($account_per_person - $person->total_contribution, 2);
                if ($difference > 0) {
                    return "+" . $difference ;
                } elseif ($difference == 0) {
                    return "Équilibré";
                } else {
                    return "vous ete besoin " . abs($difference) ;
                }
            }
        }
        return "-" . round($account_per_person, 2);
    }
    
}
    
    

