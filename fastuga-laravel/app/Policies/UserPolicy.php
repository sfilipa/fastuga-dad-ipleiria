<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;
    public function view(User $user/*, User $model*/)//com o model nao funciona
    {
        return $user->type == "EM";// || $user->id == $model->id;
    }
    public function update(User $user, /*User $model*/)
    {
        return $user->type == "EM"; //|| $user->id == $model->id;
    }
    public function updatePassword(User $user/*, User $model*/)
    {
        return true /*== $model->id*/;
    }
}
