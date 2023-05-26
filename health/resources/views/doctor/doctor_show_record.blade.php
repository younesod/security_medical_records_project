@extends('layouts.app')

@section('content')
        <div class="container ">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">{{ __('List of patient records') }}</div>

                        <div class="card-body">
                                <ul class="list-group">
                     
                                             @foreach ($patients as $patient)
                                                <form action="{{ route('doctor.dossierFile')}}" method ="POST" >
                                                @csrf
                                                @method('POST') 
                                                <input type="hidden" name="patient_id" value="{{ $patient->user_id }}">
                                                <button type="submit" class="btn btn-link" style="padding: 0; border: none; background: none;">
                                                Record of {{ $patient->name }}
                                                </button>
                                                </form>
                                              @endforeach
                               
                                        </ul>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
</div>
</div>
@endsection
