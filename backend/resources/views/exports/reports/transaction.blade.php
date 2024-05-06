<table>
    <thead>
    <tr>
        @foreach($headers as $header)
            <th>{{ $header }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($data as $d)
        <tr>
            <td>{{ $d['id'] }}</td>
            <td>{{ $d['user']['full_name'] }}</td>
            <td>{{ $d['branch']['name'] }}</td>
            <td>{{ $d['transaction_type'] }}</td>
            <td>{{ $d['total_amount'] }}</td>
            <td>{{ strtoupper($d['status']) }}</td>
            <td>{{ $d['transaction_date'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
