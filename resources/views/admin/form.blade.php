@extends('layouts.app')
@section('title', $title)
@section('content')
<x-page-header :title="$title" />
<form class="panel" method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if (($method ?? 'POST') !== 'POST')
        @method($method)
    @endif
    @foreach ($fields as $field)
        <div class="mb-3">
            <label class="form-label" for="{{ $field['name'] }}">{{ $field['label'] }}</label>
            @if (($field['type'] ?? 'text') === 'select')
                <select class="form-select" id="{{ $field['name'] }}" name="{{ $field['name'] }}" @if(!empty($field['required'])) required @endif>
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach ($field['options'] as $value => $label)
                        <option value="{{ $value }}" @selected(old($field['name'], $field['value'] ?? '') == $value)>{{ $label }}</option>
                    @endforeach
                </select>
            @elseif (($field['type'] ?? '') === 'textarea')
                <textarea class="form-control" id="{{ $field['name'] }}" name="{{ $field['name'] }}" rows="4">{{ old($field['name'], $field['value'] ?? '') }}</textarea>
            @elseif (($field['type'] ?? '') === 'checkbox')
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="{{ $field['name'] }}" name="{{ $field['name'] }}" value="1" @checked(old($field['name'], $field['value'] ?? false))>
                    <label class="form-check-label" for="{{ $field['name'] }}">{{ $field['label'] }}</label>
                </div>
            @else
                <input class="form-control" id="{{ $field['name'] }}" name="{{ $field['name'] }}" type="{{ $field['type'] ?? 'text' }}" value="{{ in_array(($field['type'] ?? 'text'), ['password', 'file'], true) ? '' : old($field['name'], $field['value'] ?? '') }}" @if(!empty($field['required'])) required @endif>
            @endif
            @error($field['name'])<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
    @endforeach
    <button class="btn btn-sats" type="submit">{{ __('ui.save') }}</button>
    <a class="btn btn-outline-secondary" href="{{ $back }}">{{ __('ui.cancel') }}</a>
</form>
@endsection
