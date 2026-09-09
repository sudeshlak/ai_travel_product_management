<?php

namespace Database\Seeders;

use App\Models\Destination;
use Illuminate\Database\Seeder;

class DestinationSeeder extends Seeder
{
    /**
     * @var list<string>
     */
    private array $names = [
        'Colombo',
        'Kandy',
        'Galle',
        'Ella',
        'Sigiriya',
    ];

    public function run(): void
    {
        foreach ($this->names as $name) {
            Destination::query()->updateOrCreate(
                ['name' => $name],
                ['name' => $name],
            );
        }
    }
}
