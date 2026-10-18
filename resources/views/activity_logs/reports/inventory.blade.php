@if(isset($inventoryYearly))
    @include('activity_logs.reports.inventory_yearly', ['report' => $inventoryYearly])
@else
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
                    <span class="fw-bold {{ $medicine->available_quantity <= 0 || $medicine->isLowStock() ? 'text-danger' : 'text-success' }}">
                        {{ $medicine->available_quantity }}
                    </span>
                    <span class="text-gray-600 fs-7">/ Reorder at {{ $medicine->minimum_stock_alert ?? '—' }}</span>
                </td>
                <td>
                    @php
                        // from the batches loaded with the list (one query for all rows, not one per medicine)
                        $expiringBatch = $medicine->batches->where('quantity', '>', 0)->sortBy('expiration_date')->first();
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
                    @php $stockStatus = $medicine->stockStatus(); @endphp
                    @if($stockStatus === 'Out of Stock')
                        <span class="badge bg-danger text-white">Out of Stock</span>
                    @elseif($stockStatus === 'Low Stock')
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
@endif
