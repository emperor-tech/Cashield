@extends('layouts.app')

@section('content')
<h1 class="text-2xl mb-4">Report Details</h1>
<div class="bg-white p-6 rounded shadow mb-6">
    <div class="mb-2"><b>Campus:</b> {{ $report->campus }}</div>
    <div class="mb-2"><b>Location:</b> {{ $report->location }}</div>
    <div class="mb-2"><b>Severity:</b> <span class="capitalize @if($report->severity=='high')text-red-600 @elseif($report->severity=='medium')text-yellow-600 @else text-green-600 @endif">{{ $report->severity }}</span></div>
    <div class="mb-2"><b>Status:</b> <span class="capitalize">{{ $report->status }}</span></div>
    <div class="mb-2"><b>Reported by:</b> @if($report->user){{ $report->user->name }}@else Guest @endif</div>
    <div class="mb-2"><b>Date:</b> {{ $report->created_at->format('M d, Y H:i') }}</div>
    <div class="mb-2"><b>Description:</b> {{ $report->description }}</div>
    @if($report->media_path)
        <div class="mb-2"><b>Media:</b> <a href="{{ asset('storage/'.$report->media_path) }}" target="_blank" class="text-blue-600 underline">View</a></div>
    @endif
</div>
<h2 class="text-lg font-semibold mb-2">Comments & Follow-up</h2>
<div class="bg-white p-4 rounded shadow mb-4">
    @forelse($report->comments as $c)
        <div class="mb-2 border-b pb-2">
            <div class="text-sm text-gray-600">@if($c->user){{ $c->user->name }}@else Admin @endif - {{ $c->created_at->format('M d, Y H:i') }}</div>
            <div>{{ $c->comment }}</div>
        </div>
    @empty
        <div class="text-gray-500">No comments yet.</div>
    @endforelse
</div>
<form method="POST" action="/admin/report/{{ $report->id }}/comment" class="bg-white p-4 rounded shadow">
    @csrf
    <label class="block mb-2 font-semibold">Add Comment/Follow-up</label>
    <textarea name="comment" class="w-full border p-2 mb-2" rows="3" required></textarea>
    <button type="submit" class="bg-blue-800 text-white px-4 py-2 rounded">Add Comment</button>
</form>
<a href="/admin" class="inline-block mt-4 text-blue-700 underline">&larr; Back to Dashboard</a>
@endsection 