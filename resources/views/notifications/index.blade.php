@extends('layouts.app')
@section('title', __('ui.notifications'))
@section('content')
<x-page-header :title="__('ui.notifications')" />
<div class="panel">
    @forelse ($notes as $note)
        <article class="border-bottom py-3">
            <strong>{{ $note->title }}</strong>
            <p class="mb-1">{{ $note->body }}</p>
            <div class="small text-secondary">{{ $note->created_at }}</div>
        </article>
    @empty
        <x-empty-state />
    @endforelse
</div>
{{ $notes->links() }}
@if (!empty($logs))
    <h2 class="h5 mt-4">{{ __('ui.channel') }}</h2>
    <div class="panel table-responsive">
        <table class="table"><thead><tr><th>{{ __('ui.channel') }}</th><th>{{ __('ui.status') }}</th><th>{{ __('ui.message') }}</th></tr></thead>
        <tbody>@foreach($logs as $log)<tr><td>{{ $log->channel }}</td><td>{{ $log->status }}</td><td>{{ \Illuminate\Support\Str::limit($log->message, 120) }}</td></tr>@endforeach</tbody></table>
        {{ $logs->links() }}
    </div>
@endif
@endsection
