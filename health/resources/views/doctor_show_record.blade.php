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
                                              <li class="list-group-item list-group-item-action"> <a href="{{ route('doctor.dossierFile', ['id' => $patient->user_id]) }}">Record of {{ $patient->name }}</a>
                                                </li>
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
