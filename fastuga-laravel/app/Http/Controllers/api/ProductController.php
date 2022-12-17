<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\OrderItems;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function getProductOfOrderItems(OrderItems $orderItems)
    {
        return new ProductResource($orderItems->product);
    }

    public function getProductsTypes()
    {
        return Product::groupBy('type')->pluck('type');
    }

    public function index()
    {
        // Get the collection and sort it
        $collection = Product::all();
        $sortedCollection = $collection->sortBy(function ($item) {
            return Str::ascii($item->name);
        });

        // Return the sorted collection as a resource
        return ProductResource::collection($sortedCollection);
    }

    public function store(StoreUpdateProductRequest $request)
    {
        $newProduct = Product::create($request->validated());
        
        if($request->hasFile('photo_url')){
            $path = Storage::putFile('public/products',  $request->file('photo_url'));
            $name = basename($path);
            $newProduct["photo_url"] = $name;
            $capitalizedName = ucfirst($newProduct["name"]);
            $newProduct["name"] = $capitalizedName;
            $newProduct->save();

            return new ProductResource($newProduct);
        }

    }

    public function show(Product $product)
    {
        return new ProductResource($product);
    }

    public function update(StoreUpdateProductRequest $request, Product $product)
    {
        $validatedData = $request->validated();

        // Check if New Photo Uploaded
        // if($request->hasFile('photo_url')){
        if($request->has('photo_url')){
            // Delete Existing Photo
            if(Storage::disk('public')->exists('products/'.$product->photo_url)) {
                Storage::disk('public')->delete('products/'.$product->photo_url);
            }

            $image_64 = $request["photo_url"];

            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png, ... 
            //  image data to decode (eg: data:image/png;base64,imgData..)
            $replace = substr($image_64, 0, strpos($image_64, ',')+1); // 
          
            // find substring to replace      
            $image = str_replace($replace, '', $image_64); 
            $image = str_replace(' ', '+', $image); 
    
            $imageName = Str::random(16).'.'.$extension;
            
            Storage::put('public/products/'.$imageName, base64_decode($image));

            $validatedData["photo_url"] = $imageName;
        }
        
        $product->fill($validatedData);
        $product->save();

        return new ProductResource($product);
    }

    public function destroy(Product $product)
    {
        $product->delete();
    }


    //Manager Statistics - maybe change later
    public function getBestProducts()
    {
        $items = OrderItems::orderBy('sum', 'DESC')->groupBy('product_id')
        ->selectRaw('sum(product_id) as sum, product_id')
        ->pluck('sum','product_id')->take(5);
        
        $i=0;
        foreach($items as $key =>$item){
            $final[$i]= Product::where('id', $key)->value('name');  
            $i++;
        }
        return $final;
    }

    public function getTotalOrdersOfTopProducts()
    {
        $items = OrderItems::orderBy('sum', 'DESC')->groupBy('product_id')
        ->selectRaw('sum(product_id) as sum, product_id')
        ->pluck('sum','product_id')->take(5);
        
        $i=0;
        foreach($items as $key =>$item){
            $total[$i] = $item;
            $i++;
        }
        return $total;
    }

    public function getWorstProducts()
    {
        $items = OrderItems::orderBy('sum', 'ASC')->groupBy('product_id')
        ->selectRaw('sum(product_id) as sum, product_id')
        ->pluck('sum','product_id')->take(5);

        
        $i=0;
        foreach($items as $key =>$item){
            $final[$i]= Product::where('id', $key)->value('name');  
            if($final[$i] == null){
                $final[$i] = 'Undefined';
            }
            $i++;
        }
        return $final;
    }

    public function getTotalOrdersOfWorstProducts()
    {
        $items = OrderItems::orderBy('sum', 'ASC')->groupBy('product_id')
        ->selectRaw('sum(product_id) as sum, product_id')
        ->pluck('sum','product_id')->take(5);
        
        $i=0;
        foreach($items as $key =>$item){
            $total[$i] = $item;
            $i++;
        }
        return $total;
    }
}
