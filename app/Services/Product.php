<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;

class Product
{
    public function storeVariant($data, $product_id)
    {
        foreach ($data->product_variant as $variant_value) {
            foreach ($variant_value['tags'] as $tag_value) {
                $productVariant = new ProductVariant();
                $productVariant->variant = $tag_value;
                $productVariant->variant_id = $variant_value['option'];
                $productVariant->product_id = $product_id;
                $productVariant->save();
            }
        }
        return "variantDone";
    }

    public function storeProductPrices($data, $product_id)
    {
        $priceData = [];
        $allVariant = ProductVariant::where('product_id', $product_id)->get();
        foreach ($data->product_variant_prices as $variantPrices) {
            $variations = explode("/", $variantPrices['title']);
            $variant1 = isset($variations[0]) && $variations[0] != "" ? $allVariant->where('variant', $variations[0])->first()->id : null;
            $variant2 = isset($variations[1]) && $variations[1] != "" ? $allVariant->where('variant', $variations[1])->first()->id : null;
            $variant3 = isset($variations[2]) && $variations[2] != "" ? $allVariant->where('variant', $variations[2])->first()->id : null;
            $priceData[] = [
                "product_variant_one" => $variant1,
                "product_variant_two" => $variant2,
                "product_variant_three" => $variant3,
                "price" => $variantPrices['price'],
                "stock" => $variantPrices['stock'],
                "product_id" => $product_id,
                "created_at" => date("Y-m-d H:i:s"),
            ];
        }
        ProductVariantPrice::insert($priceData);
        return "priceDone";
    }

    public function clearPreviousVariations($product_id)
    {
        ProductVariantPrice::where('product_id', $product_id)->delete();
        ProductVariant::where('product_id', $product_id)->delete();
    }
}
