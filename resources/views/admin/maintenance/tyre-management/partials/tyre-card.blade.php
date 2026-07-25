@php
    $slotCode = $slotCode ?? 'Unassigned';
    $tread = $tyre->tread_depth_current;
    $treadColor = 'success';
    if ($tread !== null) {
        if ($tread < 4) {
            $treadColor = 'danger';
        } elseif ($tread < 8) {
            $treadColor = 'warning';
        }
    }
@endphp

<div class="tyre-card w-100 p-2 rounded border bg-white shadow-xs position-relative" 
     draggable="true" 
     data-tyre-id="{{ $tyre->id }}"
     ondragstart="handleDragStart(event, {{ $tyre->id }}, '{{ $slotCode }}')"
     ondragend="handleDragEnd(event)">
    
    <div class="d-flex justify-content-between align-items-start mb-1">
        <span class="fw-bold text-dark text-truncate d-inline-block" style="max-width: 85px; font-size: 0.78rem;" title="{{ $tyre->tyre_brand }}">
            <i class="bx bx-disc me-1 text-primary"></i>{{ $tyre->tyre_brand }}
        </span>
        <a href="{{ route('admin.maintenance.tyre-management.edit', $tyre) }}" class="text-secondary p-0" title="Edit Tyre" onclick="event.stopPropagation();">
            <i class="bx bx-edit-alt" style="font-size: 0.85rem;"></i>
        </a>
    </div>

    <div class="text-muted text-truncate mb-1" style="font-size: 0.7rem;" title="Serial: {{ $tyre->serial_number ?? 'N/A' }}">
        #{{ $tyre->serial_number ?? 'S/N: N/A' }}
    </div>

    <div class="d-flex justify-content-between align-items-center mt-1 pt-1 border-top" style="font-size: 0.68rem;">
        <span class="badge bg-label-{{ $treadColor }} px-1 py-0" title="Current Tread Depth">
            {{ $tread !== null ? number_format($tread, 1).'mm' : 'N/A' }}
        </span>
        @if($tyre->pressure_psi)
            <span class="text-muted" title="Tyre Pressure">
                <i class="bx bx-tachometer me-1"></i>{{ $tyre->pressure_psi }} PSI
            </span>
        @endif
    </div>
</div>
