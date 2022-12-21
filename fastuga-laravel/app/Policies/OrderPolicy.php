<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

//    public function __construct()
//    {
//        $this->middleware('auth'); //só se pode fazer algo se tiver autenticado

//$currentUser = Auth::user();
//    }

    public function update(User $user, Order $order): bool
    {
        $status = request()->status;
        if($status == 'C'){
            if($user->type != 'EM'){
                return false;
            }
            return true;
        }

        if($user->type == 'ED'){
            return true;
        }

        return false;
    }

    public function viewCustomerOrders(User $user): bool
    {
        return $user->id == request()->user_id;
    }

    public function viewDeliveryOrders(User $user): bool
    {
        return $user->type == 'ED';
    }
}
