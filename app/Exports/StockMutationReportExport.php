<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockMutationReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    public function collection()
    {
        $query = DB::table('stock_mutations')
            ->join('items', 'stock_mutations.item_id', '=', 'items.id')
            ->join('warehouses', 'stock_mutations.warehouse_id', '=', 'warehouses.id')
            ->leftJoin('users', 'stock_mutations.created_by', '=', 'users.id')
            ->select([
                'stock_mutations.*',
                'items.item_code',
                'items.name as item_name',
                'warehouses.name as warehouse_name',
                'users.name as user_name',
            ]);

        if (Schema::hasTable('stock_ins') && Schema::hasColumn('stock_mutations', 'stock_in_id')) {
            $query->leftJoin('stock_ins', 'stock_mutations.stock_in_id', '=', 'stock_ins.id');

            if (Schema::hasColumn('stock_ins', 'stock_in_number')) {
                $query->addSelect('stock_ins.stock_in_number');
            } else {
                $query->addSelect(DB::raw('NULL as stock_in_number'));
            }
        } else {
            $query->addSelect(DB::raw('NULL as stock_in_number'));
        }

        return $query
            ->orderByDesc('stock_mutations.mutation_date')
            ->orderByDesc('stock_mutations.id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Kode Barang',
            'Nama Barang',
            'Gudang',
            'Jenis Mutasi',
            'Jumlah',
            'Stok Sebelum',
            'Stok Sesudah',
            'Nomor Referensi',
            'Keterangan',
            'Dibuat Oleh',
        ];
    }

    public function map($mutation): array
    {
        $mutationType = $mutation->mutation_type ?? '-';

        if ($mutationType === 'in') {
            $mutationType = 'Masuk';
        } elseif ($mutationType === 'out') {
            $mutationType = 'Keluar';
        }

        return [
            !empty($mutation->mutation_date)
                ? Carbon::parse($mutation->mutation_date)->format('d-m-Y H:i')
                : '-',

            $mutation->item_code ?? '-',
            $mutation->item_name ?? '-',
            $mutation->warehouse_name ?? '-',
            $mutationType,
            (int) ($mutation->quantity ?? 0),
            (int) ($mutation->stock_before ?? 0),
            (int) ($mutation->stock_after ?? 0),
            $mutation->stock_in_number ?? '-',
            $mutation->description ?? '-',
            $mutation->user_name ?? '-',
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
        return 'Laporan Mutasi Stok';
    }
}