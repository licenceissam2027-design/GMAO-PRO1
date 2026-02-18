@extends('layouts.app')
@section('content')
<div class="card p-3 form-shell">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">{{ __('gmao.assets.report_new') }}</h5>
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('assets.reports') }}">{{ __('gmao.common.open') }}</a>
    </div>

    <form method="POST" action="{{ route('assets.reports.store') }}" enctype="multipart/form-data" class="row g-2" id="reportCreateForm">
        @csrf
        <div class="col-md-4"><input class="form-control" name="title" value="{{ old('title') }}" placeholder="{{ __('gmao.common.title') }}" required></div>
        <div class="col-md-2"><select class="form-select" name="type">@foreach(['daily','weekly','monthly','yearly','custom'] as $t)<option value="{{ $t }}" @selected(old('type') === $t)>{{ __('gmao.enum.type.'.$t) }}</option>@endforeach</select></div>
        <div class="col-md-2"><select class="form-select" name="format">@foreach(['excel','word','pdf'] as $f)<option value="{{ $f }}" @selected(old('format') === $f)>{{ __('gmao.enum.type.'.$f) }}</option>@endforeach</select></div>
        <div class="col-md-2"><input class="form-control" type="date" name="report_date" value="{{ old('report_date') }}" required></div>
        <div class="col-md-2"><input class="form-control" type="file" name="report_file"></div>

        <div class="col-md-4">
            <select class="form-select" name="context_type" id="context_type" required>
                <option value="">{{ __('gmao.reports.context_type') }}</option>
                @foreach($contextTypes as $type)
                    <option value="{{ $type }}" @selected(old('context_type', $selectedContextType ?? '') === $type)>{{ __('gmao.reports.context.'.$type) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-8">
            <select class="form-select" name="context_id" id="context_id" required>
                <option value="">{{ __('gmao.reports.context_item') }}</option>
            </select>
        </div>

        <div class="col-12"><button class="btn btn-primary">{{ __('gmao.common.save') }}</button></div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const reportContextOptions = @json($contextOptions);
const oldContextId = @json(old('context_id', $selectedContextId ?? ''));
const contextTypeSelect = document.getElementById('context_type');
const contextItemSelect = document.getElementById('context_id');

function renderContextItems(type, selected = '') {
    const list = reportContextOptions[type] || [];
    contextItemSelect.innerHTML = '';
    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = @json(__('gmao.reports.context_item'));
    contextItemSelect.appendChild(placeholder);

    list.forEach((item) => {
        const option = document.createElement('option');
        option.value = String(item.id);
        option.textContent = item.label;
        if (selected && String(selected) === String(item.id)) {
            option.selected = true;
        }
        contextItemSelect.appendChild(option);
    });
}

contextTypeSelect?.addEventListener('change', (e) => renderContextItems(e.target.value));
renderContextItems(contextTypeSelect?.value || '', oldContextId);
</script>
@endpush
