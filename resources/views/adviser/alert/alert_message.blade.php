@if(session()->has('success'))
<div class="alert alert-success border-0 mb-3" style="
    background: #d1fae5;
    border-radius: 12px;
    border-left: 4px solid #10b981;
    padding: 1rem;
">
    <div class="d-flex align-items-center">
        <div class="me-3">
            <i class="fas fa-check-circle" style="font-size: 1.5rem; color: #10b981;"></i>
        </div>
        <div>
            <h6 class="mb-1 fw-bold text-success">Success!</h6>
            <p class="mb-0 text-success">{{session('success')}}</p>
        </div>
    </div>
</div>
@endif


