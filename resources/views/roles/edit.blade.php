@extends('layouts.app')
@section('content')
<x-page-header :title="$role->name" />
<form class="panel" method="POST" action="{{ route('roles.update', $role) }}">
    @csrf
    @method('PUT')
    @foreach ($permissions->groupBy('group') as $group => $items)
        <h2 class="h6 mt-3">{{ $group }}</h2>
        @foreach ($items as $permission)
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="perm-{{ $permission->id }}" @checked($role->permissions->contains('id', $permission->id))>
                <label class="form-check-label" for="perm-{{ $permission->id }}">{{ $permission->name }}</label>
            </div>
        @endforeach
    @endforeach
    <button class="btn btn-sats mt-3" type="submit">{{ __('ui.save') }}</button>
</form>
@endsection
