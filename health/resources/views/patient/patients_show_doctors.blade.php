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
            </div>
        @endif
    </div>
    @if (count($doctors) > 0)
        <div class="container ">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">{{ __('Doctors available') }}</div>

                        <div class="card-body">
                            <ul class="list-group">
                                @foreach ($doctors as $doctor)
                                    <li class="list-group-item list-group-item-action">Dr. {{ $doctor->user->name }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mt-4 col-md-12">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">{{ __('Add a doctor') }}</div>

                        <div class="card-body">
                            <form action="{{ route('patient_add_doctor') }}" method="post">
                                @csrf

                                <div class="form-group ">
                                    <label for="doctor_id">Doctor</label>
                                    <select class="form-control mt-2 " id="doctor" name="doctor_id">
                                        @foreach ($doctors as $doctor)
                                            <option type="number" value="{{ $doctor->doctor_id }}">Dr. {{ $doctor->user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary mt-2">{{ __('Add') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="container mt-4 col-md-12">
            <div class="alert alert-danger">
                <p>There is no doctor available</p>
            </div>
        </div>
    @endif
@endsection
