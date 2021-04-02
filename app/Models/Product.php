<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];

    public function scopeFilter($q, $request)
    {
        return $q->where(function ($productData) use ($request) {
            if ($request->title) {
                $productData->where('title', 'LIKE', '%' . $request->title . '%');
            }
            if ($request->date) {
                $productData->whereDate('created_at', '=', $request->date);
            }
        })->when($request->variant, function ($productData) use ($request) {
            $productData->whereHas('variant_prices', function ($q) use ($request) {
                $q->where('product_variant_one', $request->variant)
                    ->orWhere('product_variant_two', $request->variant)
                    ->orWhere('product_variant_three', $request->variant);
            });
        })->when($request->price_from, function ($productData) use ($request) {
            $productData->whereHas('variant_prices', function ($q) use ($request) {
                $q->whereBetween('price', [$request->price_from, $request->price_to]);
            });
        });
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, "product_id");
    }

    public function variant_prices()
    {
        return $this->hasMany(ProductVariantPrice::class, 'product_id')->Related();
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, "product_id");
    }
}
