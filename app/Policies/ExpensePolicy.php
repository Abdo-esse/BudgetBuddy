<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExpensePolicy
{
    use HandlesAuthorization;

    public function modify(User $user,Post $post): Response
    {
        return $user->id=== $post->user_id
        ? Response::allow()
        : Response::deny('You do own this post');
    }
    
}
