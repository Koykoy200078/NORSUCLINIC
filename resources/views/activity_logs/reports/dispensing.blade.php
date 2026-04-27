<div class="table-responsive">
    <table class="table table-hover table-rounded table-striped border gy-5 gs-7">
        <thead>
            <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-200">
                <th>Date Dispensed</th>
                <th>Medicine</th>
                <th>Quantity Used</th>
                <th>Associated Record</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $used)
            <tr>
                <td>{{ $used->created_at->format('M d, Y h:i A') }}</td>
                <td>{{ $used->medicine->name ?? 'Deleted Medicine' }}</td>
                <td><span class="badge bg-secondary text-white">{{ $used->stock_used }}</span></td>
                <td>
                    @if($used->model_type === 'App\Models\DocumentIssuance')
                        <span class="text-gray-600">Consultation #{{ $used->model_id }}</span>
                    @elseif($used->model_type === 'App\Models\Prescription')
                        <span class="text-gray-600">Prescription #{{ $used->model_id }}</span>
                    @else
                        <span class="text-gray-600">{{ class_basename($used->model_type) }} #{{ $used->model_id }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center text-gray-600 py-5">No dispensing records found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
