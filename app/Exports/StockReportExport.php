<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    public function collection()
    {
        $query = DB::table('item_stocks')
            ->join('items', 'item_stocks.item_id', '=', 'items.id')
            ->join('warehouses', 'item_stocks.warehouse_id', '=', 'warehouses.id')
            ->leftJoin('units', 'items.unit_id', '=', 'units.id')
            ->select([
                'items.item_code',
                'items.name as item_name',
                'warehouses.name as warehouse_name',
                'units.name as unit_name',
                'item_stocks.quantity as stock_quantity',
            ]);

        if (Schema::hasColumn('items', 'minimum_stock')) {
            $query->addSelect('items.minimum_stock');
        } else {
            $query->addSelect(DB::raw('0 as minimum_stock'));
        }

        return $query
            ->orderBy('warehouses.name')
            ->orderBy('items.name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Kode Barang',
            'Nama Barang',
            'Gudang',
            'Satuan',
            'Stok Saat Ini',
            'Stok Minimum',
            'Status',
        ];
    }

    public function map($stock): array
    {
        $minimumStock = (int) ($stock->minimum_stock ?? 0);
        $currentStock = (int) ($stock->stock_quantity ?? 0);

        return [
            $stock->item_code ?? '-',
            $stock->item_name ?? '-',
            $stock->warehouse_name ?? '-',
            $stock->unit_name ?? '-',
            $currentStock,
            $minimumStock,
            $minimumStock > 0 && $currentStock <= $minimumStock ? 'Stok Rendah' : 'Aman',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Laporan Stok Barang';
    }
}