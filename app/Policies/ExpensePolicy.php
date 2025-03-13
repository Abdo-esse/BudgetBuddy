<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExpensePolicy
{
    use HandlesAuthorization;

    public function modify(User $user,Expense $expense): Response
    {
        return $user->id=== $expense->user_id
        ? Response::allow()
        : Response::deny('You do own this post');
    }
    
}
