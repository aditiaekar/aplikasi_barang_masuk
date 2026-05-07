<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Menjalankan seeder data master aplikasi barang masuk.
     */
    public function run(): void
    {
        /**
         * Data kategori barang.
         */
        $categoryRawMaterial = Category::updateOrCreate(
            ['code' => 'KAT-001'],
            [
                'name' => 'Bahan Baku',
                'description' => 'Kategori untuk barang bahan baku.',
                'is_active' => true,
            ]
        );

        $categoryPackaging = Category::updateOrCreate(
            ['code' => 'KAT-002'],
            [
                'name' => 'Kemasan',
                'description' => 'Kategori untuk barang kemasan.',
                'is_active' => true,
            ]
        );

        /**
         * Data satuan barang.
         */
        $unitPcs = Unit::updateOrCreate(
            ['code' => 'PCS'],
            [
                'name' => 'Pcs',
                'description' => 'Satuan per buah.',
                'is_active' => true,
            ]
        );

        $unitKg = Unit::updateOrCreate(
            ['code' => 'KG'],
            [
                'name' => 'Kilogram',
                'description' => 'Satuan berat kilogram.',
                'is_active' => true,
            ]
        );

        $unitBox = Unit::updateOrCreate(
            ['code' => 'BOX'],
            [
                'name' => 'Box',
                'description' => 'Satuan per kotak.',
                'is_active' => true,
            ]
        );

        /**
         * Data supplier.
         */
        Supplier::updateOrCreate(
            ['code' => 'SUP-001'],
            [
                'name' => 'Supplier Utama',
                'phone' => '081234567890',
                'email' => 'supplierutama@example.com',
                'address' => 'Semarang',
                'description' => 'Supplier utama untuk kebutuhan barang masuk.',
                'is_active' => true,
            ]
        );

        Supplier::updateOrCreate(
            ['code' => 'SUP-002'],
            [
                'name' => 'Supplier Cadangan',
                'phone' => '081234567891',
                'email' => 'suppliercadangan@example.com',
                'address' => 'Semarang',
                'description' => 'Supplier cadangan untuk kebutuhan barang masuk.',
                'is_active' => true,
            ]
        );

        /**
         * Data gudang.
         */
        $warehouseMain = Warehouse::updateOrCreate(
            ['code' => 'GDG-001'],
            [
                'name' => 'Gudang Utama',
                'location' => 'PT. Samsudi Indoniaga Sedaya',
                'description' => 'Gudang utama penyimpanan barang.',
                'is_active' => true,
            ]
        );

        $warehouseReserve = Warehouse::updateOrCreate(
            ['code' => 'GDG-002'],
            [
                'name' => 'Gudang Cadangan',
                'location' => 'PT. Samsudi Indoniaga Sedaya',
                'description' => 'Gudang cadangan penyimpanan barang.',
                'is_active' => true,
            ]
        );

        /**
         * Data barang.
         */
        $itemA = Item::updateOrCreate(
            ['item_code' => 'BRG-001'],
            [
                'category_id' => $categoryRawMaterial->id,
                'unit_id' => $unitKg->id,
                'name' => 'Barang A',
                'barcode' => '899000000001',
                'minimum_stock' => 10,
                'image' => null,
                'description' => 'Contoh barang bahan baku.',
                'is_active' => true,
            ]
        );

        $itemB = Item::updateOrCreate(
            ['item_code' => 'BRG-002'],
            [
                'category_id' => $categoryPackaging->id,
                'unit_id' => $unitPcs->id,
                'name' => 'Barang B',
                'barcode' => '899000000002',
                'minimum_stock' => 20,
                'image' => null,
                'description' => 'Contoh barang kemasan.',
                'is_active' => true,
            ]
        );

        $itemC = Item::updateOrCreate(
            ['item_code' => 'BRG-003'],
            [
                'category_id' => $categoryPackaging->id,
                'unit_id' => $unitBox->id,
                'name' => 'Barang C',
                'barcode' => '899000000003',
                'minimum_stock' => 5,
                'image' => null,
                'description' => 'Contoh barang dalam satuan box.',
                'is_active' => true,
            ]
        );

        /**
         * Data stok awal barang per gudang.
         * Quantity dibuat 0 karena stok nantinya bertambah dari transaksi barang masuk.
         */
        $items = [$itemA, $itemB, $itemC];
        $warehouses = [$warehouseMain, $warehouseReserve];

        foreach ($items as $item) {
            foreach ($warehouses as $warehouse) {
                ItemStock::updateOrCreate(
                    [
                        'item_id' => $item->id,
                        'warehouse_id' => $warehouse->id,
                    ],
                    [
                        'quantity' => 0,
                    ]
                );
            }
        }
    }
}