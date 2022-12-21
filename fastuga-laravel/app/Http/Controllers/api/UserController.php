<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\StoreUpdateCustomerRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateUserPasswordTAESRequest;
use App\Http\Requests\UpdateUserNameTAESRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\UserResource;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.manager', ['except' => [
            'getUserOfOrderItems',
            'getUserOfCustomer',
            'getUserOfOrder',
            'show',
            'show_me',
            'store',
            'update',
            'updateTAESPassword',
            'updateTAESName',
            'destroy',
            'destroyWithEmail'
        ]]);
    }

    public function getUserOfOrderItems(OrderItems $orderItems) {
        return new UserResource($orderItems->user);
    }

    public function getUserOfCustomer(Customer $customer) {
        return new UserResource($customer->user);
    }

    public function getUserOfOrder(Order $order) {
        return new UserResource($order->user);
    }

    public function index()
    {
        return UserResource::collection(User::all());
    }

    public function show(User $user)
    {
        return new UserResource($user);
    }

    public function store(StoreUserRequest $request)
    {
       $request->validated();//validate password without hash

       $request->query->add(['password' => Hash::make($request->password)]);
        $validatedData = $request->validated();

        if($request->has('photo_url')){
            $image_64 = $request["photo_url"];

            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png, ...

            //  image data to decode (eg: data:image/png;base64,imgData..)
            $replace = substr($image_64, 0, strpos($image_64, ',')+1); //

            // find substring to replace
            $image = str_replace($replace, '', $image_64);
            $image = str_replace(' ', '+', $image);

            $imageName = Str::random(16).'.'.$extension;

            Storage::put('public/fotos/'.$imageName, base64_decode($image));
            $validatedData["photo_url"] = $imageName;
        }

       $newUser = User::create($validatedData);

        return new UserResource($newUser);
    }

    public function update(UpdateUserRequest $userRequest, User $user)
    {

        $customerRequest = new StoreUpdateCustomerRequest($userRequest->all());
        DB::beginTransaction();

        try {

            $validatedData = $userRequest->validated();
            //$user->update($userRequest->validated());

            if($userRequest->has('photo_url') && Str::length($userRequest["photo_url"]) > 21){

                // Delete Existing Photo
                if(Storage::disk('public')->exists('fotos/'.$user->photo_url)) {
                    Storage::disk('public')->delete('fotos/'.$user->photo_url);
                }
                $image_64 = $userRequest["photo_url"];

                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png, ...

                //  image data to decode (eg: data:image/png;base64,imgData..)
                $replace = substr($image_64, 0, strpos($image_64, ',')+1); //

                // find substring to replace
                $image = str_replace($replace, '', $image_64);
                $image = str_replace(' ', '+', $image);

                 $imageName = Str::random(16).'.'.$extension;
                 Storage::put('public/fotos/'.$imageName, base64_decode($image));
                 $validatedData["photo_url"] = $imageName;
            }

            $user->fill($validatedData);
            $user->save();
            $responseUser = new UserResource($user);

            if($user->type == 'C'){
                $customerRequest->validate($customerRequest->rules());
                $customer = $user->customer;
                $customer->update($customerRequest->all());
                $responseCustomer = new CustomerResource($customer);
            }
            DB::commit();
            return response()->json("Good", 204);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json($e->getMessage(), 400);
        }
    }

    public function blockUnblockUser(UpdateUserRequest $userRequest, User $user)
    {
        DB::beginTransaction();
        try {

            $validatedData = $userRequest->validated();
            if($userRequest->hasFile('photo_url')){
                // Delete Existing Photo
                if(Storage::disk('public')->exists('fotos/'.$user->photo_url)) {
                    Storage::disk('public')->delete('fotos/'.$user->photo_url);
                }
                // Save New Photo
                $path = Storage::putFile('public/fotos',  $userRequest->file('photo_url'));
                $name = basename($path);
                $validatedData["photo_url"] = $name;
            }

            $user->fill($validatedData);
            $user->save();
            $responseUser = new UserResource($user);
            DB::commit();
            return response()->json("Good", 204);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json($e->getMessage(), 400);
        }
    }

    public function update_password(UpdatePasswordRequest $request, User $user)
    {
       $this->authorize('updatePassword', $user);// substitui middleware - mudar dps

        if (!Hash::check($request->get('current_password'), $user->password))
        {
            throw ValidationException::withMessages(['current_password' => 'Current Password is incorrect']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return new UserResource($user);
    }

    public function updateTAESPassword(UpdateUserPasswordTAESRequest $request, string $email)
    {
            try {

            $user = User::where('email', $email)->firstOrFail();

            $userpassword = $request->validated();

            User::whereId($user->id)->update([
                'password' => Hash::make($request->password)
            ]);

            return new UserResource($user);
        } catch (\Exception $e) {
        return response()->json($e->getMessage(), 400);
    }
    }
    public function updateTAESName(UpdateUserNameTAESRequest $request, string $email)
    {
        try {

            $user = User::where('email', $email)->firstOrFail();

            $userName = $request->validated();

            User::whereId($user->id)->update([
                'name' => $request->name
            ]);

            return new UserResource($user);
        }catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    public function destroy(User $user)
    {
        $user = User::find($user->id);
        $user->delete();
    }

    public function destroyWithEmail(string $email)
    {
        $user = User::where('email', $email)->get();

        if($user[0]->type == 'C'){
            $customer = Customer::where('user_id', $user[0]->id)->get();
            $customer->each->delete();
        }
        $user->each->delete();
    }

    public function show_me(Request $request)
    {
        return new UserResource($request->user());
    }

    public function getAllEmployees(Request $request) {
        $query = User::query()->whereIn('type', array('ec', 'ed', 'em'))
            ->whereNull('users.deleted_at');

        $name = $request->query('name');
        if($name != null){
            $query->where('users.name','LIKE','%'.$name.'%');
        }

        $type = $request->query('type');
        if($type != null && $type!='A'){
            $query->where('users.type',$type);
        }

        return $query->orderBy('users.name','ASC')->paginate(15);
    }
}
