<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Realistic demo marketplace: stores, categories, products, tags, customers and
 * ~60 days of orders so every dashboard screen has data to show.
 */
class DemoDataSeeder extends Seeder
{
    /** store => [status, description, categories => [product name => [price, compare_price|null]]] */
    private function catalog(): array
    {
        return [
            ['Nile Electronics', 'active', 'Phones, audio and everyday gadgets from certified suppliers.', [
                'Electronics' => [
                    'Wireless Earbuds Pro' => [49.9, 69.9], 'Smart Watch Series 4' => [89.0, 119.0],
                    '20,000 mAh Power Bank' => [29.5, null], 'USB-C Fast Charger 65W' => [24.9, 34.9],
                    'Bluetooth Speaker Mini' => [34.0, 44.0], 'Mechanical Keyboard' => [64.0, null],
                    'Noise-Cancelling Headphones' => [129.0, 169.0], '4K Action Camera' => [99.0, 139.0],
                ],
            ]],
            ['Casa Bayt', 'active', 'Kitchen and home essentials, built to last.', [
                'Home & Kitchen' => [
                    'Stainless Steel Kettle' => [27.0, 35.0], 'Non-Stick Pan Set (3 pcs)' => [54.0, 79.0],
                    'Ceramic Dinner Set (16 pcs)' => [72.0, null], 'Memory Foam Pillow' => [22.5, 30.0],
                    'LED Desk Lamp' => [19.9, null], 'Cotton Bedding Set' => [58.0, 75.0],
                    'Electric Coffee Grinder' => [31.0, 42.0], 'Glass Food Containers (6 pcs)' => [26.0, null],
                ],
            ]],
            ['Zaytoun Wear', 'active', 'Modern everyday clothing and accessories.', [
                'Fashion' => [
                    'Linen Shirt' => [34.0, 45.0], 'Slim Fit Chinos' => [39.0, null],
                    'Leather Belt' => [18.5, 25.0], 'Canvas Backpack' => [42.0, 55.0],
                    'Wool Scarf' => [21.0, null], 'Classic Sneakers' => [59.0, 79.0],
                    'Cotton Hoodie' => [36.0, 48.0], 'Polarized Sunglasses' => [23.0, null],
                ],
            ]],
            ['Rose & Sage', 'active', 'Skin care and personal care with simple ingredients.', [
                'Beauty & Care' => [
                    'Vitamin C Face Serum' => [28.0, 36.0], 'Argan Hair Oil' => [16.5, null],
                    'Daily SPF 50 Sunscreen' => [14.9, 19.9], 'Shea Body Butter' => [12.0, null],
                    'Bamboo Toothbrush Set' => [7.5, 10.0], 'Rose Water Toner' => [11.0, null],
                    'Beard Grooming Kit' => [26.0, 34.0],
                ],
            ]],
            ['Trail Co.', 'inactive', 'Outdoor and fitness gear (temporarily paused).', [
                'Sports & Outdoors' => [
                    'Yoga Mat 6 mm' => [19.0, 26.0], 'Adjustable Dumbbell Pair' => [74.0, 95.0],
                    'Insulated Water Bottle 1 L' => [17.0, null], 'Trekking Poles (pair)' => [38.0, 49.0],
                    'Resistance Bands Set' => [13.5, null], 'Camping Headlamp' => [15.0, 20.0],
                ],
                'Books & Stationery' => [
                    'Hardcover Notebook A5' => [8.5, null], 'Gel Pen Set (12 colours)' => [6.9, 9.5],
                ],
            ]],
        ];
    }

