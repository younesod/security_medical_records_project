@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>List of your patients</h1>
        @if (count($doctorPatients->patients) > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($doctorPatients->patients as $patient)
                        <tr>
                            <td>{{ $patient->user->name }}</td>
                            <td>{{ $patient->user->email }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="container mt-4 col-md-12">
                <div class="alert alert-danger">
                    <p>You do not have any patients</p>
                </div>
            </div>
        @endif
    </div>
@endsection
