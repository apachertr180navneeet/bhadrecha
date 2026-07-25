@extends('admin.layouts.app')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <div>
            <h5 class="fw-bold mb-1"><i class="bx bx-grid-alt me-2 text-primary"></i>Graphical Tyre Layout & Drag-and-Drop Positioning</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Maintenance</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.maintenance.tyre-management.index') }}">Tyre Management</a></li>
                    <li class="breadcrumb-item active">Graphic Layout</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.maintenance.tyre-management.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-list-ul me-1"></i> List View
            </a>
            <a href="{{ route('admin.maintenance.tyre-management.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> New Tyre
            </a>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

    <!-- Vehicle Selector & Summary Stats -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.maintenance.tyre-management.layout') }}" id="vehicle-select-form" class="row align-items-center g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold text-muted mb-1"><i class="bx bx-truck me-1"></i> Select Vehicle (Truck)</label>
                    <select name="vehicle_id" id="vehicle_id" class="form-select form-select-lg fw-bold text-primary select2">
                        @foreach($vehicles as $v)
                            <option value="{{ $v->id }}" {{ $selectedVehicleId == $v->id ? 'selected' : '' }}>
                                {{ $v->vehicle_number }} {{ $v->vehicle_name ? '('.$v->vehicle_name.')' : '' }} {{ $v->brand ? '['.$v->brand.']' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-7">
                    <div class="d-flex justify-content-md-end gap-3 text-center">
                        <div class="bg-light rounded px-3 py-2 border">
                            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size:0.75rem;">Mounted Tyres</small>
                            <span class="fs-5 fw-bold text-success" id="mounted-count">{{ $vehicleTyres->count() }} / 18</span>
                        </div>
                        <div class="bg-light rounded px-3 py-2 border">
                            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size:0.75rem;">Avg Tread Depth</small>
                            <span class="fs-5 fw-bold text-info" id="avg-tread">
                                {{ $vehicleTyres->avg('tread_depth_current') ? number_format($vehicleTyres->avg('tread_depth_current'), 1).' mm' : 'N/A' }}
                            </span>
                        </div>
                        <div class="bg-light rounded px-3 py-2 border">
                            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size:0.75rem;">Unassigned Pool</small>
                            <span class="fs-5 fw-bold text-warning" id="unassigned-count">{{ $unassignedTyres->count() }}</span>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(!$selectedVehicle)
        <div class="alert alert-info">Please select a vehicle to view its graphical tyre layout.</div>
    @else
        @php
            // Helper function to match tyres by slot code or position alias
            $findTyre = function($codes) use ($vehicleTyres) {
                $codes = (array)$codes;
                return $vehicleTyres->first(function($t) use ($codes) {
                    $pos = strtoupper(trim($t->tyre_position));
                    foreach ($codes as $c) {
                        if ($pos === strtoupper(trim($c))) return true;
                    }
                    return false;
                });
            };
        @endphp

        <div class="row g-4">
            <!-- Left Side: Interactive Graphic Chassis Diagram (8 Left + 8 Right + 2 Spares) -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2">
                        <div class="fw-bold">
                            <i class="bx bx-truck me-2"></i> Chassis Layout (8 Left + 8 Right + 2 Spare): {{ $selectedVehicle->vehicle_number }}
                        </div>
                        <small class="text-light"><i class="bx bx-info-circle me-1"></i> Drag tyre cards into slots to fit/swap positions</small>
                    </div>
                    <div class="card-body p-4 bg-light overflow-auto" style="min-height: 680px;">
                        
                        <!-- Graphical Truck Diagram Wrapper -->
                        <div class="truck-chassis-container mx-auto position-relative py-3" style="max-width: 720px;">
                            
                            <!-- Front Cabin Graphic -->
                            <div class="truck-cab text-center mb-4 p-3 rounded-top bg-dark text-white position-relative shadow" style="border-bottom: 5px solid #007bff;">
                                <div class="fs-6 fw-bold text-uppercase tracking-wider"><i class="bx bx-navigation me-1"></i> TRUCK FRONT CABIN</div>
                                <div class="small text-muted text-light">Windshield / Driver Cockpit</div>
                                <div class="position-absolute top-100 start-50 translate-middle-x bg-secondary rounded-bottom" style="width: 80px; height: 12px;"></div>
                            </div>

                            <!-- Chassis Frame Centerline -->
                            <div class="chassis-frame position-absolute top-0 bottom-0 start-50 translate-middle-x bg-secondary opacity-25" style="width: 120px; z-index: 0; pointer-events: none;"></div>

                            <!-- AXLE 1: FRONT STEERING AXLE (L1 / R1) -->
                            <div class="axle-section mb-4 position-relative" style="z-index: 1;">
                                <div class="text-center mb-2">
                                    <span class="badge bg-primary text-uppercase px-3 py-1 shadow-sm">Axle 1: Front Steering (L1 / R1)</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center position-relative px-2">
                                    <div class="position-absolute start-0 end-0 bg-dark" style="height: 12px; top: 50%; transform: translateY(-50%); z-index: 0;"></div>

                                    <!-- Left Slot 1 (L1) -->
                                    @include('admin.maintenance.tyre-management.partials.slot', [
                                        'slotCode' => 'L1',
                                        'slotName' => 'Front Left (L1)',
                                        'tyre' => $findTyre(['L1', 'FL', 'Front Left'])
                                    ])

                                    <!-- Center Axle Hub -->
                                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 40px; height: 40px; z-index: 1;">
                                        A1
                                    </div>

                                    <!-- Right Slot 1 (R1) -->
                                    @include('admin.maintenance.tyre-management.partials.slot', [
                                        'slotCode' => 'R1',
                                        'slotName' => 'Front Right (R1)',
                                        'tyre' => $findTyre(['R1', 'FR', 'Front Right'])
                                    ])
                                </div>
                            </div>

                            <!-- AXLE 2: DRIVE AXLE 1 DUAL TYRES (L2, L3 / R3, R2) -->
                            <div class="axle-section mb-4 position-relative" style="z-index: 1;">
                                <div class="text-center mb-2">
                                    <span class="badge bg-dark text-uppercase px-3 py-1 shadow-sm">Axle 2: Drive Axle 1 (L2, L3 / R3, R2)</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center position-relative px-2">
                                    <div class="position-absolute start-0 end-0 bg-dark" style="height: 14px; top: 50%; transform: translateY(-50%); z-index: 0;"></div>

                                    <!-- Left Dual Pair (L2 Outer, L3 Inner) -->
                                    <div class="d-flex gap-2 z-1">
                                        @include('admin.maintenance.tyre-management.partials.slot', [
                                            'slotCode' => 'L2',
                                            'slotName' => 'Left Outer 1 (L2)',
                                            'tyre' => $findTyre(['L2', 'ROL1', 'Rear Left Outer'])
                                        ])
                                        @include('admin.maintenance.tyre-management.partials.slot', [
                                            'slotCode' => 'L3',
                                            'slotName' => 'Left Inner 1 (L3)',
                                            'tyre' => $findTyre(['L3', 'RIL1', 'Rear Left Inner'])
                                        ])
                                    </div>

                                    <!-- Differential Hub -->
                                    <div class="bg-dark border border-2 border-warning text-warning rounded-circle d-flex align-items-center justify-content-center fw-bold shadow" style="width: 45px; height: 45px; z-index: 1;">
                                        DIFF1
                                    </div>

                                    <!-- Right Dual Pair (R3 Inner, R2 Outer) -->
                                    <div class="d-flex gap-2 z-1">
                                        @include('admin.maintenance.tyre-management.partials.slot', [
                                            'slotCode' => 'R3',
                                            'slotName' => 'Right Inner 1 (R3)',
                                            'tyre' => $findTyre(['R3', 'RIR1', 'Rear Right Inner'])
                                        ])
                                        @include('admin.maintenance.tyre-management.partials.slot', [
                                            'slotCode' => 'R2',
                                            'slotName' => 'Right Outer 1 (R2)',
                                            'tyre' => $findTyre(['R2', 'ROR1', 'Rear Right Outer'])
                                        ])
                                    </div>
                                </div>
                            </div>

                            <!-- AXLE 3: DRIVE AXLE 2 DUAL TYRES (L4, L5 / R5, R4) -->
                            <div class="axle-section mb-4 position-relative" style="z-index: 1;">
                                <div class="text-center mb-2">
                                    <span class="badge bg-dark text-uppercase px-3 py-1 shadow-sm">Axle 3: Drive Axle 2 (L4, L5 / R5, R4)</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center position-relative px-2">
                                    <div class="position-absolute start-0 end-0 bg-dark" style="height: 14px; top: 50%; transform: translateY(-50%); z-index: 0;"></div>

                                    <!-- Left Dual Pair (L4 Outer, L5 Inner) -->
                                    <div class="d-flex gap-2 z-1">
                                        @include('admin.maintenance.tyre-management.partials.slot', [
                                            'slotCode' => 'L4',
                                            'slotName' => 'Left Outer 2 (L4)',
                                            'tyre' => $findTyre(['L4', 'ROL2'])
                                        ])
                                        @include('admin.maintenance.tyre-management.partials.slot', [
                                            'slotCode' => 'L5',
                                            'slotName' => 'Left Inner 2 (L5)',
                                            'tyre' => $findTyre(['L5', 'RIL2'])
                                        ])
                                    </div>

                                    <!-- Differential Hub -->
                                    <div class="bg-dark border border-2 border-warning text-warning rounded-circle d-flex align-items-center justify-content-center fw-bold shadow" style="width: 45px; height: 45px; z-index: 1;">
                                        DIFF2
                                    </div>

                                    <!-- Right Dual Pair (R5 Inner, R4 Outer) -->
                                    <div class="d-flex gap-2 z-1">
                                        @include('admin.maintenance.tyre-management.partials.slot', [
                                            'slotCode' => 'R5',
                                            'slotName' => 'Right Inner 2 (R5)',
                                            'tyre' => $findTyre(['R5', 'RIR2'])
                                        ])
                                        @include('admin.maintenance.tyre-management.partials.slot', [
                                            'slotCode' => 'R4',
                                            'slotName' => 'Right Outer 2 (R4)',
                                            'tyre' => $findTyre(['R4', 'ROR2'])
                                        ])
                                    </div>
                                </div>
                            </div>

                            <!-- AXLE 4: TRAILER AXLE 1 DUAL TYRES (L6, L7 / R7, R6) -->
                            <div class="axle-section mb-4 position-relative" style="z-index: 1;">
                                <div class="text-center mb-2">
                                    <span class="badge bg-secondary text-uppercase px-3 py-1 shadow-sm">Axle 4: Rear / Trailer Axle (L6, L7 / R7, R6)</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center position-relative px-2">
                                    <div class="position-absolute start-0 end-0 bg-dark" style="height: 14px; top: 50%; transform: translateY(-50%); z-index: 0;"></div>

                                    <!-- Left Dual Pair (L6 Outer, L7 Inner) -->
                                    <div class="d-flex gap-2 z-1">
                                        @include('admin.maintenance.tyre-management.partials.slot', [
                                            'slotCode' => 'L6',
                                            'slotName' => 'Left Outer 3 (L6)',
                                            'tyre' => $findTyre(['L6', 'TLO1', 'ROL3'])
                                        ])
                                        @include('admin.maintenance.tyre-management.partials.slot', [
                                            'slotCode' => 'L7',
                                            'slotName' => 'Left Inner 3 (L7)',
                                            'tyre' => $findTyre(['L7', 'TLI1', 'RIL3'])
                                        ])
                                    </div>

                                    <!-- Center Axle Hub -->
                                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 40px; height: 40px; z-index: 1;">
                                        A4
                                    </div>

                                    <!-- Right Dual Pair (R7 Inner, R6 Outer) -->
                                    <div class="d-flex gap-2 z-1">
                                        @include('admin.maintenance.tyre-management.partials.slot', [
                                            'slotCode' => 'R7',
                                            'slotName' => 'Right Inner 3 (R7)',
                                            'tyre' => $findTyre(['R7', 'TRI1', 'RIR3'])
                                        ])
                                        @include('admin.maintenance.tyre-management.partials.slot', [
                                            'slotCode' => 'R6',
                                            'slotName' => 'Right Outer 3 (R6)',
                                            'tyre' => $findTyre(['R6', 'TRO1', 'ROR3'])
                                        ])
                                    </div>
                                </div>
                            </div>

                            <!-- AXLE 5: AUXILIARY REAR AXLE (L8 / R8) -->
                            <div class="axle-section mb-4 position-relative" style="z-index: 1;">
                                <div class="text-center mb-2">
                                    <span class="badge bg-secondary text-uppercase px-3 py-1 shadow-sm">Axle 5: Auxiliary Rear Axle (L8 / R8)</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center position-relative px-2">
                                    <div class="position-absolute start-0 end-0 bg-dark" style="height: 12px; top: 50%; transform: translateY(-50%); z-index: 0;"></div>

                                    <!-- Left Slot 8 (L8) -->
                                    @include('admin.maintenance.tyre-management.partials.slot', [
                                        'slotCode' => 'L8',
                                        'slotName' => 'Rear Left (L8)',
                                        'tyre' => $findTyre(['L8', 'RL8'])
                                    ])

                                    <!-- Center Axle Hub -->
                                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 40px; height: 40px; z-index: 1;">
                                        A5
                                    </div>

                                    <!-- Right Slot 8 (R8) -->
                                    @include('admin.maintenance.tyre-management.partials.slot', [
                                        'slotCode' => 'R8',
                                        'slotName' => 'Rear Right (R8)',
                                        'tyre' => $findTyre(['R8', 'RR8'])
                                    ])
                                </div>
                            </div>

                            <!-- SPARE STEPNEY CARRIER (2 SPARES: SP1, SP2) -->
                            <div class="axle-section mb-3 position-relative" style="z-index: 1;">
                                <div class="text-center mb-2">
                                    <span class="badge bg-warning text-dark text-uppercase px-3 py-1 shadow-sm"><i class="bx bx-shield me-1"></i> Spare Wheel Carrier (2 Spares)</span>
                                </div>
                                <div class="d-flex justify-content-center gap-4 align-items-center position-relative px-2">
                                    @include('admin.maintenance.tyre-management.partials.slot', [
                                        'slotCode' => 'SP1',
                                        'slotName' => 'Spare 1 (Stepney)',
                                        'tyre' => $findTyre(['SP1', 'Spare 1', 'Spare'])
                                    ])
                                    @include('admin.maintenance.tyre-management.partials.slot', [
                                        'slotCode' => 'SP2',
                                        'slotName' => 'Spare 2 (Stepney)',
                                        'tyre' => $findTyre(['SP2', 'Spare 2'])
                                    ])
                                </div>
                            </div>

                            <!-- Rear Bumper Graphic -->
                            <div class="truck-bumper text-center p-2 rounded-bottom bg-secondary text-white fw-bold shadow-sm" style="border-top: 4px dashed #ffc107;">
                                <small><i class="bx bx-pause me-1"></i> TRUCK REAR BUMPER / REFLECTOR STRIP</small>
                            </div>

                        </div>

                    </div>
                </div>
            </div>

            <!-- Right Side: Unassigned Tyre Inventory & Pool Rack -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
                        <div class="fw-bold"><i class="bx bx-archive me-1"></i> Tyre Pool & Inventory Rack</div>
                        <span class="badge bg-light text-primary" id="unassigned-badge-count">{{ $unassignedTyres->count() }}</span>
                    </div>
                    <div class="card-body p-3 bg-light">
                        <div class="text-muted small mb-3">
                            <i class="bx bx-info-circle me-1"></i> Drag tyres from this rack into wheel slots on the truck layout to fit them. Or drag tyres back here to unmount them.
                        </div>

                        <!-- Drop zone for unassigning tyres -->
                        <div class="unassigned-drop-zone p-3 rounded border border-2 border-dashed border-primary bg-white text-center mb-3 shadow-xs" 
                             data-slot-code="Unassigned"
                             ondragover="handleDragOver(event)"
                             ondragleave="handleDragLeave(event)"
                             ondrop="handleDrop(event, 'Unassigned')">
                            <i class="bx bx-cloud-upload fs-3 text-primary d-block mb-1"></i>
                            <span class="fw-bold text-primary">Unmount Tyre Zone</span>
                            <small class="d-block text-muted">Drop any mounted tyre here to unmount from vehicle</small>
                        </div>

                        <!-- Unassigned Tyre Pool Container -->
                        <div id="unassigned-tyres-list" class="d-flex flex-column gap-2 overflow-auto" style="max-height: 600px;">
                            @forelse($unassignedTyres as $unTyre)
                                @include('admin.maintenance.tyre-management.partials.tyre-card', ['tyre' => $unTyre])
                            @empty
                                <div class="text-center text-muted py-4 id-no-unassigned">
                                    <i class="bx bx-check-circle fs-2 text-success d-block mb-1"></i>
                                    No unassigned tyres in inventory pool
                                </div>
                            @endforelse
                        </div>

                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
/* Tyre Slot Styles */
.tyre-slot {
    width: 120px;
    min-height: 140px;
    background-color: #ffffff;
    border: 2px dashed #6c757d;
    border-radius: 10px;
    transition: all 0.2s ease-in-out;
    position: relative;
}
.tyre-slot.drag-over {
    border-color: #0d6efd !important;
    background-color: #e7f1ff !important;
    transform: scale(1.05);
    box-shadow: 0 0 15px rgba(13, 110, 253, 0.4);
}
.tyre-slot.occupied {
    border-style: solid;
    border-color: #343a40;
}

/* Draggable Tyre Card */
.tyre-card {
    cursor: grab;
    user-select: none;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.tyre-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15) !important;
}
.tyre-card:active {
    cursor: grabbing;
}
.tyre-card.dragging {
    opacity: 0.4;
}

.unassigned-drop-zone.drag-over {
    background-color: #e7f1ff !important;
    border-color: #0d6efd !important;
    transform: scale(1.02);
}
</style>

<script>
let currentVehicleId = {{ $selectedVehicleId ?? 'null' }};
let draggedTyreId = null;
let draggedFromSlot = null;

function handleDragStart(event, tyreId, slotCode) {
    draggedTyreId = tyreId;
    draggedFromSlot = slotCode;
    event.dataTransfer.setData('text/plain', tyreId);
    event.dataTransfer.effectAllowed = 'move';
    event.target.classList.add('dragging');
}

function handleDragEnd(event) {
    event.target.classList.remove('dragging');
}

function handleDragOver(event) {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
    event.currentTarget.classList.add('drag-over');
}

function handleDragLeave(event) {
    event.currentTarget.classList.remove('drag-over');
}

function handleDrop(event, targetSlotCode) {
    event.preventDefault();
    const targetElement = event.currentTarget;
    targetElement.classList.remove('drag-over');

    if (!draggedTyreId || !currentVehicleId) return;

    let targetTyreId = null;
    const existingTyreCard = targetElement.querySelector('.tyre-card');
    if (existingTyreCard && targetSlotCode !== 'Unassigned') {
        targetTyreId = existingTyreCard.getAttribute('data-tyre-id');
    }

    updateTyrePosition(draggedTyreId, targetSlotCode, targetTyreId);
}

function updateTyrePosition(tyreId, newPosition, targetTyreId = null) {
    showToast('info', 'Updating tyre position...');

    fetch('{{ route("admin.maintenance.tyre-management.update-position") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            tyre_id: tyreId,
            vehicle_id: currentVehicleId,
            new_position: newPosition,
            target_tyre_id: targetTyreId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message);
            
            // Dynamically update affected wheel slots in DOM without page reload
            if (data.slots_html) {
                Object.keys(data.slots_html).forEach(slotCode => {
                    const slotEl = document.querySelector(`.tyre-slot[data-slot-code="${slotCode}"]`);
                    if (slotEl) {
                        slotEl.outerHTML = data.slots_html[slotCode];
                    }
                });
            }

            // Dynamically update unassigned tyres pool
            if (data.unassigned_html !== undefined) {
                const unassignedList = document.getElementById('unassigned-tyres-list');
                if (unassignedList) {
                    unassignedList.innerHTML = data.unassigned_html;
                }
            }

            // Dynamically update header summary stats
            if (data.stats) {
                const mountedEl = document.getElementById('mounted-count');
                const avgTreadEl = document.getElementById('avg-tread');
                const unassignedCountEl = document.getElementById('unassigned-count');
                const unassignedBadgeEl = document.getElementById('unassigned-badge-count');

                if (mountedEl) mountedEl.textContent = data.stats.mounted_count;
                if (avgTreadEl) avgTreadEl.textContent = data.stats.avg_tread;
                if (unassignedCountEl) unassignedCountEl.textContent = data.stats.unassigned_count;
                if (unassignedBadgeEl) unassignedBadgeEl.textContent = data.stats.unassigned_count;
            }
        } else {
            showToast('danger', data.message || 'Failed to update tyre position');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('danger', 'Error updating tyre position');
    });
}

function showToast(type, message) {
    const toastContainer = document.getElementById('toast-container');
    const toastId = 'toast-' + Date.now();
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0 show shadow" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body fw-bold">
                    <i class="bx bx-info-circle me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    setTimeout(() => {
        const el = document.getElementById(toastId);
        if (el) el.remove();
    }, 4000);
}
$(document).ready(function() {
    $('#vehicle_id').on('change', function() {
        $('#vehicle-select-form').submit();
    });
});
</script>
@endsection
