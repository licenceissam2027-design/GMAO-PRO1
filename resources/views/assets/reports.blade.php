@extends('layouts.app')
@section('content')
<div class="card p-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">{{ __('gmao.assets.reports_list') }}</h5>
        <a class="action-icon" href="{{ route('assets.reports.create') }}"><i class="bi bi-file-earmark-plus"></i><span>{{ __('gmao.common.add') }}</span></a>
    </div>

    <form method="GET" class="row g-2 mb-2" id="reportFiltersForm">
        <div class="col-md-2">
            <select class="form-select form-select-sm" name="sector">
                <option value="">{{ __('gmao.common.all_sectors') }}</option>
                @foreach($sectors as $sector)
                    <option value="{{ $sector }}" @selected($selectedSector === $sector)>{{ __('gmao.enum.sector.'.$sector) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-select form-select-sm" name="context_type" id="filter_context_type">
                <option value="">{{ __('gmao.reports.context_type') }}</option>
                @foreach($contextTypes as $type)
                    <option value="{{ $type }}" @selected($selectedContextType === $type)>{{ __('gmao.reports.context.'.$type) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <select class="form-select form-select-sm" name="context_id" id="filter_context_id">
                <option value="">{{ __('gmao.reports.context_item') }}</option>
            </select>
        </div>
        <div class="col-md-2"><input class="form-control form-control-sm" type="date" name="date_from" value="{{ $selectedDateFrom }}"></div>
        <div class="col-md-2"><input class="form-control form-control-sm" type="date" name="date_to" value="{{ $selectedDateTo }}"></div>
        <div class="col-md-12 d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary">{{ __('gmao.common.search') }}</button>
            <a class="btn btn-sm btn-outline-danger" href="{{ route('assets.reports') }}">{{ __('gmao.common.clear_filters') }}</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead>
            <tr>
                <th>{{ __('gmao.common.title') }}</th>
                <th>{{ __('gmao.common.type') }}</th>
                <th>{{ __('gmao.common.format') }}</th>
                <th>{{ __('gmao.common.date') }}</th>
                <th>{{ __('gmao.reports.context_type') }}</th>
                <th>{{ __('gmao.reports.context_item') }}</th>
                <th>{{ __('gmao.common.file') }}</th>
                <th>{{ __('gmao.common.actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($items as $i)
                <tr>
                    <td>{{ $i->title }}</td>
                    <td>{{ __('gmao.enum.type.'.$i->type) }}</td>
                    <td>{{ __('gmao.enum.type.'.$i->format) }}</td>
                    <td>{{ $i->report_date }}</td>
                    <td>{{ $i->context_type ? __('gmao.reports.context.'.$i->context_type) : '-' }}</td>
                    <td>{{ $i->context_label ?: '-' }}</td>
                    <td>@if($i->file_path)<a href="{{ asset('storage/'.$i->file_path) }}" target="_blank">{{ __('gmao.common.open') }}</a>@else - @endif</td>
                    <td>
                        @can('update', $i)
                        <details>
                            <summary class="btn btn-sm btn-outline-primary">{{ __('gmao.common.edit') }}</summary>
                            <form method="POST" action="{{ route('assets.reports.update', $i) }}" enctype="multipart/form-data" class="vstack gap-1 mt-1 report-edit-form">
                                @csrf
                                @method('PATCH')
                                <input class="form-control form-control-sm" name="title" value="{{ $i->title }}" required>
                                <select class="form-select form-select-sm" name="type">@foreach(['daily','weekly','monthly','yearly','custom'] as $t)<option value="{{ $t }}" @selected($i->type === $t)>{{ __('gmao.enum.type.'.$t) }}</option>@endforeach</select>
                                <select class="form-select form-select-sm" name="format">@foreach(['excel','word','pdf'] as $f)<option value="{{ $f }}" @selected($i->format === $f)>{{ __('gmao.enum.type.'.$f) }}</option>@endforeach</select>
                                <input class="form-control form-control-sm" type="date" name="report_date" value="{{ $i->report_date }}" required>

                                <select class="form-select form-select-sm report-context-type" name="context_type" required>
                                    <option value="">{{ __('gmao.reports.context_type') }}</option>
                                    @foreach($contextTypes as $type)
                                        <option value="{{ $type }}" @selected($i->context_type === $type)>{{ __('gmao.reports.context.'.$type) }}</option>
                                    @endforeach
                                </select>
                                <select class="form-select form-select-sm report-context-id" name="context_id" data-selected="{{ $i->context_id }}" required>
                                    <option value="">{{ __('gmao.reports.context_item') }}</option>
                                </select>

                                <input class="form-control form-control-sm" type="file" name="report_file">
                                <button class="btn btn-sm btn-primary">{{ __('gmao.common.update') }}</button>
                            </form>
                        </details>
                        @endcan
                        @can('delete', $i)
                        <form method="POST" action="{{ route('assets.reports.destroy', $i) }}" class="mt-1" onsubmit="return confirm('{{ __('gmao.common.confirm_delete') }}')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">{{ __('gmao.common.delete') }}</button>
                        </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center">{{ __('gmao.common.none') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $items->links() }}
</div>
@endsection

@push('scripts')
<script>
const reportContextOptions = @json($contextOptions);
const selectedFilterContextId = @json($selectedContextId);
const filterContextType = document.getElementById('filter_context_type');
const filterContextId = document.getElementById('filter_context_id');

function renderContextItemsForForm(typeSelect, itemSelect, selected = '') {
    const type = typeSelect?.value || '';
    const list = reportContextOptions[type] || [];
    itemSelect.innerHTML = '';

    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = @json(__('gmao.reports.context_item'));
    itemSelect.appendChild(placeholder);

    list.forEach((item) => {
        const opt = document.createElement('option');
        opt.value = String(item.id);
        opt.textContent = item.label;
        if (selected && String(selected) === String(item.id)) {
            opt.selected = true;
        }
        itemSelect.appendChild(opt);
    });
}

if (filterContextType && filterContextId) {
    renderContextItemsForForm(filterContextType, filterContextId, selectedFilterContextId);
    filterContextType.addEventListener('change', () => renderContextItemsForForm(filterContextType, filterContextId));
}

document.querySelectorAll('.report-edit-form').forEach((form) => {
    const typeSelect = form.querySelector('.report-context-type');
    const itemSelect = form.querySelector('.report-context-id');
    const selected = itemSelect?.dataset?.selected || '';
    renderContextItemsForForm(typeSelect, itemSelect, selected);
    typeSelect?.addEventListener('change', () => renderContextItemsForForm(typeSelect, itemSelect));
});
</script>
@endpush
