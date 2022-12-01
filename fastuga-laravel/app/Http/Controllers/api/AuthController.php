<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Customer;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\StoreUpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\UserResource;

const PASSPORT_SERVER_URL = "http://localhost";
const CLIENT_ID = 2;
//const CLIENT_SECRET = 'NnwPusaTbdGZmiVbt8FV2VtjCITzYR7Abe4CqSEn'; 

const CLIENT_SECRET = 'vabnHSSpaYqEAQwyMNKQlFNMbW6bEv0S1dgTICN4';
class AuthController extends Controller
{
    private function passportAuthenticationData($username, $password)
    {
        return [
            'grant_type' => 'password',
            'client_id' => CLIENT_ID,
            'client_secret' => CLIENT_SECRET,
            'username' => $username,
            'password' => $password,
            'scope' => ''
        ];
    }

    public function login(Request $request)
    {
        try {
            request()->request->add($this->passportAuthenticationData($request->username,
                $request->password));
            $request = Request::create(PASSPORT_SERVER_URL . '/oauth/token', 'POST');
            $response = Route::dispatch($request);
            $errorCode = $response->getStatusCode();
            $auth_server_response = json_decode((string)$response->content(), true);
            return response()->json($auth_server_response, $errorCode);
        } catch (\Exception $e) {
            return response()->json('Authentication has failed!', 401);
        }
    }

    public function register(Request $request)
    {
        
        $userRequest = new StoreUserRequest($request->all());
        $customerRequest = new StoreUpdateCustomerRequest($request->all());
        try {
            $validationUser = $userRequest->validate($userRequest->rules());
            $customerRequest->query->add(['user_id' => 1]);  //TODO: Change this 
            $validationCustomer = $customerRequest->validate($customerRequest->rules());
            
            $newUser = User::create($validationUser);
            $responseUser = new UserResource($newUser);
            
            $customerRequest->query->add(['user_id' => $newUser->id]); 

            $newCustomer = Customer::create($validationCustomer);
            $responseCustomer = new CustomerResource($newCustomer);
            return response()->json("Good", 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), $e->status);
        }
    }

    public function logout(Request $request)
    {
        $accessToken = $request->user()->token();
        $token = $request->user()->tokens->find($accessToken);
        $token->revoke();
        $token->delete();
        return response(['msg' => 'Token revoked'], 200);
    }
}
