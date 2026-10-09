<div class="row">
    <div class="col-md-12">
        @if(!$search)
            <div class="text-center py-20">
                <i class="fas fa-search fs-4x text-gray-300 mb-5"></i>
                <h3 class="text-muted">Enter a search term to find records across the system</h3>
            </div>
        @else
            @if($searchModules['patients'] ?? false)
            <!-- Patients -->
            <div class="card mb-5 shadow-sm">
                <div class="card-header bg-light-primary py-3">
                    <h3 class="card-title text-primary fs-5 fw-bold">Patient Records</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-row-dashed align-middle gs-7">
                            <thead>
                                <tr class="fw-bold text-gray-800">
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($patients ?? [] as $p)
                                <tr>
                                    <td>{{ $p->patient_unique_id }}</td>
                                    <td>{{ $p->user->full_name }}</td>
                                    <td>{{ $p->contact_no }}</td>
                                    <td class="text-end">
                                        <a href="{{ route(isRole('clinic_admin') ? 'patients.show' : (isRole('staff') ? 'staff.patients.show' : 'doctors.patients.show'), $p->id) }}" class="btn btn-sm btn-icon btn-light-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted py-5">No patients matched your search</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @endif

            @if($searchModules['prescriptions'] ?? false)
            <!-- Prescriptions -->
            <div class="card mb-5 shadow-sm">
                <div class="card-header bg-light-success py-3">
                    <h3 class="card-title text-success fs-5 fw-bold">Prescription Records</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-row-dashed align-middle gs-7">
                            <thead>
                                <tr class="fw-bold text-gray-800">
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>Prescription #</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($prescriptions ?? [] as $pres)
                                <tr>
                                    <td>{{ $pres->created_at->format('M d, Y') }}</td>
                                    <td>{{ $pres->patient->user->full_name }}</td>
                                    <td>#{{ $pres->id }}</td>
                                    <td class="text-end">
                                        <a href="{{ route(isRole('clinic_admin') ? 'prescriptions.show' : (isRole('staff') ? 'staff.prescriptions.show' : 'doctors.prescriptions.show'), $pres->id) }}" class="btn btn-sm btn-icon btn-light-success">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted py-5">No prescriptions matched your search</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @endif

            @if($searchModules['inventory'] ?? false)
            <!-- Inventory -->
            <div class="card mb-5 shadow-sm">
                <div class="card-header bg-light-warning py-3">
                    <h3 class="card-title text-warning fs-5 fw-bold">Inventory Items</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-row-dashed align-middle gs-7">
                            <thead>
                                <tr class="fw-bold text-gray-800">
                                    <th>Medicine</th>
                                    <th>Stock</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($inventory ?? [] as $inv)
                                <tr>
                                    <td>{{ $inv->name }}</td>
                                    <td>{{ $inv->available_quantity }}</td>
                                    <td class="text-end">
                                        <a href="{{ route(isRole('clinic_admin') ? 'medicines.index' : (isRole('staff') ? 'staff.medicines.index' : 'doctors.medicines.index'), ['search' => $inv->name]) }}" class="btn btn-sm btn-icon btn-light-warning">
                                            <i class="fas fa-search"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center text-muted py-5">No medicine matched your search</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        @endif
    </div>
</div>