    public function run(): void
    {
        mt_srand(2026); // reproducible demo data
        $faker = \Faker\Factory::create();
        $faker->seed(2026);

        // Tags
        $tags = collect(['New', 'Bestseller', 'Eco-friendly', 'Gift idea', 'Limited'])
            ->map(fn ($n) => Tag::firstOrCreate(['slug' => Str::slug($n)], ['name' => $n]));

        // Stores, categories, products
        $products = collect();
        foreach ($this->catalog() as [$storeName, $status, $description, $groups]) {
            $store = Store::create([
                'name' => $storeName,
                'slug' => Str::slug($storeName),
                'description' => $description,
                'status' => $status,
            ]);

            foreach ($groups as $categoryName => $items) {
                $category = Category::firstOrCreate(
                    ['slug' => Str::slug($categoryName)],
                    ['name' => $categoryName, 'description' => "Everything in {$categoryName}.", 'status' => 'active']
                );

                foreach ($items as $name => [$price, $compare]) {
                    $stock = match (true) {
                        mt_rand(1, 100) <= 8 => 0,
                        mt_rand(1, 100) <= 20 => mt_rand(1, 5),
                        default => mt_rand(12, 120),
                    };
                    $product = Product::create([
                        'store_id' => $store->id,
                        'category_id' => $category->id,
                        'name' => $name,
                        'description' => $faker->sentence(14),
                        'price' => $price,
                        'compare_price' => $compare,
                        'quantity' => $stock,
                        'featured' => mt_rand(1, 100) <= 25,
                        'status' => mt_rand(1, 100) <= 90 ? 'active' : 'draft',
                    ]);
                    $product->tags()->sync($tags->random(mt_rand(0, 2))->pluck('id')->all());
                    $products->push($product);
                }
            }
        }

        // Customers (signed up over the last 90 days)
        $customers = collect();
        for ($i = 0; $i < 38; $i++) {
            $joined = Carbon::now()->subDays(mt_rand(0, 90))->subMinutes(mt_rand(0, 1400));
            $user = User::factory()->create([
                'name' => $faker->name(),
                'email' => $faker->unique()->safeEmail(),
                'phone_number' => $faker->numerify('+97059#######'),
            ]);
            $user->forceFill(['created_at' => $joined, 'updated_at' => $joined])->save();
            $customers->push($user);
        }

        // Orders, oldest first so order numbers stay chronological
        $stores = Store::all();
        $weights = $stores->mapWithKeys(fn ($s) => [$s->id => match ($s->name) {
            'Nile Electronics' => 34, 'Casa Bayt' => 26, 'Zaytoun Wear' => 22, 'Rose & Sage' => 12, default => 6,
        }]);
        $countries = ['PS', 'PS', 'PS', 'EG', 'JO', 'AE', 'SA'];

        $dates = collect(range(1, 190))->map(function () {
            // more orders in recent weeks
            $days = (int) floor(60 * (mt_rand(0, 1000) / 1000) ** 1.5);

            return Carbon::now()->subDays($days)->subMinutes(mt_rand(0, 1300));
        })->sort()->values();

        foreach ($dates as $date) {
            $storeId = $this->weightedPick($weights);
            $pool = $products->where('store_id', $storeId)->where('status', 'active');
            if ($pool->isEmpty()) {
                continue;
            }
            $customer = mt_rand(1, 100) <= 90 ? $customers->random() : null;
            $age = $date->diffInDays(now());
            [$status, $paymentStatus] = $this->pickStatus($age);

            $order = Order::create([
                'user_id' => $customer?->id,
                'store_id' => $storeId,
                'payment_method' => ['cod', 'cod', 'card', 'card', 'bank_transfer'][mt_rand(0, 4)],
                'status' => $status,
                'payment_status' => $paymentStatus,
            ]);
            $order->forceFill(['created_at' => $date, 'updated_at' => $date->copy()->addHours(mt_rand(1, 40))->min(now())])->save();

            foreach ($pool->random(min($pool->count(), mt_rand(1, 4))) as $product) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => mt_rand(1, 3),
                ]);
            }

            [$first, $last] = $customer
                ? array_pad(explode(' ', $customer->name, 2), 2, '')
                : [$faker->firstName(), $faker->lastName()];
            $address = [
                'first_name' => $first, 'last_name' => $last ?: $first,
                'email' => $customer?->email ?? $faker->safeEmail(),
                'phone_number' => $customer?->phone_number ?? $faker->numerify('+97059#######'),
                'street_address' => $faker->streetAddress(), 'city' => $faker->city(),
                'postal_code' => $faker->postcode(), 'state' => null,
                'country' => $countries[mt_rand(0, count($countries) - 1)],
            ];
            $order->addresses()->create($address + ['type' => 'billing']);
            $order->addresses()->create($address + ['type' => 'shipping']);
        }
    }

    private function weightedPick($weights): int
    {
        $roll = mt_rand(1, $weights->sum());
        foreach ($weights as $id => $w) {
            if (($roll -= $w) <= 0) {
                return $id;
            }
        }

        return $weights->keys()->first();
    }

    /** @return array{0:string,1:string} [status, payment_status] */
    private function pickStatus(int $age): array
    {
        $roll = mt_rand(1, 100);
        if ($age > 14) {
            return match (true) {
                $roll <= 80 => ['completed', 'paid'],
                $roll <= 92 => ['cancelled', 'failed'],
                default => ['refunded', 'paid'],
            };
        }
        if ($age > 3) {
            return match (true) {
                $roll <= 45 => ['completed', 'paid'],
                $roll <= 70 => ['delivering', mt_rand(0, 1) ? 'paid' : 'pending'],
                $roll <= 90 => ['processing', mt_rand(0, 1) ? 'paid' : 'pending'],
                default => ['cancelled', 'failed'],
            };
        }

        return match (true) {
            $roll <= 40 => ['pending', 'pending'],
            $roll <= 75 => ['processing', mt_rand(0, 2) ? 'paid' : 'pending'],
            $roll <= 90 => ['delivering', 'paid'],
            default => ['cancelled', 'failed'],
        };
    }
}
