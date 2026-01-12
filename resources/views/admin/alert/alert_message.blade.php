@if(session()->has('success'))
<div class="alert alert-success">
    <div class="d-flex align-items-center justify-content-start">
        <span class="alert-icon">
            <i class="anticon anticon-check-o"></i>
        </span>
        <span>{!! session('success') !!}</span>
    </div>
</div>
    
@endif


