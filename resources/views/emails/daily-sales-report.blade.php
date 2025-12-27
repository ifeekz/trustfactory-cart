<h2>Daily Sales Report</h2>

@if ($sales->isEmpty())
    <p>No products sold today.</p>
@else
    <table cellpadding="6" cellspacing="0" border="1">
        <thead>
            <tr>
                <th align="left">Product</th>
                <th align="left">Quantity Sold</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sales as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->total_sold }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
