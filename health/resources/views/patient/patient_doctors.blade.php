@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>List of your doctors</h1>
        @if (count($doctorsPatient) > 0)
            <ul class="list-group">
                @foreach ($doctorsPatient as $doctor)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Dr. {{ $doctor->user->name }}
                        <form action="{{ route('patient_remove_doctor') }}" method="post">
                            @csrf
                            @method('delete')
                            <input type="hidden" name="doctor_id" value="{{ $doctor->doctor_id }}">
                            <input type="hidden" name="patient_id" value="{{ $doctor->patient_id }}">
                            <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="container mt-4 col-md-12">
                <div class="alert alert-info">
                    <p>You do not have any doctors.</p>
                </div>
                <div class="alert alert-success">
                    <p>Suggest :
                        @if (Auth::check() && Auth::user()->role === 'patient')
                            <a class="btn btn-success" href="{{ route('showDoctors') }}">Add a doctor</a>
                        @endif
                    </p>
                </div>
            </div>
        @endif
        <div class="container mt-4 col-md-12">
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
@endsection
