@extends('layouts.app')

@section('content')
    <div class="container ">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">


                    <div class="card-header">{{ __('Dossier') }}</div>
                    @foreach ($files as $file)
                        @if ($files->isEmpty())
                            No files are available

                            <form action="{{ route('doctor_add_file') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                @method('post')
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
                                    <td> {{ $file->name }} </td>
                                    <td>
                                        <form action="{{ route('doctor_delete_file') }}" method="post">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="fileId" value="{{ $file->id }}">
                                            <input type="hidden" name="patientId" value="{{ $file->user_id }}">
                                            <button type="submit" class="btn btn-danger btn-sm"><i
                                                    class="fa fa-trash-o"></i></button>
                                        </form>
                                        <form action="{{ route('doctor_download') }}" method="post">
                                            @csrf
                                            @method('post')
                                            <input type="hidden" name="fileId" value="{{ $file->id }}">
                                            <input type="hidden" name="patientId" value="{{ $file->user_id }}">
                                            <button type="submit" class="btn btn-primary ">Download</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <form action="{{ route('doctor_add_file')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        @method('post')
                        <input type="file" name="file">
                        <input type="hidden" name="id" value="{{ session('patient_id') }}">
                        <input type="submit" value="Upload" class="btn btn-primary">
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
