@extends('layouts.app')
@section('title', __('ui.search'))
@section('content')
<x-page-header :title="__('ui.search')" />
<form class="panel mb-3" method="GET">
    <label class="form-label" for="q">{{ __('ui.search_prompt') }}</label>
    <div class="d-flex gap-2">
        <input id="q" class="form-control" name="q" value="{{ $term }}">
        <button class="btn btn-sats" type="submit">{{ __('ui.search') }}</button>
    </div>
</form>
@foreach ($groups as $label => $rows)
    <section class="panel mb-3">
        <h2 class="h6">{{ $label }}</h2>
        @forelse ($rows as $row)
            <div class="border-bottom py-2">{{ $row }}</div>
        @empty
            <p class="text-secondary mb-0">{{ __('ui.no_results') }}</p>
        @endforelse
    </section>
@endforeach
@endsection
