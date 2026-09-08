---
name: ER Models SoftDeletes
overview: Create Laravel Eloquent models and MySQL-oriented migrations for every entity in `er.mmd`, with soft deletes, proper column types, FKs, and the product–destination pivot.
todos:
  - id: enums
    content: Add ProductStatus, OrderStatus, PaymentMethod PHP enums
    status: completed
  - id: migrations
    content: Add softDeletes to users + create all entity/pivot migrations with MySQL types
    status: completed
  - id: models
    content: Create Category, Destination, Product, Order, OrderItem; update User with SoftDeletes + relations
    status: completed
  - id: mysql-env
    content: Point .env and .env.example to MySQL connection defaults
    status: completed
isProject: false
---

# Models + migrations from ER diagram

## Scope

From [`backend/docs/er/er.mmd`](backend/docs/er/er.mmd), add migrations and models for:

- `Category`, `Destination`, `Product`, `Order`, `OrderItem`
- Update existing [`User`](backend/app/Models/User.php) for soft deletes + relationships
- Pivot `destination_product` for `PRODUCT }|--|{ Destination`

Migrations are included because MySQL types and `deleted_at` live there, not in models alone.

## Naming / conventions

Laravel defaults (not ER camelCase):

| ER | Table | Model |
|---|---|---|
| USER | `users` (existing) | `User` |
| CATEGORY | `categories` | `Category` |
| Destination | `destinations` | `Destination` |
| PRODUCT | `products` | `Product` (`id`, not `productId`) |
| ORDER | `orders` | `Order` |
| ORDER-ITEM | `order_items` | `OrderItem` |

Implied FKs from relationships:

- `products.user_id` → publisher
- `products.category_id` → category
- `orders.user_id` → placer
- `order_items.order_id`, `order_items.product_id`
- Pivot: `destination_product(product_id, destination_id)` unique pair (no soft delete on pivot)

## MySQL column types

| Column | Type |
|---|---|
| PKs | `BIGINT UNSIGNED` via `$table->id()` |
| `email`, `code` | `VARCHAR` unique |
| `password` | `VARCHAR` (existing) |
| names | `VARCHAR(255)` |
| `description` | `TEXT` |
| `price` (product + order item snapshot) | `DECIMAL(12,2)` unsigned — money, not int |
| `inventory_count` | `UNSIGNED INT` |
| `valid_from` / `valid_until` | `DATE` |
| product `status` | MySQL `ENUM('Active','Inactive')` |
| order `status` | `ENUM('requested','rejected','approved')` |
| `payment_method` | `ENUM('card','cash')` |
| FKs | `foreignId()->constrained()` |
| soft delete | `softDeletes()` → `deleted_at` TIMESTAMP NULL |
| audit | `timestamps()` on all main tables |

Also add PHP enums under `app/Enums/` (`ProductStatus`, `OrderStatus`, `PaymentMethod`) and cast them on models.

## Soft deletes

Use `Illuminate\Database\Eloquent\SoftDeletes` on: `User`, `Category`, `Destination`, `Product`, `Order`, `OrderItem`.

- New migration to add `deleted_at` to existing `users` table
- Soft deletes on all new entity tables
- Pivot: hard attach/detach only (no `deleted_at`)

## Models + relationships

```
User       hasMany Product, hasMany Order
Category   hasMany Product
Destination belongsToMany Product
Product    belongsTo User, Category; belongsToMany Destination; hasMany OrderItem
Order      belongsTo User; hasMany OrderItem
OrderItem  belongsTo Order, Product
```

Match existing Laravel 13 style on `User` (`#[Fillable]`, `casts()` method).

## Files to create / change

**Enums:** `app/Enums/ProductStatus.php`, `OrderStatus.php`, `PaymentMethod.php`

**Migrations (ordered):**

1. `add_soft_deletes_to_users_table`
2. `create_categories_table`
3. `create_destinations_table`
4. `create_products_table`
5. `create_destination_product_table`
6. `create_orders_table`
7. `create_order_items_table`

**Models:** `Category`, `Destination`, `Product`, `Order`, `OrderItem` + update `User`

**Config:** set [`backend/.env`](backend/.env) (and `.env.example`) to `DB_CONNECTION=mysql` with local defaults (`127.0.0.1`, `3306`, database `ai_travel_product_management`). You will need MySQL running and the database created before `php artisan migrate`.

## Out of scope

Controllers, seeders, factories, API routes, switching away from Laravel’s default `users.name` / auth columns.
