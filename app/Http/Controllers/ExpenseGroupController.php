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

    public function balances($group)
    {
        $group = $this->getGroupById($group->id);
        if (!$group) return $this->errorResponse('Le groupe spécifié est introuvable.', 404);
    
        $expenseGroups = $this->getExpenseGroups($group->id);
        if ($expenseGroups->isEmpty()) return $this->errorResponse('Aucune dépense trouvée pour ce groupe.', 404);
    
        $totalPrise = $expenseGroups->sum('total_prix');
        $AmontTotal_Contribution = $this->calculateTotalContribution($group->id);
        $balances = $this->calculateBalances($group, $totalPrise, $AmontTotal_Contribution);
        $transactions = $this->optimizeTransactions($balances);
    
        return response()->json([
            'transactions' => $transactions,
            'balances' => $balances
        ], 200);
    }
    
    /** 🔹 Récupère le groupe ou retourne null si non trouvé */
    private function getGroupById($group_id)
    {
        return Group::find($group_id);
    }
    
    /** 🔹 Récupère les dépenses du groupe */
    private function getExpenseGroups($group_id)
    {
        return ExpenseGroup::where('group_id', $group_id)->with('users')->get();
    }
    
    /** 🔹 Calcule la contribution totale de chaque utilisateur */
    private function calculateTotalContribution($group_id)
    {
        return DB::table('users as u')
            ->join('expenses_users as eu', 'u.id', '=', 'eu.user_id')
            ->join('expenses_groups as eg', 'eu.expense_group_id', '=', 'eg.id')
            ->where('eg.group_id', $group_id)
            ->groupBy('u.id', 'u.name')
            ->select('u.id', 'u.name', DB::raw('SUM(eu.montant_contribution) AS total_contribution'))
            ->get()
            ->keyBy('id');
    }
    
    /** 🔹 Calcule le solde de chaque utilisateur (positif = créancier, négatif = débiteur) */
    private function calculateBalances($group, $totalPrise, $AmontTotal_Contribution)
    {
        $nusers = $group->users->count();
        $dettesParPersonne = $nusers > 0 ? $totalPrise / $nusers : 0;
    
        $balances = [];
        foreach ($group->users as $user) {
            $contribution = $AmontTotal_Contribution[$user->id]->total_contribution ?? 0;
            $balances[$user->id] = round($contribution - $dettesParPersonne, 2);
        }
    
        return $balances;
    }
    
    /** 🔹 Optimise les transactions en réduisant le nombre de virements */
    private function optimizeTransactions($balances)
    {
        $debiteurs = [];
        $creanciers = [];
        
        foreach ($balances as $userId => $solde) {
            if ($solde < 0) {
                $debiteurs[] = ['id' => $userId, 'montant' => abs($solde)];
            } elseif ($solde > 0) {
                $creanciers[] = ['id' => $userId, 'montant' => $solde];
            }
        }
    
        $transactions = [];
        
        while (!empty($debiteurs) && !empty($creanciers)) {
            $debiteur = &$debiteurs[0];
            $creancier = &$creanciers[0];
    
            $montant = min($debiteur['montant'], $creancier['montant']);
            
            $transactions[] = [
                'from' => $debiteur['id'],
                'to' => $creancier['id'],
                'amount' => $montant
            ];
    
            $debiteur['montant'] -= $montant;
            $creancier['montant'] -= $montant;
    
            if ($debiteur['montant'] == 0) array_shift($debiteurs);
            if ($creancier['montant'] == 0) array_shift($creanciers);
        }
    
        return $transactions;
    }
    
    /** 🔹 Retourne une réponse d'erreur JSON */
    private function errorResponse($message, $status)
    {
        return response()->json(['error' => $message], $status);
    }
    
    
}
    
    

