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
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($doctorPatients->patients as $patient)
                        <tr>
                            <td>{{ $patient->user->name }}</td>
                            <td>{{ $patient->user->email }}</td>
                            <td>
                                <form action="{{ route('remove_patient') }}" method="POST">
                                    @csrf
                                    @method('delete')
                                    <input type="hidden" name="patient_id" value="{{ $patient->patient_id }}">
                                    <button type="submit" class="btn btn-danger">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="container mt-4 col-md-12">
                <div class="alert alert-info">
                    <p>You do not have any patients.</p>
                </div>
            </div>
        @endif
    </div>
    <div class="container">
        <h1>Add a patient</h1>
        <form action="{{ route('request_add_patient') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="patient_id">Select a patient</label>
                <select class="form-control" id="patient_id" name="patient_id">
                    @foreach ($patients as $patient)
                        <option value="{{ $patient->patient_id }}">{{ $patient->user->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Patient</button>
        </form>
    </div>
@endsection
