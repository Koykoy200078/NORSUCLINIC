<div class="table-responsive">
    <table class="table table-hover table-rounded table-striped border gy-5 gs-7">
        <thead>
            <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-200">
                <th>Medicine Name</th>
                <th>Category / Generic</th>
                <th>Current Stock</th>
                <th>Expiry Alerts</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $medicine)
            <tr>
                <td>
                    <div class="fw-bold text-gray-800">{{ $medicine->name }}</div>
                </td>
                <td>
                    <div>{{ $medicine->category->name ?? 'N/A' }}</div>
                    <div class="text-gray-600 fs-7">{{ $medicine->generic->name ?? 'N/A' }}</div>
                </td>
                <td>
                    <span class="fw-bold {{ $medicine->available_quantity <= $medicine->minimum_stock_alert ? 'text-danger' : 'text-success' }}">
                        {{ $medicine->available_quantity }}
                    </span>
                    <span class="text-gray-600 fs-7">/ Reorder at {{ $medicine->minimum_stock_alert }}</span>
                </td>
                <td>
                    @php
                        $expiringBatch = $medicine->batches()->where('quantity', '>', 0)->orderBy('expiration_date', 'asc')->first();
                    @endphp
                    @if($expiringBatch)
                        <span class="badge bg-{{ $expiringBatch->expiration_date <= now()->addDays(30) ? 'danger' : 'info' }}">
                            {{ $expiringBatch->expiration_date->format('M d, Y') }}
                        </span>
                    @else
                        <span class="text-gray-600">No active batches</span>
                    @endif
                </td>
                <td>
                    @if($medicine->available_quantity <= 0)
                        <span class="badge bg-danger text-white">Out of Stock</span>
                    @elseif($medicine->available_quantity <= $medicine->minimum_stock_alert)
                        <span class="badge bg-warning text-dark">Low Stock</span>
                    @else
                        <span class="badge bg-success text-white">Healthy</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center text-gray-600 py-5">No inventory records found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
