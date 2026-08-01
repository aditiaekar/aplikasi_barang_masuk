<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Stock Out Request</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 6px;
        }

        th {
            background: #eee;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    @foreach ($requests as $request)
    @php
            $totalPrice = 0;
            $stockOutItems = $request->items ?? collect();
            $rowNumber = 1;
        @endphp
        <table>
            <thead>
                <tr>
                    <td colspan="5" style="border-bottom:none">{{ $request->recipient_name ?? '-' }}</td>
                </tr>
                <tr>
                    <td colspan="5" style="border-top:none;border-bottom:none">{{ $request->recipient_postal_code ?? '-' }}</td>
                </tr>
                <tr>
                    <td colspan="5" style="border-top:none;border-bottom:none">{{ $request->recipient_address ?? '-' }}</td>
                </tr>
                <tr>
                    <td colspan="5" style="border-top:none;border-bottom:none">{{ $request->recipient_phone ?? '-' }}</td>
                </tr>
                <tr>
                    <td>EMS {{ $request->ems_number }}</td>
                    <td colspan="4"></td>
                </tr>
                <tr>
                    <th>No</th>
                    <th>Barang</th>
                    <th>Jumlah</th>
                    <th>Harga</th>
                    <th>Total Harga</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($stockOutItems as $item)
                    @forelse ($item->stockOutItemLayers as $layer)
                        @php
                            $total = $layer->subtotal;
                            $totalPrice += $total;
                        @endphp
                        <tr>
                            <td>{{ $rowNumber++ }}</td>
                            <td>{{ $item->item->name ?? '-' }}</td>
                            <td>{{ $layer->quantity }}</td>
                            <td style="text-align:right">{{ number_format((float) $layer->price, 0, ',', '.') }}</td>
                            <td style="text-align:right">{{ number_format((float) $total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        @php
                            $total = $item->total_price;
                            $totalPrice += $total;
                        @endphp
                        <tr>
                            <td>{{ $rowNumber++ }}</td>
                            <td>{{ $item->item->name ?? '-' }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td style="text-align:right">{{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                            <td style="text-align:right">{{ number_format((float) $total, 0, ',', '.') }}</td>
                        </tr>
                    @endforelse
                @endforeach
                <tr>
                    <td>PENGIRIM</td>
                    <td colspan="3" style="text-align:center">{{ $request->sender_name }}</td>
                    <td style="text-align:right"><strong>{{ number_format((float) $totalPrice, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>

        @if (!$loop->last)
            <br><br>
        @endif
    @endforeach
</body>

</html>
