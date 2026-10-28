@extends('layouts.app')
@section('title', 'Waiting-area board')
@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Waiting-area board</h1>
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    <p>Open the secret link on the waiting-area screen. It shows today's ticket numbers and room codes. Tickets start at 1 each clinic day and keep their number when called or moved in the queue. Priority patients are called first.</p>
    <p>Only numeric room codes, such as 12 or 203A, appear publicly. Other room labels appear as “Clinic room”. Keep the link with clinic staff; anyone holding it can view the board.</p>
    @if($token)
        <div class="card mb-4"><div class="card-body">
            <a class="btn btn-primary" href="{{ route('queue-board.show', $token) }}" target="_blank" rel="noopener noreferrer">Open waiting-area board</a>
            <label class="form-label d-block mt-3" for="board-link">Secret board link</label>
            <input class="form-control" id="board-link" readonly value="{{ route('queue-board.show', $token) }}">
        </div></div>
    @else
        <p>No board link has been generated yet.</p>
    @endif
    <form method="post" action="{{ route('queue-board.rotate') }}">
        @csrf
        <p>Generating a new link immediately revokes the previous link. Open the new link on the waiting-area screen afterward.</p>
        <button class="btn btn-warning" type="submit">Generate new board link</button>
        <a class="btn btn-secondary" href="{{ getRouteByRole('patient-queue.index') }}">Back to queue</a>
    </form>
</div>
@endsection
