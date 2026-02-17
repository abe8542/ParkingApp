<table>
    <thead>
        <tr>
            <th>Plate Number</th>
            <th>Phone</th>
            <th>Arrival</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($vehicles as $vehicle)
        <tr>
            <td>{{ $vehicle->plate_number }}</td>
            <td>{{ $vehicle->phone_number }}</td>
            <td>{{ $vehicle->arrival_time->diffForHumans() }}</td>
            <td>
                <form action="{{ route('checkout.pay', $vehicle->id) }}" method="POST">
                    @csrf
                    <button type="submit">Initiate Payment (STK Push)</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
