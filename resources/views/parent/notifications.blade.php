@extends('layouts.portal')
@section('title', __('ui.notifications'))
@section('content')
<h1 class="h4">{{ __('ui.notifications') }}</h1>
@forelse ($notes as $note)
    <article class="panel mb-2">
        <strong>{{ $note->title }}</strong>
        <p class="mb-1">{{ $note->body }}</p>
        <div class="small text-secondary">{{ $note->created_at->diffForHumans() }}</div>
    </article>
@empty
    <x-empty-state />
@endforelse
{{ $notes->links() }}
@endsection
