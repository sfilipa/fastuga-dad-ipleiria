<?php

namespace App\Policies;

use App\Models\User;
use App\Models\OrderItems;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderItemsPolicy
{
    use HandlesAuthorization;
//    public function __construct()
//    {
//        //
//    }

    public function viewHotDishes(User $user): bool
    {
        return $user->type == 'EC';
    }

    public function update(User $user): bool
    {
        return $user->type == 'EC';
    }

}
