<div class="mb-3">
    <label class="form-label" for="deviation_type">{{ __('ui.type') }}</label>
    <select class="form-select" id="deviation_type" name="deviation_type" required>
        @foreach ($types as $group => $keys)
            <optgroup label="{{ __('ui.deviation_groups.'.$group) }}">
                @foreach ($keys as $key)
                    <option value="{{ $key }}" @selected(old('deviation_type') === $key)>{{ __('ui.deviation_types.'.$key) }}</option>
                @endforeach
            </optgroup>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label" for="relief_teacher_id">{{ __('ui.relief_teacher') }}</label>
    <select class="form-select" id="relief_teacher_id" name="relief_teacher_id">
        <option value="">{{ __('ui.not_covered') }}</option>
        @foreach ($teachers as $teacher)
            <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label" for="reason">{{ __('ui.reason') }}</label>
    <input class="form-control" id="reason" name="reason" value="{{ old('reason') }}">
</div>
<div class="mb-3">
    <label class="form-label" for="description">{{ __('ui.description') }}</label>
    <textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
</div>
