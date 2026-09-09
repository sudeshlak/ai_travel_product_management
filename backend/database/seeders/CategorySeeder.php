<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * @var list<string>
     */
    private array $names = [
        'Adventure',
        'Beach',
        'Cultural',
        'Food',
        'Wellness',
    ];

    public function run(): void
    {
        foreach ($this->names as $name) {
            Category::query()->updateOrCreate(
                ['name' => $name],
                ['name' => $name],
            );
        }
    }
}
