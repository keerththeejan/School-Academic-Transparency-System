@extends('layouts.app')
@section('title', __('ui.settings'))
@section('content')
<x-page-header :title="__('ui.settings')" />
<p class="text-secondary">{{ __('ui.secrets_note') }}</p>
<form class="panel" method="POST" action="{{ route('settings.update') }}">
    @csrf
    @method('PUT')
    @foreach ($fields as $name => $meta)
        <div class="mb-3">
            <label class="form-label" for="{{ $name }}">{{ $meta['label'] }}</label>
            @if ($meta['type'] === 'bool')
                <select class="form-select" id="{{ $name }}" name="{{ $name }}">
                    <option value="1" @selected($meta['value'])>{{ __('ui.yes') }}</option>
                    <option value="0" @selected(! $meta['value'])>{{ __('ui.no') }}</option>
                </select>
            @else
                <input class="form-control" id="{{ $name }}" name="{{ $name }}" value="{{ $meta['value'] }}">
            @endif
        </div>
    @endforeach
    <button class="btn btn-sats" type="submit">{{ __('ui.save') }}</button>
</form>
@endsection
