<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateOrderItemsRequest;
use App\Http\Requests\StoreUpdateOrderRequest;
use App\Http\Resources\OrderItemsResource;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function getOrdersStatus()
    {
        return Order::groupBy('status')->pluck('status');
    }

    public function getOrderByStatus(String $status)
    {
        $status = strtoupper($status);
        if ($status == 'P' or  $status == 'R' or $status == 'D' or $status == 'C') {
            return Order::where('status', $status)->with("orderItems", "orderItems.product")->get();
        }
    }

    public function getOrderForDelivery(Request $request)
    {
        $query = Order::query();
        $ticketNumber = $request->query('ticket');
        if($ticketNumber != null){
            $query->where('ticket_number',$ticketNumber);
        }

        return $query->whereIn('status', ['P', 'R'])
                     ->with("orderItems", "orderItems.product")
                     ->orderBy('status', 'desc')
                     ->orderBy('id','asc')->paginate(10);

    }

    public function getOrderByStatusTAES()
    {
        return OrderResource::collection(Order::where('status', 'R')->orWhere('status', 'P')->get());
    }

    public function getUnassignedOrders(){
        $orders = Order::whereNotNull('custom')->get();
        // return $orders;
        $subset = $orders->filter(function ($order){
            return json_decode($order->custom)->assigned==null;
        });
        // $subset = $orders->map(function ($order) {
        //     // return
        //     return collect($order)
        //             ->whereNotNull(json_decode($order->custom)->address)
        //             ->all();
        // });
        return OrderResource::collection($subset);
        // dd($order);
        // return json_decode($order->custom)->address;
    }

    public function getOrderOfOrderItems(OrderItems $orderItems)
    {
        return new OrderResource($orderItems->order);
    }

    public function index(Request $request)
    {
        $query = Order::query();
        $status = $request->query('status');
        if($status != null){
            $query->where('status',$status);
        }

        $date = $request->query('date');
        if($date != null){
            $query->where('date',$date);
        }

        $ticketNumber = $request->query('ticket');
        if($ticketNumber != null){
            $query->where('ticket_number',$ticketNumber);
        }

        return $query
            ->with("orderItems", "orderItems.product")
            ->orderBy('id','DESC')->paginate(10);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try{
            $orderRequest = new StoreUpdateOrderRequest($request->all());
            //só deixar fazer pedidos ou user == null ou user == customer
            $newTicketNumber = Order::orderBy('id', 'DESC')->first()->ticket_number;
            $newTicketNumber++;
            if($newTicketNumber > 99){
                $newTicketNumber = 1;
            }
            $orderRequest['ticket_number'] = $newTicketNumber;
            $validatedOrder = $orderRequest->validate($orderRequest->rules());
            $newOrder = Order::create($validatedOrder);
            $newOrder->save();

            $orderItemsArray = $request->order_items;
            $local_number = 0;
            foreach($orderItemsArray as $item){
                $this->store_each_order_item($item, $newOrder['id'], $local_number);
                $local_number++;
            }

            if($newOrder['customer_id'] != null){
                $customer = Customer::find($newOrder['customer_id']);
                $previousPoints = $customer->points;
                $customer->points = $previousPoints - $newOrder['points_used_to_pay'] + $newOrder['points_gained'];
                $customer->save();
            }

            DB::commit();
            return new OrderResource($newOrder);
        }catch(\Exception $e){
            DB::rollBack();
            return response($e->getMessage());
        }
    }

    public function storeTAES(StoreUpdateOrderRequest $request) {
        $order = new Order($request->validated());
        $order->custom = json_encode($request["custom"]);
        $order->save();
        return new OrderResource($order);
    }

    function store_each_order_item($item, $order_id, $local_number){
        $status = $item['type'] == 'hot dish' ? 'W' : 'R';
        $itemRequest = new StoreUpdateOrderItemsRequest([
            'order_id' => $order_id,
            'order_local_number' => $local_number,
            'product_id' => $item['id'],
            'status' => $status,
            'price' => $item['price'],
            'preparation_by' => null,
            'notes' => $item['notes'] != 'null'? $item['notes'] : null,
            'custom' => null
        ]);
        $validateItem = $itemRequest->validate($itemRequest->rules());
        $newItem = OrderItems::create($validateItem);
        $newItem->save();
    }

    public function show(Order $order)
    {
        return new OrderResource($order);
    }

    public function update(StoreUpdateOrderRequest $request, Order $order)
    {
        $order->fill($request->validated());
        $order->custom = json_encode($request["custom"]);
        $order->save();
        return new OrderResource($order);
    }

    public function destroy(Order $order)
    {
        $order->delete();
    }

    //Statistics - Customer
    public function getAllCustomerOrders(User $user)
    {
        $id = Customer::where('user_id', $user->id)->pluck('id');
        return Order::where('customer_id', $id[0])->with("orderItems", "orderItems.product")->paginate(10);
    }

    public function getCustomerCurrentOrders($user_id)
    {
        $id = Customer::where('user_id', $user_id)->get('id');
        $currentOrdersStatus = ['P', 'R'];

        return Order::where('customer_id', $id[0]->id)
                    ->whereIn('status', $currentOrdersStatus)
                    ->get();
    }
    //Statistics - Manager - Orders
    public function getTotalOrdersByMonth()
    {
        $items = Order::orderBy('month', 'ASC')->groupBy('month')
        ->selectRaw('MONTH(date) as month, sum(id) as sum')
        ->pluck('month','sum');

        foreach($items as $key =>$item){
         switch ($item){
                        case 1:
                            $item = 'Janeiro';
                            break;
                        case 2:
                            $item = 'Fevereiro';
                            break;
                        case 3:
                            $item = 'Março';
                            break;
                        case 4:
                            $item = 'Abril';
                            break;
                        case 5:
                            $item = 'Maio';
                            break;
                        case 6:
                            $item = 'Junho';
                            break;
                        case 7:
                            $item = 'Julho';
                            break;
                        case 8:
                            $item = 'Agosto';
                            break;
                        case 9:
                            $item = 'Setembro';
                            break;
                        case 10:
                            $item = 'Outubro';
                            break;
                        case 11:
                            $item = 'Novembro';
                            break;
                        default:
                        $item = 'Dezembro';
                    }
            $total[$item] = $key;
        }
        return $total;
    }

    //Statistics - Manager - Orders - faturacao
    public function getTotalGainedByMonth()
    {
        $items = Order::orderBy('month', 'ASC')->where('status', '=', 'D')->groupBy('month')
        ->selectRaw('MONTH(date) as month, sum(total_paid) as sum')
        ->pluck('month','sum');

        foreach($items as $key =>$item){
            switch ($item){
                case 1:
                    $item = 'Janeiro';
                    break;
                case 2:
                    $item = 'Fevereiro';
                    break;
                case 3:
                    $item = 'Março';
                    break;
                case 4:
                    $item = 'Abril';
                    break;
                case 5:
                     $item = 'Maio';
                     break;
                case 6:
                    $item = 'Junho';
                    break;
                case 7:
                    $item = 'Julho';
                    break;
                case 8:
                    $item = 'Agosto';
                   break;
                case 9:
                     $item = 'Setembro';
                     break;
                case 10:
                    $item = 'Outubro';
                    break;
               case 11:
                    $item = 'Novembro';
                    break;
               default:
                $item = 'Dezembro';
            }
             $total[$item] = $key;
        }
        return $total;
    }

    //Statistics - Driver
    public function getAllOrdersDelivered($user)
    {
        $orders = Order::where('delivered_by', $user)->with("orderItems", "orderItems.product")->paginate(10);
        return $orders;
    }

    public function updateOrderStatus(Request $request, Order $order, $status)
    {
        $userId = $request['userId'];
        $employee = User::find($userId);
        if($employee == null){
            return response("Couldn't find current logged user", 403);
        }

        if($status == 'C'){
            if($employee->type != 'EM'){
                return response("Current logged user isn't a manager!", 403);
            }

            if($order->customer != null){
                $customer = $order->customer;
                $customer->points = $customer->points + $order->points_used_to_pay - $order->points_gained;
                $customer->save();
            }
        }else{
            if($employee->type != 'ED'){
                return response("Current logged user isn't an delivery employee!", 403);
            }

            if($status == 'R'){
                if(!$this->checkIfOrderIsReady($order)){
                    //TODO - confirmar se isto nao vai entrar em conflito com os items que ja estão do db:seed
                    return response("Selected order still has items to prepare!", 403);
                }
            }else if($status == 'D'){
                $order->delivered_by = $userId;
            }
        }

        $order->status = $status;
        $order->save();
        return new OrderResource($order);
    }

    public function checkIfOrderIsReady(Order $order)
    {
        foreach ($order->orderItems as $item){
            if($item->status == 'W' || $item->status == 'P'){
                return false;
            }
        }
        return true;
    }

    public function getItemsAndProducts($order_id)
    {
        return OrderItemsResource::collection(OrderItems::where('order_id', $order_id)->get());
    }

    public function getNumberOfActiveOrders()
    {
        return Order::whereIn('status', ['P','R'])->count();
    }
}
