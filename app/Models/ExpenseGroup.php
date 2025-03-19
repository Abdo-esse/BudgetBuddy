<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseGroup extends Model
{
    use HasFactory;

    protected $table = 'expenses_groups';

    protected $fillable = ['group_id', 'title', 'total_prix', 'description', 'methode_division'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'expenses_users')->withPivot('montant_contribution', 'is_payer', 'pourcentage');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }


    public static function createExpenseGroup($group_id, $validated)
    {
        return self::create([
            'group_id' => $group_id->id,
            'title' => $validated['expense_group']['title'],
            'total_prix' => $validated['expense_group']['total_prix'],
            'description' => $validated['expense_group']['description'],
            'methode_division' => $validated['expense_group']['methode_division'],
        ]);
    }

    public static function validateUserContributions(&$validated)
    {
        if ($validated['expense_group']['methode_division'] == 'égal') {
            $payers = array_filter($validated['expenses_users'], fn($user) => $user['is_payer'] == true);
            if (count($payers) == 1) {
                $payer = reset($payers);
                if ($payer['montant_contribution'] !== $validated['expense_group']['total_prix']) {
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
    }

    public static function validatePercentages($validated)
    {
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

    public static function attachUsersToExpenseGroup($expenseGroup, $validated)
    {
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
    }
}
