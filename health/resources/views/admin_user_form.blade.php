@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-10">
                        <div class="card">
                            <div class="card-header">{{ __('List of users') }}</div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Role</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($users as $user)
                                                <tr>
                                                    <td>{{ $user->name }}</td>
                                                    <td>{{ $user->role }}</td>
                                                    <td>
                                                        <form action="{{ route('admin_delete_user')}}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <input type="hidden" name="user_email" value="{{ $user->email }}">
                                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash-o"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Assign a role') }}</div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('admin_change_role') }}">
                            @csrf

                            <div class="form-group row">
                                <label for="user_id"
                                    class="col-md-4 col-form-label text-md-right">{{ __('User') }}</label>

                                <div class="col-md-6">
                                    <select id="user_id" name="user_id"
                                        class="form-control @error('user_id') is-invalid @enderror" required>
                                        <option value="">Select a user</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}"
                                                @if (old('user_id') == $user->id) selected @endif>{{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('user_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="role"
                                    class="col-md-4 col-form-label text-md-right">{{ __('Rôle') }}</label>

                                <div class="col-md-6">
                                    <select id="role" name="role"
                                        class="form-control @error('role') is-invalid @enderror" required>
                                        <option value="">Select a role</option>
                                        <option value='patient'>Patient</option>
                                        <option value="doctor" @if (old('role') == 'doctor') selected @endif>Doctor
                                        </option>
                                        <option value="admin">Admin</option>
                                    </select>

                                    @error('role')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Register') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
