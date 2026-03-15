<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();
        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'amount' => 'required|numeric',
            'price' => 'required|numeric',
        ]);
        $product = Product::create($request->all());
        return response()->json([
            'message'=>'Produto criado com sucesso',
            'product'=>$product
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string',
            'amount' => 'required|numeric',
            'price' => 'required|numeric',
        ]);
        $product->update($request->all());
        return response()->json([
            'message'=>'Produto atualizado com sucesso',
            'product'=>$product
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json([
            'message'=>'Produto deletado'
        ]);
    }
}
