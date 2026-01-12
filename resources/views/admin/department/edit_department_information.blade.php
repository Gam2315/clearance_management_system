@extends('admin.layout.header')

@section('main-content')


<!-- Page Container START -->
<div class="page-container">

    <!-- Content Wrapper START -->
    <div class="main-content">
        <div class="page-header">
            <h2 class="header-title">Edit Department Information</h2>
            <div class="header-sub-title">
                <nav class="breadcrumb breadcrumb-dash">
                    <a href="{{route('admin.dashboard')}}" class="breadcrumb-item"><i class="anticon anticon-home m-r-5"></i>Home</a>
                    <a class="breadcrumb-item" href="{{ route('admin.department.list-of-department') }}">Department</a>
                    <span class="breadcrumb-item active">Edit Department Information</span>
                </nav>
            </div>
        </div>
        <!-- Content goes Here -->
        @include('admin.alert.alert_message')
        @include('admin.alert.alert_danger')
        <div class="card">
            <div class="card-body">
                <h4>Department Information</h4>
                <div class="m-t-25">
                    <form method="POST" action="{{route('admin.department.update_information_department', $department->id)}}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="form-group row">
                            <label for="department_code" class="col-sm-2 col-form-label">Department Code</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" id="department_code" name="department_code"
                                    placeholder="Enter Department Code" value="{{ old('department_code', $department->department_code) }}">
                            </div>
                            @error('department_code')
                            <span class="badge badge-pill badge-red">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group row">
                            <label for="department_name" class="col-sm-2 col-form-label">Department Name</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="department_name" name="department_name"
                                    placeholder="Enter Department Name" value="{{ old('department_name', $department->department_name) }}">
                            </div>
                            @error('department_name')
                            <span class="badge badge-pill badge-red">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group row">
                            <label for="department_head" class="col-sm-2 col-form-label">Department Head</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="department_head" name="department_head"
                                    placeholder="Enter Department Head" value="{{ old('department_head', $department->department_head) }}">
                            </div>
                            @error('department_head')
                            <span class="badge badge-pill badge-red">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="form-group row">
                            <div class="col-sm-10">
                                <a class="btn btn-danger btn-tone m-r-5" href="{{ route('admin.department.list-of-department') }}" role="button">Back</a>
                                <button type="submit" class="btn btn-success btn-tone m-r-5">Update Department
                                    Information</button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
    <!-- Content Wrapper END -->

    @include('admin.layout.footer')

</div>
<!-- Page Container END -->


@endsection