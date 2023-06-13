@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Request for doctors</h1>

        @if (count($requests) > 0)
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Doctor</th>
                        <th>Status</th>
                        <th>Detail</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requests as $request)
                        <tr>
                            <td>{{ $request->doctor->user->name }}</td>
                            <td>{{ $request->status }}</td>
                            <td>
                                @if ($request->file_path === null && $request->file_delete === null)
                                    Request to add you to the patient list
                                @elseif($request->file_delete === null)
                                    Request to add the file "{{ $request->file_name }}" in your record
                                @else
                                    Request to remove the file "{{ $request->file_name }}" from your record
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('process_consent_request') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="doctor_id" value="{{ $request->doctor_id }}">
                                    <input type="hidden" name="action" value="rejected">

                                    <button type="submit" class="btn btn-danger">Refuse</button>
                                </form>

                                <form action="{{ route('process_consent_request') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="doctor_id" value="{{ $request->doctor_id }}">
                                    <input type="hidden" name="action" value="accepted">
                                    @if ($request->file_path !== null)
                                        <input type="hidden" name="file_name" value="{{ $request->file_name }}">
                                    @elseif($request->file_delete !== null)
                                        <input type="hidden" name="file_delete" value="{{ $request->file_delete }}">
                                    @endif
                                    <button type="submit" class="btn btn-success">Accept</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No pending consent requests.</p>
        @endif
    </div>
@endsection
