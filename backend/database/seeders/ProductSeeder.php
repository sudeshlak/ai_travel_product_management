<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->where('email', 'test@test.com')->first();

        if ($user === null) {
            throw new RuntimeException('Seed user test@test.com must exist before ProductSeeder runs.');
        }

        $categories = Category::query()->pluck('id', 'name');
        $destinations = Destination::query()->pluck('id', 'name');

        foreach (['Adventure', 'Beach', 'Cultural', 'Food', 'Wellness'] as $name) {
            if (! $categories->has($name)) {
                throw new RuntimeException("Missing seeded category: {$name}");
            }
        }

        foreach (['Colombo', 'Kandy', 'Galle', 'Ella', 'Sigiriya'] as $name) {
            if (! $destinations->has($name)) {
                throw new RuntimeException("Missing seeded destination: {$name}");
            }
        }

        Product::query()->where('user_id', $user->id)->forceDelete();

        $definitions = [
            ...$this->dinnerBuffets(),
            ...$this->airportTransfers(),
            ...$this->familyPackages(),
            ...$this->adventureProducts(),
            ...$this->beachProducts(),
            ...$this->culturalProducts(),
            ...$this->wellnessProducts(),
            ...$this->fillerProducts(),
        ];

        if (count($definitions) !== 100) {
            throw new RuntimeException('ProductSeeder must define exactly 100 products, got '.count($definitions).'.');
        }

        foreach ($definitions as $definition) {
            $product = Product::query()->create([
                'user_id' => $user->id,
                'category_id' => $categories[$definition['category']],
                'product_name' => $definition['product_name'],
                'description' => $definition['description'],
                'price' => $definition['price'],
                'inventory_count' => $definition['inventory_count'],
                'valid_from' => $definition['valid_from'],
                'valid_until' => $definition['valid_until'],
                'status' => $definition['status'],
            ]);

            $destinationIds = array_map(
                static fn (string $name): int => (int) $destinations[$name],
                $definition['destinations'],
            );

            $product->destinations()->sync($destinationIds);
        }
    }

    /**
     * @return list<array{
     *     product_name: string,
     *     description: string,
     *     category: string,
     *     destinations: list<string>,
     *     price: float,
     *     inventory_count: int,
     *     valid_from: string,
     *     valid_until: string,
     *     status: ProductStatus
     * }>
     */
    private function dinnerBuffets(): array
    {
        $endOfMonth = now()->endOfMonth()->toDateString();
        $from = now()->startOfMonth()->toDateString();

        $items = [
            [
                'product_name' => 'Dinner Buffet at Cinnamon Grand',
                'description' => 'International dinner buffet at Cinnamon Grand with live cooking stations and dessert counters.',
                'price' => 8500.00,
            ],
            [
                'product_name' => 'Hilton Dinner Buffet',
                'description' => 'Premium dinner buffet at Hilton with seafood and dessert counters.',
                'price' => 9200.00,
            ],
            [
                'product_name' => 'Galle Face Hotel Lunch Buffet',
                'description' => 'Classic lunch buffet overlooking the Indian Ocean at Galle Face Hotel.',
                'price' => 6500.00,
            ],
            [
                'product_name' => 'Shangri-La Sunday Brunch Buffet',
                'description' => 'Family-friendly Sunday brunch buffet with kids stations.',
                'price' => 11000.00,
            ],
            [
                'product_name' => 'Cinnamon Lakeside Dinner Buffet',
                'description' => 'Lakeside dinner buffet featuring Asian and Western favourites.',
                'price' => 7800.00,
            ],
            [
                'product_name' => 'Taj Samudra Dinner Buffet',
                'description' => 'Elegant dinner buffet service with live Indian and Sri Lankan kitchens.',
                'price' => 8900.00,
            ],
            [
                'product_name' => 'Kingsbury Seafood Dinner Buffet',
                'description' => 'Seafood-focused dinner buffet with fresh catch of the day.',
                'price' => 12500.00,
            ],
            [
                'product_name' => 'Jetwing Seven Lunch Buffet',
                'description' => 'Weekday lunch buffet ideal for business travellers.',
                'price' => 4500.00,
            ],
            [
                'product_name' => 'Mövenpick Dinner Buffet',
                'description' => 'International dinner buffet with Swiss pastry highlights.',
                'price' => 9800.00,
            ],
            [
                'product_name' => 'Cinnamon Grand High Tea Buffet',
                'description' => 'Afternoon high tea buffet at Cinnamon Grand with savoury and sweet selections.',
                'price' => 5200.00,
            ],
            [
                'product_name' => 'City Dinner Buffet Special',
                'description' => 'Affordable dinner buffet experience across partner hotels.',
                'price' => 3900.00,
            ],
            [
                'product_name' => 'Ramada Dinner Buffet',
                'description' => 'Relaxed dinner buffet with Sri Lankan rice and curry stations.',
                'price' => 5600.00,
            ],
            [
                'product_name' => 'Cinnamon Red Rooftop Dinner Buffet',
                'description' => 'Rooftop dinner buffet with city skyline views.',
                'price' => 7200.00,
            ],
            [
                'product_name' => 'Galadari Lunch Buffet',
                'description' => 'Central lunch buffet for groups and corporate guests.',
                'price' => 4800.00,
            ],
            [
                'product_name' => 'Cinnamon Grand Festival Dinner Buffet',
                'description' => 'Seasonal festival dinner buffet at Cinnamon Grand with special menus.',
                'price' => 10500.00,
            ],
        ];

        return array_map(
            fn (array $item): array => $this->definition(
                productName: $item['product_name'],
                description: $item['description'],
                category: 'Food',
                destinations: ['Colombo'],
                price: $item['price'],
                inventoryCount: 40,
                validFrom: $from,
                validUntil: $endOfMonth,
                status: ProductStatus::Active,
            ),
            $items,
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function airportTransfers(): array
    {
        $items = [
            ['Airport Transfer Private Car', 'Private airport transfer from Bandaranaike International Airport CMB to city hotels.', 4500.00],
            ['Airport Transfer Shared Van Service', 'Shared van airport transfer service between CMB and the city centre.', 2200.00],
            ['Premium Airport Transfer with Meet and Greet', 'Premium CMB airport transfer with meet and greet and luggage assistance.', 7500.00],
            ['Airport Transfer Night Service', 'Late-night airport transfer service for CMB arrivals into the city.', 5500.00],
            ['Airport Transfer SUV', 'Spacious SUV airport transfer for families travelling from CMB.', 6800.00],
            ['Express Airport Transfer to Fort Station', 'Fast airport transfer service from CMB to Fort railway station.', 3900.00],
            ['Airport Transfer Round Trip Package', 'Round-trip airport transfer package covering CMB pickup and hotel drop.', 8200.00],
            ['Private Airport Transfer for Groups', 'Private airport transfer minibus for group arrivals at CMB.', 9500.00],
            ['Airport Transfer Budget Shuttle', 'Budget shuttle airport transfer service from CMB to selected hotels.', 1800.00],
            ['Luxury Airport Transfer Chauffeur', 'Luxury chauffeur airport transfer service from CMB with complimentary water.', 12000.00],
        ];

        return array_map(
            fn (array $item): array => $this->definition(
                productName: $item[0],
                description: $item[1],
                category: 'Adventure',
                destinations: ['Colombo'],
                price: $item[2],
                inventoryCount: 25,
                validFrom: now()->subDays(7)->toDateString(),
                validUntil: now()->addMonths(2)->toDateString(),
                status: ProductStatus::Active,
            ),
            $items,
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function familyPackages(): array
    {
        $items = [
            ['Family Weekend Package', 'Family package for a weekend with kids activities and hotel stay.', ['Colombo'], 'Cultural', 15000.00],
            ['Family Heritage Package', 'Family package exploring temples and Botanical Gardens with kids-friendly pacing.', ['Kandy'], 'Cultural', 18500.00],
            ['Family Beach Package', 'Family package combining fort walks and beach time for parents and kids.', ['Galle'], 'Beach', 16500.00],
            ['Kids City Family Package', 'Family package with zoo, museum, and soft adventure for children.', ['Colombo'], 'Cultural', 9800.00],
            ['Two-City Family Package', 'Multi-day family package covering city and hill-country highlights with family rooms.', ['Colombo', 'Kandy'], 'Cultural', 32000.00],
            ['Southern Family Beach Package', 'Family beach package with surfing lessons for older kids.', ['Galle'], 'Beach', 21000.00],
            ['Family Package Temple and Tea Country', 'Family package combining cultural sites and nearby hill views.', ['Kandy'], 'Cultural', 24000.00],
            ['Family Food Trail Package', 'Family package tasting street food with kid-friendly stops.', ['Colombo'], 'Food', 7500.00],
            ['Fort Family Walking Package', 'Family package walking tour of the fort with ice cream and lighthouse photos.', ['Galle'], 'Cultural', 5600.00],
            ['Family Cultural Show Package', 'Evening family package with cultural dance performance and dinner.', ['Kandy'], 'Cultural', 8900.00],
            ['Beach and City Family Package', 'Family package linking city fun with a beach day.', ['Colombo', 'Galle'], 'Beach', 27500.00],
            ['Budget Family Day Trip Package', 'Affordable family package for a full day with lunch included.', ['Colombo'], 'Cultural', 4200.00],
        ];

        return array_map(
            fn (array $item): array => $this->definition(
                productName: $item[0],
                description: $item[1],
                category: $item[3],
                destinations: $item[2],
                price: $item[4],
                inventoryCount: 20,
                validFrom: now()->subDays(3)->toDateString(),
                validUntil: now()->addMonths(3)->toDateString(),
                status: ProductStatus::Active,
            ),
            $items,
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function adventureProducts(): array
    {
        $items = [
            ['Rock Sunrise Hike', 'Guided sunrise hiking adventure to the famous rock trail with packed breakfast.', ['Ella'], 6500.00],
            ['Little Adams Peak Trek', 'Short adventure trek to Little Adams Peak with panoramic hill views.', ['Ella'], 3500.00],
            ['Nine Arch Bridge Walk', 'Scenic walking adventure to Nine Arch Bridge.', ['Ella'], 2500.00],
            ['Rock Fortress Climb', 'Classic climb of the rock fortress with guide commentary.', ['Sigiriya'], 7800.00],
            ['Pidurangala Sunrise Climb', 'Adventure climb of Pidurangala for sunrise photos.', ['Sigiriya'], 5200.00],
            ['Demodara Rail Adventure', 'Train adventure experience around the Demodara loop.', ['Ella'], 4800.00],
            ['Village Cycling Adventure', 'Village cycling adventure through countryside trails.', ['Sigiriya'], 4100.00],
            ['Adventure Day Combo', 'Combined hiking and viewpoint adventure package.', ['Ella'], 9900.00],
            ['Wildlife Safari Add-on', 'Optional safari-style adventure near nearby parks.', ['Sigiriya'], 14500.00],
            ['Waterfall Adventure Walk', 'Adventure walk visiting nearby waterfalls.', ['Ella'], 3900.00],
            ['Climb and Museum Tour', 'Adventure climb plus museum visit.', ['Sigiriya'], 8600.00],
            ['Zipline Adventure', 'Adrenaline zipline adventure experience in the hills.', ['Ella'], 7200.00],
        ];

        return array_map(
            fn (array $item): array => $this->definition(
                productName: $item[0],
                description: $item[1],
                category: 'Adventure',
                destinations: $item[2],
                price: $item[3],
                inventoryCount: 30,
                validFrom: now()->subDays(5)->toDateString(),
                validUntil: now()->addMonths(2)->toDateString(),
                status: ProductStatus::Active,
            ),
            $items,
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function beachProducts(): array
    {
        $items = [
            ['Beach Day Escape', 'Relaxed beach day on the southern coast with sunbeds.', 5500.00],
            ['Coastal Sunset Tour', 'Coastal beach and sunset experience along the south shore.', 4800.00],
            ['Unawatuna Beach Day', 'Beach day transfer and lounge package to Unawatuna.', 6200.00],
            ['Surfing Starter Session', 'Beginner surfing session on southern beaches.', 7500.00],
            ['Fort and Beach Combo', 'Morning fort walk plus afternoon beach time.', 6900.00],
            ['Southern Beach Picnic Package', 'Beach picnic package with snacks on the coastline.', 4300.00],
            ['Snorkelling Beach Trip', 'Snorkelling beach trip with gear included.', 8800.00],
            ['Luxury Beach Cabana', 'Private beach cabana day experience.', 15000.00],
            ['Beach Photography Walk', 'Guided beach photography walk at golden hour.', 3600.00],
            ['Family Beach Fun Day', 'Kids-friendly beach games and swimming day.', 5100.00],
            ['Lagoon and Beach Tour', 'Lagoon boat ride plus a beach stop.', 9400.00],
            ['Weekend Beach Getaway', 'Overnight beach getaway package.', 22000.00],
        ];

        return array_map(
            fn (array $item): array => $this->definition(
                productName: $item[0],
                description: $item[1],
                category: 'Beach',
                destinations: ['Galle'],
                price: $item[2],
                inventoryCount: 28,
                validFrom: now()->subDays(4)->toDateString(),
                validUntil: now()->addMonths(2)->toDateString(),
                status: ProductStatus::Active,
            ),
            $items,
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function culturalProducts(): array
    {
        $items = [
            ['Temple of the Tooth Tour', 'Guided cultural visit to the Temple of the Tooth.', 4500.00],
            ['Cultural Dance Show', 'Traditional Kandyan cultural dance performance with reserved seats.', 3800.00],
            ['Heritage Walking Tour', 'Heritage walking tour of colonial and sacred sites.', 4200.00],
            ['Royal Botanical Gardens Visit', 'Cultural half-day visit to Peradeniya Botanical Gardens.', 3500.00],
            ['Museum and Temple Combo', 'Combined cultural tour of the museum and temple precinct.', 5600.00],
            ['Tea Heritage Cultural Experience', 'Cultural tea estate visit and tasting in the hills.', 7800.00],
            ['Evening Cultural Circuit', 'Evening cultural circuit covering viewpoints and temple lights.', 4900.00],
            ['Sacred City Cultural Day', 'Full-day cultural immersion across sacred sites.', 9200.00],
            ['Artisan Craft Workshop', 'Hands-on cultural craft workshop with local artisans.', 6100.00],
            ['Buddhist Heritage Tour', 'Focused cultural tour of Buddhist heritage landmarks.', 5300.00],
            ['Lake Cultural Stroll', 'Gentle cultural stroll around the lake with guide stories.', 2800.00],
            ['Hill Country Cultural Overview', 'Broad cultural overview tour of city highlights.', 6700.00],
        ];

        return array_map(
            fn (array $item): array => $this->definition(
                productName: $item[0],
                description: $item[1],
                category: 'Cultural',
                destinations: ['Kandy'],
                price: $item[2],
                inventoryCount: 22,
                validFrom: now()->subDays(6)->toDateString(),
                validUntil: now()->addMonths(3)->toDateString(),
                status: ProductStatus::Active,
            ),
            $items,
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function wellnessProducts(): array
    {
        $items = [
            ['Spa Retreat Half Day', 'Wellness spa retreat with massage and steam.', ['Colombo'], 9500.00],
            ['Ayurveda Wellness Session', 'Traditional Ayurveda wellness treatment session.', ['Kandy'], 8700.00],
            ['Beach Yoga Morning', 'Sunrise beach yoga wellness class.', ['Galle'], 3200.00],
            ['Mountain Wellness Hike', 'Gentle wellness hike with breathing exercises in the hills.', ['Ella'], 4100.00],
            ['Nature Wellness Walk', 'Mindful nature wellness walk near rock landscapes.', ['Sigiriya'], 3900.00],
            ['Hot Stone Spa Package', 'Hot stone spa wellness package at partner hotels.', ['Colombo'], 12500.00],
            ['Herbal Wellness Bath', 'Herbal bath and wellness therapy experience.', ['Kandy'], 7600.00],
            ['Coastal Wellness Escape', 'Half-day coastal wellness escape with massage.', ['Galle'], 11000.00],
            ['Tea Detox Wellness Day', 'Tea-focused detox wellness day in the hills.', ['Ella'], 6800.00],
            ['Meditation and Spa Combo', 'Meditation class plus spa wellness combo.', ['Colombo'], 8900.00],
        ];

        return array_map(
            fn (array $item): array => $this->definition(
                productName: $item[0],
                description: $item[1],
                category: 'Wellness',
                destinations: $item[2],
                price: $item[3],
                inventoryCount: 18,
                validFrom: now()->subDays(2)->toDateString(),
                validUntil: now()->addMonths(2)->toDateString(),
                status: ProductStatus::Active,
            ),
            $items,
        );
    }

    /**
     * Fillers: expired Active, Inactive valid, Inactive expired, and extra under/over 10k items.
     *
     * @return list<array<string, mixed>>
     */
    private function fillerProducts(): array
    {
        $definitions = [];

        // 8 Active + expired (for expired summary + excluded from catalog search)
        for ($i = 1; $i <= 8; $i++) {
            $definitions[] = $this->definition(
                productName: "Seasonal Buffet Promo {$i}",
                description: "Seasonal buffet promo {$i} with limited kitchen stations and dessert counters.",
                category: 'Food',
                destinations: ['Colombo'],
                price: 3000 + ($i * 500),
                inventoryCount: 5,
                validFrom: now()->subMonths(3)->toDateString(),
                validUntil: now()->subDays($i)->toDateString(),
                status: ProductStatus::Active,
            );
        }

        // 7 Inactive but currently valid
        for ($i = 1; $i <= 7; $i++) {
            $definitions[] = $this->definition(
                productName: "Heritage Evening Special {$i}",
                description: "Heritage evening special {$i} with guided storytelling and light refreshments.",
                category: 'Cultural',
                destinations: ['Kandy'],
                price: 4000 + ($i * 700),
                inventoryCount: 10,
                validFrom: now()->subDays(10)->toDateString(),
                validUntil: now()->addDays(40)->toDateString(),
                status: ProductStatus::Inactive,
            );
        }

        // 2 Inactive + expired
        for ($i = 1; $i <= 2; $i++) {
            $definitions[] = $this->definition(
                productName: "Coastal Picnic Deal {$i}",
                description: "Coastal picnic deal {$i} with beach mats, snacks, and shaded seating.",
                category: 'Beach',
                destinations: ['Galle'],
                price: 5500.00,
                inventoryCount: 0,
                validFrom: now()->subMonths(4)->toDateString(),
                validUntil: now()->subMonths(1)->toDateString(),
                status: ProductStatus::Inactive,
            );
        }

        return $definitions;
    }

    /**
     * @param  list<string>  $destinations
     * @return array{
     *     product_name: string,
     *     description: string,
     *     category: string,
     *     destinations: list<string>,
     *     price: float,
     *     inventory_count: int,
     *     valid_from: string,
     *     valid_until: string,
     *     status: ProductStatus
     * }
     */
    private function definition(
        string $productName,
        string $description,
        string $category,
        array $destinations,
        float $price,
        int $inventoryCount,
        string $validFrom,
        string $validUntil,
        ProductStatus $status,
    ): array {
        return [
            'product_name' => $productName,
            'description' => $description,
            'category' => $category,
            'destinations' => $destinations,
            'price' => $price,
            'inventory_count' => $inventoryCount,
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'status' => $status,
        ];
    }
}
