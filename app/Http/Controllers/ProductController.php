<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Variant;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use App\Models\ProductVariantPrice;
use App\Services\Product as ServicesProduct;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request $request)
    {

        // dd($request->all());
        $products = Product::with("variant_prices")
            ->Filter($request)
            ->paginate(2);
        $variants = Variant::with('product_variations')->get()->map(function ($data) {
            return [
                "title" => $data->title,
                "product_variations" => collect($data->product_variations)->unique('variant'),
            ];
        });
        // dd($variants->toArray());
        return view('products.index', compact('products', 'variants'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $product = new Product();
            $product->fill($request->all())->save();

            $productService = new ServicesProduct();
            $productService->storeVariant($request, $product->id);
            $productService->storeProductPrices($request, $product->id);
            DB::commit();
            return response()->json("Success", 201);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $product = Product::with(["variants", "variant_prices"])->findOrFail($id);
        $variants = Variant::all();
        return view('products.edit', compact('variants', 'product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        try {
            DB::beginTransaction();
            $product->fill($request->all())->save();

            $productService = new ServicesProduct();
            $productService->clearPreviousVariations($product->id);
            $productService->storeVariant($request, $product->id);
            $productService->storeProductPrices($request, $product->id);
            DB::commit();
            return response()->json("Updated", 201);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
