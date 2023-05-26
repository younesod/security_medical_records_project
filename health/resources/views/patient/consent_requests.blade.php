@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Request for doctors</h1>

        @if (count($requests)>0)
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Doctor</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requests as $request)
                            <tr>
                                <td>{{$request->doctor->user->name }}</td>
                                <td>{{$request->status}}</td>
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
