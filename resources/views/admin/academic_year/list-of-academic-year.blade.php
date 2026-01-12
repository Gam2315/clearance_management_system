@extends('admin.layout.header')

@section('main-content')


<!-- Page Container START -->
<div class="page-container">

    <!-- Content Wrapper START -->
    <div class="main-content">
        <div class="page-header">
            <h2 class="header-title">List of Academic Year</h2>
            <div class="header-sub-title">
                <nav class="breadcrumb breadcrumb-dash">
                    <a href="{{route('admin.dashboard')}}" class="breadcrumb-item"><i
                            class="anticon anticon-home m-r-5"></i>Home</a>
                    <a class="breadcrumb-item" href="{{ route('admin.academic_year.list_of_academic_year') }}">Academic Year</a>
                    <span class="breadcrumb-item active">List of Academic Year</span>
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
                    <table id="data-table" class="table">
                        <thead>
                            <tr>
                                <th>Academic Year</th>
                                <th>Semester</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($academic_years as $ay)
                            <tr>
                                <td>{{ $ay->academic_year }}</td>
                                <td>{{ $ay->semester}}</td>
                                <td>{{ $ay->status }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{route('admin.academic_year.edit_information_academic_year', $ay->id)}}"
                                            class="btn btn-warning btn-tone m-r-5"><i
                                                class="anticon anticon-edit"></i></a>

                                        <button class="btn btn-danger btn-tone m-r-5" data-toggle="modal"
                                            data-target="#deleteModal-{{ $ay->id }}">
                                            <i class="anticon anticon-delete"></i>
                                        </button>
                                    </div>

                                </td>

                                <div class="modal fade" id="deleteModal-{{ $ay->id }}" tabindex="-1" role="dialog"
                                    aria-labelledby="deleteModalLabel-{{ $ay->id }}" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel-{{ $ay->id }}">Delete
                                                    Academic Year</h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close">
                                                    <i class="anticon anticon-close"></i>
                                                </button>
                                            </div>
                                            <form action="{{route('admin.academic_year.delete_information_academic_year', $ay->id)}}"
                                                method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-body">
                                                    <p>Are you sure you want to delete this Academic Year?</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-warning btn-tone m-r-5"
                                                        data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger btn-tone m-r-5">Delete
                                                        Academic Year</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>


                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Academic Year</th>
                                <th>Semester</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>

                    </table>

                </div>

            </div>
        </div>
    </div>
    <!-- Content Wrapper END -->

    @include('admin.layout.footer')

</div>
<!-- Page Container END -->































































@endsection