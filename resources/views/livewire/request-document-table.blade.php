<div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Clinic ID</th>
                <th>Document Type</th>
                <th>Description</th>
                <th>Requested At</th>
                <th>Fulfilled At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($requestDocuments as $requestDocument)
            <tr>
                <td>{{ $requestDocument->id }}</td>
                <td>{{ $requestDocument->user_id }}</td>
                <td>{{ $requestDocument->clinic_id }}</td>
                <td>{{ $requestDocument->document_type }}</td>
                <td>{{ $requestDocument->description }}</td>
                <td>{{ $requestDocument->requested_at }}</td>
                <td>{{ $requestDocument->fulfilled_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>