@extends('admin.layout.header')

@section('main-content')


<!-- Page Container START -->
<div class="page-container">

    <!-- Content Wrapper START -->
    <div class="main-content">
        <div class="page-header">
            <h2 class="header-title">Edit Program Information</h2>
            <div class="header-sub-title">
                <nav class="breadcrumb breadcrumb-dash">
                    <a href="{{route('admin.dashboard')}}" class="breadcrumb-item"><i class="anticon anticon-home m-r-5"></i>Home</a>
                    <a class="breadcrumb-item" href="{{ route('program.list-of-program') }}">Program</a>
                    <span class="breadcrumb-item active">Add Edit Program</span>
                </nav>
            </div>
        </div>
        <!-- Content goes Here -->
        @include('admin.alert.alert_message')
        @include('admin.alert.alert_danger')
        <div class="card">
            <div class="card-body">
                <h4>Program Information</h4>
                <div class="m-t-25">
                    <form method="POST" action="{{route('program.update_information_program', $program->id)}}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="form-group row">
                            <label for="department_id" class="col-sm-2 col-form-label">Department</label>
                            <div class="col-sm-7">
                                <select id="department_id" name="department_id" class="form-control">
                                    <option selected disabled>Choose Department</option>
                                    @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id', $program->
                                        department_id ?? '') == $department->id ? 'selected' : '' }}>
                                        {{ $department->department_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('department_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group row">
                            <label for="program_code" class="col-sm-2 col-form-label">Program Code</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="program_code" name="program_code"
                                    placeholder="Enter Program Code" value="{{ old('program_code', $program->course_code) }}">
                            </div>
                            @error('program_code')
                            <span class="badge badge-pill badge-red">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group row">
                            <label for="program_name" class="col-sm-2 col-form-label">Program Name</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="program_name" name="program_name"
                                    placeholder="Enter Program Name" value="{{ old('program_name', $program->course_name) }}">
                            </div>
                            @error('program_name')
                            <span class="badge badge-pill badge-red">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="form-group row">
                            <div class="col-sm-10">
                                <a class="btn btn-danger btn-tone m-r-5" href="{{ route('program.list-of-program') }}" role="button">Back</a>
                                <button type="submit" class="btn btn-success btn-tone m-r-5">Update Program
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