<div class="tyre-slot p-2 d-flex flex-column justify-content-between align-items-center {{ $tyre ? 'occupied shadow-sm bg-white' : 'bg-light' }}"
     data-slot-code="{{ $slotCode }}"
     ondragover="handleDragOver(event)"
     ondragleave="handleDragLeave(event)"
     ondrop="handleDrop(event, '{{ $slotCode }}')">

    <!-- Slot Header Position Badge -->
    <div class="w-100 d-flex justify-content-between align-items-center mb-1">
        <span class="badge bg-dark text-white fw-bold" style="font-size: 0.7rem;">{{ $slotCode }}</span>
        <small class="text-muted text-truncate" style="font-size: 0.65rem;" title="{{ $slotName }}">{{ $slotName }}</small>
    </div>

    <!-- Tyre Content OR Empty Placeholder -->
    @if($tyre)
        @include('admin.maintenance.tyre-management.partials.tyre-card', ['tyre' => $tyre, 'slotCode' => $slotCode])
    @else
        <div class="empty-slot-placeholder text-center my-auto py-2">
            <i class="bx bx-plus-circle text-secondary fs-3 d-block mb-1 opacity-50"></i>
            <small class="text-muted fw-semibold d-block" style="font-size: 0.7rem;">Empty Slot</small>
        </div>
    @endif
</div>
