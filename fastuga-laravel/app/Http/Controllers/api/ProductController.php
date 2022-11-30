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
        App::setlocale("pt");
        return  ProductResource::collection(Product::all())->sortBy('name', SORT_LOCALE_STRING)->sort()->values();
    }

    public function store(StoreUpdateProductRequest $request)
    {
        $newProduct = Product::create($request->validated());
        
        if($request->hasFile('photo_url')){
            $path = Storage::putFile('public/products',  $request->file('photo_url'));
            $name = basename($path);
            $newProduct["photo_url"] = $name;
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
        $product->update($request->validated());
        return new ProductResource($product);
    }

    public function destroy(Product $product)
    {
        $product->delete();
    }
}
