<div class="d-flex justify-content-end gap-1">
    <a href="{{ $editRoute }}"
       class="action-btn"
       title="Edit">
        <i class="bx bx-edit"></i>
    </a>

    <form action="{{ $destroyRoute }}"
          method="POST"
          class="d-inline"
          onsubmit="return confirm('Yakin ingin menghapus data ini?')">
        @csrf
        @method('DELETE')

        <button type="submit"
                class="action-btn"
                title="Hapus">
            <i class="bx bx-trash"></i>
        </button>
    </form>
</div>
