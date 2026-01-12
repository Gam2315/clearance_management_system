@extends('admin.layout.header')

@section('main-content')


<!-- Page Container START -->
<div class="page-container">

    <!-- Content Wrapper START -->
    <div class="main-content">
        <div class="page-header">
            <h2 class="header-title">Update Academic Year</h2>
            <div class="header-sub-title">
                <nav class="breadcrumb breadcrumb-dash">
                    <a href="{{route('admin.dashboard')}}" class="breadcrumb-item"><i class="anticon anticon-home m-r-5"></i>Home</a>
                    <a class="breadcrumb-item" href="{{ route('admin.academic_year.list_of_academic_year') }}">Academic Year</a>
                    <span class="breadcrumb-item active">Update Academic Year</span>
                </nav>
            </div>
        </div>
        <!-- Content goes Here -->
        @include('admin.alert.alert_message')
        @include('admin.alert.alert_danger')
        <div class="card">
            <div class="card-body">
                <h4>Academic Year Information</h4>
                <div class="m-t-25">
                    <form method="POST" action="{{route('admin.academic_year.update_information_academic_year', $academic_year->id)}}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="form-group row">
                            <label for="academic_year" class="col-sm-2 col-form-label">Academic Year</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" id="academic_year" name="academic_year"
                                    placeholder="Enter A.Y." value="{{ old('academic_year', $academic_year->academic_year) }}">
                            </div>
                            @error('academic_year')
                            <span class="badge badge-pill badge-red">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group row">
                            <label for="semester" class="col-sm-2 col-form-label">Semester</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" id="semester" name="semester"
                                    placeholder="Enter Semester" value="{{ old('semester', $academic_year->semester)  }}">
                            </div>
                            @error('semester')
                            <span class="badge badge-pill badge-red">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group row">
                            <label for="status" class="col-sm-2 col-form-label">Status</label>
                            <div class="col-sm-2">
                                <select id="status" name="status" class="form-control">
                                    <option selected disabled>Select Status</option>
                                    <option value="active" {{ old('status', $academic_year->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $academic_year->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                   
                                </select>
                            </div>
                            @error('status')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="form-group row">
                            <div class="col-sm-10">
                                <a class="btn btn-danger btn-tone m-r-5" href="{{ route('admin.academic_year.list_of_academic_year') }}" role="button">Back</a>
                                <button type="submit" class="btn btn-success btn-tone m-r-5">Update Academic Year</button>
                                   
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