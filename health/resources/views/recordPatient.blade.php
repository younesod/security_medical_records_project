@extends('layouts.app')

@section('content')
   
        <div class="container ">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card">
                    @if (session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                            @endif
                        <div class="card-header">{{ __('Mon dossier medical') }}</div>
                        <table class="table table-bordered table-hover">
                                        <tbody>
                                        @foreach ($record as $records)
                                                <tr>
                                                    <td> {{$records->name}} </td>
                                                    <td>
                                                    <form action="{{ route('patient_delete_file')}}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <input type="hidden" name="fileId" value="{{ $records->id }}">
                                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash-o"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                        <form action="{{route('patient_create_file')}}" method ="post" >
                        @csrf
                        @method('POST')
                       <input type="file" name="fileName"> 
                       <input type="submit" value="Upload" class="btn btn-primary">
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
 
    
    
@endsection
