@extends('layouts.app')

@section('content')
   
        <div class="container ">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card">
                  
                   
                     <div class="card-header">{{ __('Dossier') }}</div>
                        @foreach ($files as $file)
                        @if($files->isEmpty())
                        Aucun fichier n'est disponible pour le moment


                        <form action="{{route('doctor_add_file',['id' => $id])}}" method ="post" >
                        @csrf
                        @method('POST')
                       <input type="file" name="fileName"> 
                       <input type="hidden" name="id" value="{{ $id }}">
                       <input type="submit" value="Upload" class="btn btn-primary">
                        </form>
                        @endif
                        @endforeach
                        
                                <table class="table table-bordered table-hover">
                                        <tbody>
                                        @foreach ($files as $file)
                                                <tr>
                                                    <td> {{$file->name}} </td>
                                                    <td>
                                                    <form action="{{ route('doctor_delete_file',['id' => $file->user_id])}}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <input type="hidden" name="fileId" value="{{ $file->id }}">
                                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash-o"></i></button>
                                                        </form>
                                                        <a href="{{ route('doctor_download', ['id' => $file->id]) }}" class="btn btn-primary " >Télécharger</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                        <form action="{{route('doctor_add_file', ['id' => request()->route('id')])}}" method ="post" enctype="multipart/form-data" >
                        @csrf
                        @method('POST')
                       <input type="file" name="file"> 
                       <input type="hidden" name="id" value="{{  request()->route('id') }}">
                       <input type="submit" value="Upload" class="btn btn-primary">
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
 
    
    
@endsection