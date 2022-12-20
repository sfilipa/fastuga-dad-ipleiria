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

        return $query->whereIn('status', ['P', 'R'])->with("orderItems", "orderItems.product")->orderBy('id','asc')->paginate(10);

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

        return $query->orderBy('id','DESC')->paginate(10);
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
            'notes' => null,
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
    public function getAllCustomerOrders($user_id)
    {
        $id = Customer::where('user_id', $user_id)->get('id');
        return Order::where('customer_id', $id[0]->id)->get();
    }

    public function getCustomerCurrentOrders($user_id)
    {
        $id = Customer::where('user_id', $user_id)->get('id');
        $currentOrdersStatus = ['P', 'R'];

        return Order::where('customer_id', $id[0]->id)
                    ->whereIn('status', $currentOrdersStatus)
                    ->get();
    }

    public function getAllOrderProducts($order_id)
    {
        $products_id = OrderItems::where('order_id', $order_id)->get('product_id');

        $allProducts = Product::whereIn('id', $products_id)->get();

        return $allProducts;
    }

    //Statistics - Manager - Orders
    public function getTotalOrdersByMonth()
    {
        $items = Order::orderBy('month', 'ASC')->groupBy('month')
        ->selectRaw('MONTH(date) as month, sum(id) as sum')
        ->pluck('month','sum');

        $i=0;
        foreach($items as $key =>$item){
            $total[$i] = $key;
            $i++;
        }

        return $total;
    }

    public function getTotalOrdersMonths()
    {
        $items = Order::orderBy('month', 'ASC')->groupBy('month')
        ->selectRaw('MONTH(date) as month, sum(id) as sum')
        ->pluck('month','sum');

        $i=0;
        foreach($items as $key =>$item){
            $months[$i] = $item;

            switch ($months[$i]){
                case 1:
                    $months[$i] = 'Janeiro';
                    break;
                case 2:
                    $months[$i] = 'Fevereiro';
                    break;
                case 3:
                    $months[$i] = 'Março';
                    break;
                case 4:
                    $months[$i] = 'Abril';
                    break;
                case 5:
                    $months[$i] = 'Maio';
                    break;
                case 6:
                    $months[$i] = 'Junho';
                    break;
                case 7:
                    $months[$i] = 'Julho';
                    break;
                case 8:
                    $months[$i] = 'Agosto';
                    break;
                case 9:
                    $months[$i] = 'Setembro';
                    break;
                case 10:
                    $months[$i] = 'Outubro';
                    break;
                case 11:
                    $months[$i] = 'Novembro';
                    break;
                default:
                $months[$i] = 'Dezembro';
            }
            $i++;
        }

        return $months;
    }

    //Statistics - Driver
    public function getAllOrdersDelivered(int $user_id)
    {
        $orders = Order::where('delivered_by', $user_id)->paginate(10);
        return $orders;
    }

    public function cancelOrder(Order $order){
        if($order->customer != null){
            $customer = $order->customer;
            $customer->points = $customer->points + $order->points_used_to_pay - $order->points_gained;
            $customer->save();
        }
        $order->status = 'C';
        $order->save();
        return new OrderResource($order);
    }

    public function updateOrderStatus(Request $request, Order $order, $status)
    {
        $userId = $request['userId'];
        $employee = User::find($userId);
        if($employee == null){
            return response("Couldn't find current logged user", 403);
        }

        if($employee->type != 'ED'){
            return response("Current logged user isn't an delivery employee!", 403);
        }

        if($status == 'R'){
            if(!$this->checkIfOrderIsReady($order)){
                //TODO - confirmar se isto nao vai entrar em conflito com os items que ja estão do db:seed
                return response("Selected order still has items to prepare!", 403);
            }
        }
        $order->delivered_by = $userId;
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
