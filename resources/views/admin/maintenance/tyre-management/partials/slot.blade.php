<div class="tyre-slot p-2 d-flex flex-column justify-content-between align-items-center {{ $tyre ? 'occupied' : 'empty' }}"
     data-slot-code="{{ $slotCode }}"
     ondragover="handleDragOver(event)"
     ondragleave="handleDragLeave(event)"
     ondrop="handleDrop(event, '{{ $slotCode }}')">

    <!-- Slot Header Position Badge -->
    <div class="w-100 d-flex justify-content-between align-items-center mb-1">
        <span class="badge bg-primary text-white fw-bold shadow-xs" style="font-size: 0.68rem; padding: 3px 6px;">{{ $slotCode }}</span>
        <small class="slot-title text-truncate opacity-75 fw-semibold" style="font-size: 0.65rem;" title="{{ $slotName }}">{{ $slotName }}</small>
    </div>

    <!-- Tyre Content OR Empty Placeholder -->
    @if($tyre)
        @include('admin.maintenance.tyre-management.partials.tyre-card', ['tyre' => $tyre, 'slotCode' => $slotCode])
    @else
        <div class="empty-slot-placeholder text-center my-auto py-2">
            <i class="bx bx-plus-circle fs-3 d-block mb-1 opacity-50"></i>
            <small class="empty-slot-text opacity-75 fw-semibold d-block" style="font-size: 0.68rem;">Empty Slot</small>
        </div>
    @endif
</div>
