@extends('admin.layout.header')

@section('main-content')


<!-- Page Container START -->
<div class="page-container">

    <!-- Content Wrapper START -->
    <div class="main-content">
        <div class="page-header">
            <h2 class="header-title">Edit Student Information</h2>
            <div class="header-sub-title">
                <nav class="breadcrumb breadcrumb-dash">
                    <a href="{{route('admin.dashboard')}}" class="breadcrumb-item"><i
                            class="anticon anticon-home m-r-5"></i>Home</a>
                    <a class="breadcrumb-item" href="{{route('admin.students.list-of-students')}}">List of Student</a>
                    <span class="breadcrumb-item active">Edit Student Information</span>
                </nav>
            </div>
        </div>
        <!-- Content goes Here -->
        @include('admin.alert.alert_message')
        @include('admin.alert.alert_danger')
        <div class="card">
            <div class="card-body">
                <h4>Student Personal Information</h4>
                <div class="m-t-25">
                    <form method="POST" action="{{route('admin.student.update_information_student', $student->id)}}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT') {{-- Use PUT for updating data --}}

                        <div class="form-group row">
                            <label for="firstname" class="col-sm-2 col-form-label">First Name</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="firstname" name="firstname"
                                    value="{{ old('firstname', $student->user->firstname) }}" placeholder="Enter First Name">
                            </div>
                            @error('firstname')
                            <span class="badge badge-pill badge-red">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group row">
                            <label for="middlename" class="col-sm-2 col-form-label">Middle Name</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="middlename" name="middlename"
                                    value="{{ old('middlename', $student->user->middlename) }}"
                                    placeholder="Enter Middle Name">
                            </div>
                            @error('middlename')
                            <span class="badge badge-pill badge-red">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group row">
                            <label for="lastname" class="col-sm-2 col-form-label">Last Name</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="lastname" name="lastname"
                                    value="{{ old('lastname', $student->user->lastname) }}" placeholder="Enter Last Name">
                            </div>
                            @error('lastname')
                            <span class="badge badge-pill badge-red">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group row">
                            <label for="suffix_name" class="col-sm-2 col-form-label">Suffix Name</label>
                            <div class="col-sm-2">
                                <select id="suffix_name" name="suffix_name" class="form-control">
                                    <option selected disabled>Suffix Name</option>
                                    <option value="Jr." {{ old('suffix_name', $student->user->suffix_name ?? '') == 'Jr.' ? 'selected' : '' }}>Jr.</option>
                                    <option value="Sr." {{ old('suffix_name', $student->user->suffix_name ?? '') == 'Sr.' ? 'selected' : '' }}>Sr.</option>
                                    <option value="I" {{ old('suffix_name', $student->user->suffix_name ?? '') == 'I' ? 'selected' : '' }}>I</option>
                                    <option value="II" {{ old('suffix_name', $student->user->suffix_name ?? '') == 'II' ? 'selected' : '' }}>II</option>
                                    <option value="III" {{ old('suffix_name', $student->user->suffix_name ?? '') == 'III' ? 'selected' : '' }}>III</option>
                                    <option value="IV" {{ old('suffix_name', $student->user->suffix_name ?? '') == 'IV' ? 'selected' : '' }}>IV</option>
                                    <option value="V" {{ old('suffix_name', $student->user->suffix_name ?? '') == 'V' ? 'selected' : '' }}>V</option>
                                </select>
                            </div>
                            @error('suffix_name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        
                        <div class="form-group row">
                            <label for="student_id" class="col-sm-2 col-form-label">Student ID</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" id="student_id" name="student_id" readonly
                                    value="{{ old('student_id', $student->student_number) }}"
                                    placeholder="Enter Student ID">
                            </div>
                            @error('student_id')
                            <span class="badge badge-pill badge-red">{{ $message }}</span>
                            @enderror
                        </div>

                      

                        <div class="form-group row">
                            <label for="department_id" class="col-sm-2 col-form-label">Department</label>
                            <div class="col-sm-5">
                                <select id="department_id" name="department_id" class="form-control">
                                    <option selected disabled>Choose Department</option>
                                    @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id', $student->
                                        department_id ?? '') == $department->id ? 'selected' : '' }}>
                                        {{ $department->department_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('department_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group row">
                            <label for="program" class="col-sm-2 col-form-label">Program</label>
                            <div class="col-sm-7">
                                <select id="program" name="program" class="form-control">
                                    <option selected disabled>Choose Program</option>
                                    @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ old('program', $student->program ?? '') ==
                                        $course->id ? 'selected' : '' }}>
                                        {{ $course->course_name }}
                                    </option>
                                    @endforeach


                                </select>
                            </div>
                            @error('program')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group row">
                            <label for="year" class="col-sm-2 col-form-label">Year</label>
                            <div class="col-sm-3">
                                <select id="year" name="year" class="form-control">
                                    <option selected disabled>Choose Year</option>
                                    <option value="1st" {{ old('year', $student->year) == '1st' ? 'selected' : '' }}>1st
                                        Year</option>
                                    <option value="2nd" {{ old('year', $student->year) == '2nd' ? 'selected' : '' }}>2nd
                                        Year</option>
                                    <option value="3rd" {{ old('year', $student->year) == '3rd' ? 'selected' : '' }}>3rd
                                        Year</option>
                                    <option value="4th" {{ old('year', $student->year) == '4th' ? 'selected' : '' }}>4th
                                        Year</option>
                                </select>
                            </div>
                            @error('year')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group row">
                            <label for="academic_id" class="col-sm-2 col-form-label">Academic Year</label>
                            <div class="col-sm-3">
                                <select id="academic_id" name="academic_id" class="form-control">
                                    <option selected disabled>Choose Academic Year</option>
                                    @foreach($academic_year as $ay)
                                    <option value="{{ $ay->id }}" {{ old('academic_id', $student->
                                        academic_id ?? '') == $ay->id ? 'selected' : '' }}>
                                         {{ $ay->academic_year }} - {{ $ay->semester }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('academic_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                       

                        <div class="form-group row">
                            <div class="col-sm-10">
                                <a class="btn btn-danger btn-tone m-r-5" href="{{ route('admin.students.list-of-students') }}"
                                    role="button">Back</a>
                                <button type="submit" class="btn btn-success btn-tone m-r-5">Update Student
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