<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\ModifierGroup;
use App\Models\Modifier;

class SampleProductsSeeder extends Seeder
{
    public function run(): void
    {
        // Categories
        $categories = [
            ['name' => 'Bakso', 'slug' => 'bakso', 'icon' => 'soup', 'sort_order' => 1],
            ['name' => 'Mie', 'slug' => 'mie', 'icon' => 'utensils', 'sort_order' => 2],
            ['name' => 'Minuman', 'slug' => 'minuman', 'icon' => 'cup-soda', 'sort_order' => 3],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }

        // Modifier Groups
        $levelPedas = ModifierGroup::firstOrCreate(
            ['name' => 'Level Pedas'],
            [
                'selection_type' => 'single',
                'is_required' => false,
                'min_selections' => 0,
                'max_selections' => 1,
            ]
        );

        $topping = ModifierGroup::firstOrCreate(
            ['name' => 'Topping'],
            [
                'selection_type' => 'multiple',
                'is_required' => false,
                'min_selections' => 0,
                'max_selections' => 5,
            ]
        );

        // Modifiers for Level Pedas
        $pedasModifiers = [
            ['name' => 'Tidak Pedas', 'price_adjustment' => 0, 'sort_order' => 1],
            ['name' => 'Pedas Sedikit', 'price_adjustment' => 0, 'sort_order' => 2],
            ['name' => 'Pedas Sedang', 'price_adjustment' => 0, 'sort_order' => 3],
            ['name' => 'Pedas Banget', 'price_adjustment' => 0, 'sort_order' => 4],
        ];

        foreach ($pedasModifiers as $mod) {
            Modifier::firstOrCreate(
                ['modifier_group_id' => $levelPedas->id, 'name' => $mod['name']],
                $mod + ['modifier_group_id' => $levelPedas->id]
            );
        }

        // Modifiers for Topping
        $toppingModifiers = [
            ['name' => 'Tahu Goreng', 'price_adjustment' => 2000, 'sort_order' => 1],
            ['name' => 'Siomay', 'price_adjustment' => 3000, 'sort_order' => 2],
            ['name' => 'Pangsit Goreng', 'price_adjustment' => 2500, 'sort_order' => 3],
            ['name' => 'Telur Puyuh', 'price_adjustment' => 3000, 'sort_order' => 4],
            ['name' => 'Kerupuk', 'price_adjustment' => 1000, 'sort_order' => 5],
        ];

        foreach ($toppingModifiers as $mod) {
            Modifier::firstOrCreate(
                ['modifier_group_id' => $topping->id, 'name' => $mod['name']],
                $mod + ['modifier_group_id' => $topping->id]
            );
        }

        // Products from menu.png
        $baksoCategory = Category::where('slug', 'bakso')->first();
        $mieCategory = Category::where('slug', 'mie')->first();
        $minumanCategory = Category::where('slug', 'minuman')->first();

        $products = [
            // Bakso
            ['category_id' => $baksoCategory->id, 'name' => 'BAKSO JUMBO+MALANG', 'sku' => 'BSO-001', 'price' => 17000, 'cost_price' => 10000, 'is_featured' => true],
            ['category_id' => $baksoCategory->id, 'name' => 'BAKSO TELOR +MALANG', 'sku' => 'BSO-002', 'price' => 17000, 'cost_price' => 10000],
            ['category_id' => $baksoCategory->id, 'name' => 'BAKSO URAT+MALANG', 'sku' => 'BSO-003', 'price' => 12000, 'cost_price' => 7000],
            ['category_id' => $baksoCategory->id, 'name' => 'BAKSO MALANG', 'sku' => 'BSO-004', 'price' => 10000, 'cost_price' => 6000],

            // Mie
            ['category_id' => $mieCategory->id, 'name' => 'MIE AYAM BAKSO+CEKER', 'sku' => 'MIE-001', 'price' => 17000, 'cost_price' => 10000, 'is_featured' => true],
            ['category_id' => $mieCategory->id, 'name' => 'MIE AYAM BAKSO', 'sku' => 'MIE-002', 'price' => 15000, 'cost_price' => 8000],
            ['category_id' => $mieCategory->id, 'name' => 'MIE AYAM', 'sku' => 'MIE-003', 'price' => 12000, 'cost_price' => 6000],

            // Minuman
            ['category_id' => $minumanCategory->id, 'name' => 'ES TEH JUMBO', 'sku' => 'MNM-001', 'price' => 5000, 'cost_price' => 2000],
            ['category_id' => $minumanCategory->id, 'name' => 'ES TEH MINI', 'sku' => 'MNM-002', 'price' => 3000, 'cost_price' => 1000],
            ['category_id' => $minumanCategory->id, 'name' => 'ES JERUK JUMBO', 'sku' => 'MNM-003', 'price' => 7000, 'cost_price' => 3000],
            ['category_id' => $minumanCategory->id, 'name' => 'ES JERUK MINI', 'sku' => 'MNM-004', 'price' => 5000, 'cost_price' => 2000],
        ];

        foreach ($products as $prod) {
            $product = Product::updateOrCreate(
                ['sku' => $prod['sku']],
                $prod
            );

            // Attach modifier groups to bakso and mie products
            if (in_array($prod['category_id'], [$baksoCategory->id, $mieCategory->id])) {
                $product->modifierGroups()->syncWithoutDetaching([$levelPedas->id, $topping->id]);
            }
        }

        $this->command->info('Sample products seeded successfully!');
    }
}
