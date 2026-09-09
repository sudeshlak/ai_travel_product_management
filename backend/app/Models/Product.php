<?php

namespace App\Models;

use App\Enums\ProductStatus;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'category_id',
    'product_name',
    'description',
    'price',
    'inventory_count',
    'valid_from',
    'valid_until',
    'status',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'inventory_count' => 'integer',
            'valid_from' => 'date',
            'valid_until' => 'date',
            'status' => ProductStatus::class,
        ];
    }

    /**
     * Scope: products whose validity window covers the current date.
     *
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function valid(Builder $query): void
    {
        $today = now()->toDateString();

        $query
            ->whereDate('valid_from', '<=', $today)
            ->whereDate('valid_until', '>=', $today);
    }

    /**
     * Scope: products whose validity window has passed.
     *
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function expired(Builder $query): void
    {
        $query->whereDate('valid_until', '<', now()->toDateString());
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsToMany<Destination, $this>
     */
    public function destinations(): BelongsToMany
    {
        return $this->belongsToMany(Destination::class)->withTimestamps();
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
