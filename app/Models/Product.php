<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'category_id',
        'shop_id',
        'name',
        'description',
        'condition',
        'is_maintenance'
    ];

    protected $casts = [
        'is_maintenance' => 'boolean'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function rentals()
    {
        return $this->hasMany(ProductRental::class);
    }

      public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

        public function orders()
    {
        return $this->hasManyThrough(
            Order::class,
            ProductRental::class,
            'product_id',        // FK di product_rentals
            'product_rental_id', // FK di orders
            'id',                // PK di products
            'id'                 // PK di product_rentals
        );
    }
 
}
