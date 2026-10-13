<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use App\Models\Unit;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductUnitConversion;
use App\Models\BranchStock;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 0. Create Roles
        $roleSuperAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $roleKasir = Role::firstOrCreate(['name' => 'kasir']);
        $roleManager = Role::firstOrCreate(['name' => 'manager']);

        $kasirPermissions = [
            'page_PosPage',
            'buka_kasir_pos',
            'cetak_struk_transaksi',
            'create_transaction',
            'view_transaction',
        ];

        foreach ($kasirPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        $roleKasir->syncPermissions($kasirPermissions);

        // 1. Create Branches
        $branch1 = Branch::firstOrCreate(
            ['code' => 'JKT01'],
            [
                'name' => 'Cabang Jakarta Pusat (Utama)',
                'phone' => '021-5550192',
                'address' => 'Jl. Jendral Sudirman No. 45, Jakarta Pusat',
                'is_active' => true,
            ]
        );

        $branch2 = Branch::firstOrCreate(
            ['code' => 'BDG01'],
            [
                'name' => 'Cabang Bandung',
                'phone' => '022-7770144',
                'address' => 'Jl. Asia Afrika No. 120, Bandung',
                'is_active' => true,
            ]
        );

        $branch3 = Branch::firstOrCreate(
            ['code' => 'SBY01'],
            [
                'name' => 'Cabang Surabaya',
                'phone' => '031-8880211',
                'address' => 'Jl. Tunjungan No. 88, Surabaya',
                'is_active' => true,
            ]
        );

        // 2. Ensure Admin User & Branch Cashier Accounts
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin Kasir',
                'password' => Hash::make('12345678'),
                'branch_id' => $branch1->id,
            ]
        );
        $admin->branches()->sync([$branch1->id, $branch2->id, $branch3->id]);
        $admin->assignRole($roleSuperAdmin);

        $kasirJkt = User::firstOrCreate(
            ['email' => 'kasir.jakarta@example.com'],
            [
                'name' => 'Budi (Kasir Jakarta)',
                'password' => Hash::make('12345678'),
                'branch_id' => $branch1->id,
            ]
        );
        $kasirJkt->branches()->sync([$branch1->id]);
        $kasirJkt->assignRole($roleKasir);

        $kasirBdg = User::firstOrCreate(
            ['email' => 'kasir.bandung@example.com'],
            [
                'name' => 'Siti (Kasir Bandung)',
                'password' => Hash::make('12345678'),
                'branch_id' => $branch2->id,
            ]
        );
        $kasirBdg->branches()->sync([$branch2->id]);
        $kasirBdg->assignRole($roleKasir);

        $kasirSby = User::firstOrCreate(
            ['email' => 'kasir.surabaya@example.com'],
            [
                'name' => 'Andi (Kasir Surabaya)',
                'password' => Hash::make('12345678'),
                'branch_id' => $branch3->id,
            ]
        );
        $kasirSby->branches()->sync([$branch3->id]);
        $kasirSby->assignRole($roleKasir);

        // 3. Create Units
        $pcs = Unit::firstOrCreate(['short_name' => 'Pcs'], ['name' => 'Pieces']);
        $pack = Unit::firstOrCreate(['short_name' => 'Pck'], ['name' => 'Pack']);
        $box = Unit::firstOrCreate(['short_name' => 'Box'], ['name' => 'Dus / Box']);
        $botol = Unit::firstOrCreate(['short_name' => 'Btl'], ['name' => 'Botol']);

        // 4. Create Categories
        $catFood = Category::firstOrCreate(['slug' => 'makanan-snack'], ['name' => 'Makanan & Snack', 'description' => 'Produk makanan ringan dan mi instan']);
        $catDrink = Category::firstOrCreate(['slug' => 'minuman'], ['name' => 'Minuman', 'description' => 'Air mineral, minuman dingin, dan kopi']);
        $catHousehold = Category::firstOrCreate(['slug' => 'kebutuhan-rumah'], ['name' => 'Kebutuhan Rumah', 'description' => 'Sabun, minyak goreng, dan deterjen']);

        // 5. Create Products & Multi-Unit Conversions
        // Product 1: Indomie Goreng 85g
        $indomie = Product::firstOrCreate(
            ['barcode' => '8998866200011'],
            [
                'category_id' => $catFood->id,
                'name' => 'Indomie Goreng 85g',
                'sku' => 'IND-GRG-85',
                'description' => 'Mi Instan Rasa Goreng Spesial',
                'base_unit_id' => $pcs->id,
                'buy_price' => 2800,
                'sell_price' => 3500,
                'is_active' => true,
            ]
        );

        // Indomie Conversions: 1 Pack = 5 Pcs, 1 Box = 40 Pcs
        ProductUnitConversion::firstOrCreate(
            ['product_id' => $indomie->id, 'unit_id' => $pack->id],
            [
                'conversion_factor' => 5,
                'sell_price' => 17000,
            ]
        );
        ProductUnitConversion::firstOrCreate(
            ['product_id' => $indomie->id, 'unit_id' => $box->id],
            [
                'conversion_factor' => 40,
                'sell_price' => 132000,
            ]
        );

        // Product 2: Aqua 600ml
        $aqua = Product::firstOrCreate(
            ['barcode' => '8998866200022'],
            [
                'category_id' => $catDrink->id,
                'name' => 'Aqua Air Mineral 600ml',
                'sku' => 'AQA-600',
                'description' => 'Air mineral kemasan botol 600ml',
                'base_unit_id' => $botol->id,
                'buy_price' => 2600,
                'sell_price' => 4000,
                'is_active' => true,
            ]
        );

        ProductUnitConversion::firstOrCreate(
            ['product_id' => $aqua->id, 'unit_id' => $box->id],
            [
                'conversion_factor' => 24,
                'sell_price' => 90000,
            ]
        );

        // Product 3: Minyak Goreng Bimoli 2L
        $bimoli = Product::firstOrCreate(
            ['barcode' => '8998866200033'],
            [
                'category_id' => $catHousehold->id,
                'name' => 'Bimoli Minyak Goreng 2 Liter',
                'sku' => 'BML-2L',
                'description' => 'Minyak goreng kelapa sawit kemasan pouch 2L',
                'base_unit_id' => $pcs->id,
                'buy_price' => 32000,
                'sell_price' => 38500,
                'is_active' => true,
            ]
        );

        ProductUnitConversion::firstOrCreate(
            ['product_id' => $bimoli->id, 'unit_id' => $box->id],
            [
                'conversion_factor' => 6,
                'sell_price' => 225000,
            ]
        );

        // Product 4: Coca Cola 390ml (Contoh produk stok toko menipis untuk alert)
        $coca = Product::firstOrCreate(
            ['barcode' => '8998866200044'],
            [
                'category_id' => $catDrink->id,
                'name' => 'Coca Cola 390ml Botol',
                'sku' => 'KO-390',
                'description' => 'Minuman berkarbonasi rasa cola',
                'base_unit_id' => $botol->id,
                'buy_price' => 4000,
                'sell_price' => 6000,
                'is_active' => true,
            ]
        );

        // 6. Create Branch Stocks (Store & Warehouse)
        BranchStock::updateOrCreate(['branch_id' => $branch1->id, 'product_id' => $indomie->id], [
            'store_stock' => 120,
            'warehouse_stock' => 500,
            'min_store_stock_alert' => 25,
            'min_warehouse_stock_alert' => 100,
        ]);

        BranchStock::updateOrCreate(['branch_id' => $branch1->id, 'product_id' => $aqua->id], [
            'store_stock' => 80,
            'warehouse_stock' => 300,
            'min_store_stock_alert' => 20,
            'min_warehouse_stock_alert' => 50,
        ]);

        BranchStock::updateOrCreate(['branch_id' => $branch1->id, 'product_id' => $bimoli->id], [
            'store_stock' => 15,
            'warehouse_stock' => 60,
            'min_store_stock_alert' => 10,
            'min_warehouse_stock_alert' => 20,
        ]);

        BranchStock::updateOrCreate(['branch_id' => $branch1->id, 'product_id' => $coca->id], [
            'store_stock' => 3,
            'warehouse_stock' => 8,
            'min_store_stock_alert' => 15,
            'min_warehouse_stock_alert' => 30,
        ]);

        BranchStock::updateOrCreate(['branch_id' => $branch2->id, 'product_id' => $indomie->id], [
            'store_stock' => 50,
            'warehouse_stock' => 200,
            'min_store_stock_alert' => 20,
            'min_warehouse_stock_alert' => 50,
        ]);
        BranchStock::updateOrCreate(['branch_id' => $branch2->id, 'product_id' => $coca->id], [
            'store_stock' => 25,
            'warehouse_stock' => 100,
            'min_store_stock_alert' => 15,
            'min_warehouse_stock_alert' => 30,
        ]);
        BranchStock::updateOrCreate(['branch_id' => $branch2->id, 'product_id' => $aqua->id], [
            'store_stock' => 60,
            'warehouse_stock' => 250,
            'min_store_stock_alert' => 20,
            'min_warehouse_stock_alert' => 50,
        ]);

        BranchStock::updateOrCreate(['branch_id' => $branch3->id, 'product_id' => $indomie->id], [
            'store_stock' => 40,
            'warehouse_stock' => 150,
            'min_store_stock_alert' => 20,
            'min_warehouse_stock_alert' => 50,
        ]);
        BranchStock::updateOrCreate(['branch_id' => $branch3->id, 'product_id' => $coca->id], [
            'store_stock' => 30,
            'warehouse_stock' => 120,
            'min_store_stock_alert' => 15,
            'min_warehouse_stock_alert' => 30,
        ]);
        BranchStock::updateOrCreate(['branch_id' => $branch3->id, 'product_id' => $aqua->id], [
            'store_stock' => 70,
            'warehouse_stock' => 280,
            'min_store_stock_alert' => 20,
        ]);

        // 7. Create Sample Completed Transactions
        if (Transaction::count() === 0) {
            $trx1 = Transaction::create([
                'transaction_number' => 'TRX-' . date('Ymd') . '-0001',
                'branch_id' => $branch1->id,
                'user_id' => $admin->id,
                'total_amount' => 42000,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'grand_total' => 42000,
                'paid_amount' => 50000,
                'change_amount' => 8000,
                'total_cost_price' => 31200,
                'net_profit' => 10800,
                'payment_method' => 'Cash',
                'status' => 'completed',
                'created_at' => now()->subHours(2),
            ]);

            TransactionItem::create([
                'transaction_id' => $trx1->id,
                'product_id' => $indomie->id,
                'unit_id' => $pack->id,
                'unit_name' => 'Pack',
                'quantity' => 2,
                'conversion_factor' => 5,
                'total_base_quantity' => 10,
                'price_per_unit' => 17000,
                'cost_price_per_unit' => 14000,
                'subtotal' => 34000,
                'item_profit' => 6000,
            ]);

            TransactionItem::create([
                'transaction_id' => $trx1->id,
                'product_id' => $aqua->id,
                'unit_id' => $botol->id,
                'unit_name' => 'Btl',
                'quantity' => 2,
                'conversion_factor' => 1,
                'total_base_quantity' => 2,
                'price_per_unit' => 4000,
                'cost_price_per_unit' => 2600,
                'subtotal' => 8000,
                'item_profit' => 2800,
            ]);
        }
    }
}
