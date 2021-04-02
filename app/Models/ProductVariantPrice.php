<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantPrice extends Model
{

    protected $fillable = [
        "price",
        "stock",
        "product_id",
        "created_at",
        "product_variant_one",
        "product_variant_two",
        "product_variant_three"
    ];
    public function scopeRelated($q)
    {
        return $q->with("variant_one", "variant_two", "variant_three");
    }

    public function variant_one()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_one')->withDefault();
    }

    public function variant_two()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_two')->withDefault();
    }

    public function variant_three()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_three')->withDefault();
    }
}
