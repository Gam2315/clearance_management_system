@if(session()->has('error'))
<div class="alert alert-danger">
    <div class="d-flex align-items-center justify-content-start">
        <span class="alert-icon">
            <i class="anticon anticon-check-o"></i>
        </span>
        <span>{{session('error')}}</span>
    </div>
</div>
@endif