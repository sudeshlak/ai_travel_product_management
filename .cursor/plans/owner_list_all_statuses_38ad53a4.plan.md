---
name: Owner list all statuses
overview: Stop filtering the authenticated owner product index to Active-only so inactive products appear for the owner; keep Active + currently-valid filters only on the public search path.
todos:
  - id: index-no-status-filter
    content: "Pass status: null in ProductController::index ProductSearchCriteria"
    status: completed
  - id: update-index-test
    content: Update feature test so owner index includes inactive products
    status: completed
isProject: false
---

# Show inactive products on owner index

## Problem

[`ProductController::index`](backend/app/Http/Controllers/Api/V1/ProductController.php) builds criteria with `status: ProductStatus::Active`, so inactive products never appear in `GET /api/v1/products`. Validity is already off (`onlyValid: false`); search correctly keeps Active + valid.

## Change

In `index`, pass `status: null` (no status filter) and keep `onlyValid: false`, still scoped to `$request->user()->id`:

```php
$paginator = $this->products->list(new ProductSearchCriteria(
    status: null,
    onlyValid: false,
    userId: $request->user()->id,
    page: $request->integer('page', 1),
    perPage: $request->integer('per_page', 15),
));
```

[`ProductSearchService`](backend/app/Services/ProductSearchService.php) stays unchanged (`status: Active`, `onlyValid: true`).

## Tests

Update [`test_index_returns_only_authenticated_users_active_products`](backend/tests/Feature/ProductControllerTest.php):

- Rename to reflect owned products of any status
- Assert both owned Active and owned Inactive appear (`assertJsonCount(2, ...)`, `meta.total` = 2)
- Still exclude another user’s products
- Keep relation/structure assertions on the known Active row (or assert by status paths)

No search-test changes.
