@extends(auth()->user()->hasRole('parent') ? 'layouts.portal' : 'layouts.app')
@section('title', __('ui.profile'))
@section('content')
<h1 class="h4">{{ __('ui.profile') }}</h1>
<form class="panel" method="POST" action="{{ route('profile.update') }}">
    @csrf
    @method('PUT')
    <div class="mb-3"><label class="form-label" for="name">{{ __('ui.name') }}</label><input class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required></div>
    <div class="mb-3"><label class="form-label" for="mobile">{{ __('ui.mobile') }}</label><input class="form-control" id="mobile" name="mobile" value="{{ old('mobile', $user->mobile) }}"></div>
    <div class="mb-3"><label class="form-label" for="locale">{{ __('ui.language') }}</label>
        <select class="form-select" id="locale" name="locale">
            @foreach (['en' => __('ui.english'), 'ta' => __('ui.tamil'), 'si' => __('ui.sinhala')] as $code => $label)
                <option value="{{ $code }}" @selected(old('locale', $user->locale) === $code)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-3"><label class="form-label" for="current_password">{{ __('ui.current_password') }}</label><input class="form-control" id="current_password" name="current_password" type="password" autocomplete="current-password"></div>
    <div class="mb-3"><label class="form-label" for="password">{{ __('ui.new_password') }}</label><input class="form-control" id="password" name="password" type="password" autocomplete="new-password"></div>
    <div class="mb-3"><label class="form-label" for="password_confirmation">{{ __('ui.password_confirmation') }}</label><input class="form-control" id="password_confirmation" name="password_confirmation" type="password"></div>
    <button class="btn btn-sats" type="submit">{{ __('ui.save') }}</button>
</form>
@endsection
