<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function view(User $user, User $model)
    {
        return $user->type == "EM" || $user->id == $model->id;
    }
    public function update(User $user, User $model)
    {
        return /*$user->type == "EM" ||*/ $user->id == $model->id;
    }
    public function updatePassword(User $user, User $model)
    {
        return $user->id == $model->id;
    }
    public function deliverHistory(User $user, User $model)
    {
        return $user->type == "ED" || $user->id == $model->id;
    }

    public function chefHistory(User $user, User $model)
    {
        return $user->type == "EC" || $user->id == $model->id;
    }

     public function customerHistory(User $user, User $model)
     {
        return $user->type == "C" || $user->id == $model->id;
     }
     public function statisticsManager(User $user)
     {
        return $user->type == "EM";
     }
}

