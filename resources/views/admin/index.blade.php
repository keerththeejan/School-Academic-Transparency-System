@extends('layouts.app')
@section('title', $title)
@section('content')
<x-page-header :title="$title" :action="$createUrl ?? null" />
<form class="panel row g-2 align-items-end mb-3" method="GET">
    <div class="col-md-6">
        <label class="form-label" for="q">{{ __('ui.search') }}</label>
        <input id="q" name="q" value="{{ request('q') }}" class="form-control">
    </div>
    <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-sats" type="submit">{{ __('ui.filter') }}</button>
        <a class="btn btn-outline-secondary" href="{{ url()->current() }}">{{ __('ui.clear') }}</a>
    </div>
</form>
<div class="panel">
    @if ($records->isEmpty())
        <x-empty-state />
    @else
        <div class="table-responsive">
            <table class="table align-middle">
                <caption class="visually-hidden">{{ $title }}</caption>
                <thead>
                    <tr>
                        @foreach ($columns as $column)
                            <th scope="col">{{ $column['label'] }}</th>
                        @endforeach
                        <th scope="col">{{ __('ui.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($records as $record)
                        <tr>
                            @foreach ($columns as $column)
                                <td>
                                    @php $value = data_get($record, $column['key']); @endphp
                                    {{ !empty($column['trans']) && $value ? __('ui.'.$value) : $value }}
                                </td>
                            @endforeach
                            <td>
                                @if (!empty($showRoute))
                                    <a href="{{ route($showRoute, $record) }}">{{ __('ui.view') }}</a>
                                @endif
                                @if (!empty($editRoute))
                                    <a href="{{ route($editRoute, $record) }}">{{ __('ui.edit') }}</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $records->links() }}
    @endif
</div>
@endsection
