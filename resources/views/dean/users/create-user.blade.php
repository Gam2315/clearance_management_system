@extends('dean.layout.layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Create New User</h4>
                    <a href="{{ route('dean.manage-users') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Users
                    </a>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('dean.store-user') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="firstname" class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="firstname" name="firstname" 
                                           value="{{ old('firstname') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="middlename" class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" id="middlename" name="middlename" 
                                           value="{{ old('middlename') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="lastname" class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="lastname" name="lastname" 
                                           value="{{ old('lastname') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="suffix_name" class="form-label">Suffix</label>
                                    <input type="text" class="form-control" id="suffix_name" name="suffix_name" 
                                           value="{{ old('suffix_name') }}" placeholder="Jr., Sr., III, etc.">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="employee_id" class="form-label">Employee ID <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="employee_id" name="employee_id" 
                                           value="{{ old('employee_id') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="">Select Role</option>
                                        <option value="employee" {{ old('role') === 'employee' ? 'selected' : '' }}>Employee</option>
                                        <option value="adviser" {{ old('role') === 'adviser' ? 'selected' : '' }}>Adviser</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="department_id" class="form-label">Department</label>
                                    <select class="form-select" id="department_id" name="department_id" disabled>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" selected>{{ $department->department_name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Users will be assigned to your department</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="picture" class="form-label">Profile Picture</label>
                                    <input type="file" class="form-control" id="picture" name="picture" 
                                           accept="image/jpeg,image/png,image/jpg">
                                    <small class="text-muted">Max size: 5MB. Formats: JPEG, PNG, JPG</small>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> The default password for new users is <code>spup@2024</code>. 
                            Users should change their password after first login.
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('dean.manage-users') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
