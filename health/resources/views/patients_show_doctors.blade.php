@extends('layouts.app')

@section('content')
    @if (count($doctors) > 0)
        <div class="container ">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">{{ __('Liste des docteurs') }}</div>

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
                            @if (session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="container mt-4 col-md-12">
            <div class="alert alert-danger">
                <p>Aucun médecin disponible</p>
            </div>
        </div>
    @endif
@endsection
