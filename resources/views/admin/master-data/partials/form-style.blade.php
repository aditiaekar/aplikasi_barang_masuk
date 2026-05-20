@push('styles')
<style>
    .form-card {
        background: rgba(255, 255, 255, 0.94);
        border: 1px solid rgba(229, 231, 235, 0.85);
        border-radius: 22px;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .form-card-header {
        padding: 1.25rem 1.35rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .form-card-header h5 {
        margin: 0;
        font-weight: 800;
        color: #1f2937;
    }

    .form-card-header p {
        margin: 0.25rem 0 0;
        color: #6b7280;
        font-size: 0.88rem;
    }

    .form-card-body {
        padding: 1.35rem;
    }

    .form-control,
    .form-select {
        border-radius: 14px;
        border-color: #e5e7eb;
        padding: 0.72rem 0.9rem;
        font-size: 0.92rem;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #be123c;
        box-shadow: 0 0 0 0.2rem rgba(190, 18, 60, 0.12);
    }

    .btn-red {
        background: linear-gradient(135deg, #9f1239, #be123c);
        color: #fff;
        border: none;
        border-radius: 14px;
        padding: 0.7rem 1rem;
        font-weight: 700;
        box-shadow: 0 12px 24px rgba(159, 18, 57, 0.2);
    }

    .btn-red:hover {
        color: #fff;
    }
</style>
@endpush