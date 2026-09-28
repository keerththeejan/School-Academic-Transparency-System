@extends('layouts.portal')
@section('title', __('ui.report_discrepancy'))
@section('content')
<h1 class="h4">{{ __('ui.report_discrepancy') }}</h1>
<p>{{ __('ui.discrepancy_prompt') }}</p>
<form method="POST" action="{{ route('parent.discrepancy.store') }}" class="panel">
    @csrf
    <div class="mb-3">
        <label class="form-label" for="student_id">{{ __('ui.select_child') }}</label>
        <select class="form-select" id="student_id" name="student_id" required>
            @foreach ($children as $child)
                <option value="{{ $child->id }}" @selected(old('student_id', $selectedStudent) == $child->id)>{{ $child->full_name }} · {{ $child->schoolClass?->label() }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label" for="date">{{ __('ui.date') }}</label>
        <input class="form-control" id="date" type="date" name="date" value="{{ old('date', $selectedDate) }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="period">{{ __('ui.select_period') }}</label>
        <input class="form-control" id="period" type="number" min="1" max="12" name="period" value="{{ old('period') }}">
    </div>
    <div class="mb-3">
        <label class="form-label" for="description">{{ __('ui.description') }}</label>
        <textarea class="form-control" id="description" name="description" rows="4" required>{{ old('description') }}</textarea>
    </div>
    <button class="btn btn-sats" type="submit">{{ __('ui.submit') }}</button>
</form>
@endsection
