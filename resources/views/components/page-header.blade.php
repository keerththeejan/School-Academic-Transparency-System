@props(['title', 'action' => null, 'actionLabel' => null])
<div class="d-flex justify-content-between align-items-center gap-3 mb-3">
    <h1 class="h3 mb-0">{{ $title }}</h1>
    @if ($action)
        <a class="btn btn-sats" href="{{ $action }}">{{ $actionLabel ?? __('ui.create') }}</a>
    @endif
</div>
