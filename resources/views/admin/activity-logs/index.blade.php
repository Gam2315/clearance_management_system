@extends('admin.layout.header')

@section('main-content')


<!-- Page Container START -->
<div class="page-container">

    <!-- Content Wrapper START -->
    <div class="main-content">
        <div class="page-header">
            <h2 class="header-title">Activity Logs</h2>
            <div class="header-sub-title">
                <nav class="breadcrumb breadcrumb-dash">
                    <a href="{{route('admin.dashboard')}}" class="breadcrumb-item"><i
                            class="anticon anticon-home m-r-5"></i>Home</a>
                    <a class="breadcrumb-item" href="{{route('admin.activity-logs.index')}}"">Activity Logs</a>
                    <span class=" breadcrumb-item active">View Activity Logs</span>
                </nav>
            </div>
        </div>
        <!-- Content goes Here -->
        @include('admin.alert.alert_message')
        @include('admin.alert.alert_danger')
        <div class="card">
            <div class="card-body">

                <div class="m-t-25">
                    <h4>Log Activity</h4>
                    <table id="data-table" class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Description</th>
                                <th >Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                            <tr >
                                <td >
                                  @if($log->causer)
                                      {{ $log->causer->lastname. ' ' . $log->causer->firstname .' '. $log->causer->middlename }}
                                  @else
                                      <span class="text-muted">System</span>
                                  @endif
                                </td>



                                <td>
                                  @if($log->causer)
                                      {{ $log->causer->role }}
                                  @else
                                      <span class="text-muted">System</span>
                                  @endif
                                </td>
                                 <td >{{ $log->description }}</td>
                                <td >{{ $log->created_at->format('F j, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th >Name</th>
                                <th>Role</th>
                                 <th>Description</th>
                                <th >Date</th>
                            </tr>
                        </tfoot>

                    </table>

                    <!-- Pagination -->
                  


                  
                </div>
            </div>
        </div>
    </div>
    <!-- Content Wrapper END -->

    @include('admin.layout.footer')

</div>
<!-- Page Container END -->



@endsection