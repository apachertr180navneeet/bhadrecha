<div class="d-inline-flex gap-1">
    <a href="{{ route('admin.documents.preview', $doc->id) }}" class="btn btn-icon btn-sm btn-outline-info" target="_blank" data-bs-toggle="tooltip" title="Preview Browser">
        <i class="bx bx-show-alt"></i>
    </a>
    <a href="{{ route('admin.documents.show', $doc->id) }}" class="btn btn-icon btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="View Details">
        <i class="bx bx-info-circle"></i>
    </a>
    <a href="{{ route('admin.documents.download', $doc->id) }}" class="btn btn-icon btn-sm btn-outline-success" data-bs-toggle="tooltip" title="Download">
        <i class="bx bx-download"></i>
    </a>
    <a href="{{ route('admin.documents.edit', $doc->id) }}" class="btn btn-icon btn-sm btn-outline-warning" data-bs-toggle="tooltip" title="Edit Metadata">
        <i class="bx bx-edit-alt"></i>
    </a>
    <form action="{{ route('admin.documents.destroy', $doc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Move this document to trash?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-icon btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Trash Document">
            <i class="bx bx-trash"></i>
        </button>
    </form>
</div>
