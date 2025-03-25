<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;


class ExpenseUser extends Pivot
{
    use HasFactory;
    protected $table = 'expenses_users';

    protected $fillable = ['expense_group_id', 'user_id', 'montant_contribution', 'is_payer', 'pourcentage'];
}
