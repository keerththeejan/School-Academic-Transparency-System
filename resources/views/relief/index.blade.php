@extends('layouts.app')
@section('title', __('ui.relief'))
@section('content')
<x-page-header :title="__('ui.relief')" />
<div class="panel">
    @forelse ($records as $relief)
        <form class="row g-2 align-items-center border-bottom py-2" method="POST" action="{{ route('relief.update', $relief) }}">
            @csrf
            @method('PUT')
            <div class="col-md-4">{{ $relief->date?->toDateString() }} · {{ $relief->schoolClass?->label() }} · {{ __('ui.period') }} {{ $relief->period }}</div>
            <div class="col-md-4">
                <select class="form-select" name="relief_teacher_id">
                    <option value="">{{ __('ui.not_covered') }}</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected($relief->relief_teacher_id == $teacher->id)>{{ $teacher->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-sm btn-sats" type="submit">{{ __('ui.assign') }}</button></div>
        </form>
    @empty
        <x-empty-state />
    @endforelse
</div>
{{ $records->links() }}
@endsection
