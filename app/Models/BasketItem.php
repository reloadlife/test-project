<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="BasketItem",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="basket_id", type="integer"),
 *     @OA\Property(property="product_id", type="integer"),
 *     @OA\Property(property="quantity", type="integer"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="product", ref="#/components/schemas/Product"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class BasketItem extends Model
{
    /** @use HasFactory<\Database\Factories\BasketItemFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'basket_id',
        'product_id',
        'quantity',
        'description',
    ];
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // Update basket total when item changes
        static::saved(function ($item) {
            $item->basket->updateTotalPrice();
        });

        static::deleted(function ($item) {
            $item->basket->updateTotalPrice();
        });
    }

    /**
     * Get the basket that owns the item.
     **/
    public function basket(): BelongsTo
    {
        return $this->belongsTo(Basket::class);
    }

    /**
     * Get the product associated with the basket item.
     **/
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
