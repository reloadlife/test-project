<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @OA\Schema(
 *     schema="Basket",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="total_price", type="integer"),
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/BasketItem")
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Basket extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'description',
        'total_price',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_price' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // Update total price when creating a new basket
        static::creating(function ($basket) {
            $basket->updateTotalPrice();
        });
    }

    public function updateTotalPrice(): void
    {
        $this->total_price = $this->items()
            ->join('products', 'basket_items.product_id', '=', 'products.id')
            ->selectRaw('SUM(products.price * basket_items.quantity) as total')
            ->value('total') ?? 0;
    }

    /**
     * Get the user that owns the basket.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items in the basket.
     */
    public function items(): HasMany
    {
        return $this->hasMany(BasketItem::class);
    }
}
