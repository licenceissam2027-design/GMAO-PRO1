@extends('layouts.app')
@section('content')
<div class="card p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">{{ __('gmao.maintenance.rounds') }}</h5>
        <div class="d-flex gap-2">
            <span class="badge text-bg-primary">{{ __('gmao.maintenance.round_total') }}: {{ $totalRounds }}</span>
            <span class="badge text-bg-success">{{ __('gmao.maintenance.round_completed') }}: {{ $completedRounds }}</span>
            <span class="badge text-bg-warning">{{ __('gmao.maintenance.round_pending') }}: {{ $pendingRounds }}</span>
        </div>
    </div>

    @forelse($roundTasks as $task)
        <div class="border rounded p-2 mb-2">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <strong>{{ $task->title }}</strong>
                    <div class="small text-muted">
                        {{ __('gmao.common.reference') }}: {{ $task->preventivePlan?->asset_reference ?: '-' }} |
                        {{ __('gmao.maintenance.domain') }}: {{ $task->preventivePlan?->maintenance_domain ? ($domainLabels[$task->preventivePlan->maintenance_domain] ?? $task->preventivePlan->maintenance_domain) : '-' }} |
                        {{ __('gmao.maintenance.failure_mode') }}: {{ $task->preventivePlan?->failure_mode ? ($failureModeLabels[$task->preventivePlan->failure_mode] ?? $task->preventivePlan->failure_mode) : '-' }}
                    </div>
                </div>
                <span class="badge text-bg-secondary">{{ __('gmao.enum.status.'.$task->status) }}</span>
            </div>

            <form method="POST" action="{{ route('maintenance.tasks.execution', $task) }}" class="row g-2 round-form">@csrf @method('PATCH')
                @php
                    $planChecklistLines = collect(preg_split('/\r\n|\r|\n/', (string) ($task->preventivePlan?->checklist ?? '')))
                        ->map(fn ($line) => trim(preg_replace('/^\d+[\)\.\-:\s]*/', '', $line)))
                        ->filter()
                        ->values();
                    $savedChecks = collect($task->execution_checks ?? []);
                @endphp

                @if($planChecklistLines->isNotEmpty())
                    <div class="col-12">
                        <div class="small fw-semibold mb-1">{{ __('gmao.maintenance.template_checklist') }}</div>
                        <div class="row g-2">
                            @foreach($planChecklistLines as $i => $line)
                                @php
                                    $saved = $savedChecks->first(fn ($c) => (($c['label'] ?? '') === $line)) ?? [];
                                    $isDone = (bool) ($saved['done'] ?? false);
                                    $note = (string) ($saved['note'] ?? '');
                                @endphp
                                <div class="col-md-6 border rounded p-2">
                                    <input type="hidden" name="execution_checks[{{ $i }}][label]" value="{{ $line }}">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="execution_checks[{{ $i }}][done]" value="1" @checked($isDone)>
                                        <label class="form-check-label">{{ $line }}</label>
                                    </div>
                                    <input class="form-control form-control-sm mt-1" name="execution_checks[{{ $i }}][note]" value="{{ $note }}" placeholder="{{ __('gmao.maintenance.execution_note') }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="col-md-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="did_lubrication" value="1" @checked($task->did_lubrication)><label class="form-check-label">{{ __('gmao.maintenance.did_lubrication') }}</label></div></div>
                <div class="col-md-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="did_measurement" value="1" @checked($task->did_measurement)><label class="form-check-label">{{ __('gmao.maintenance.did_measurement') }}</label></div></div>
                <div class="col-md-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="did_inspection" value="1" @checked($task->did_inspection)><label class="form-check-label">{{ __('gmao.maintenance.did_inspection') }}</label></div></div>
                <div class="col-md-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="did_replacement" value="1" @checked($task->did_replacement)><label class="form-check-label">{{ __('gmao.maintenance.did_replacement') }}</label></div></div>
                <div class="col-md-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="did_cleaning" value="1" @checked($task->did_cleaning)><label class="form-check-label">{{ __('gmao.maintenance.did_cleaning') }}</label></div></div>
                <div class="col-md-2"><div class="form-check"><input class="form-check-input anomaly-toggle" type="checkbox" name="anomaly_detected" value="1" @checked($task->anomaly_detected)><label class="form-check-label text-danger">{{ __('gmao.maintenance.anomaly_detected') }}</label></div></div>

                <div class="col-md-3"><input class="form-control form-control-sm" name="measurement_reading" value="{{ $task->measurement_reading }}" placeholder="{{ __('gmao.maintenance.measurement_reading') }}"></div>
                <div class="col-md-3"><input class="form-control form-control-sm" name="inspection_location" value="{{ $task->inspection_location }}" placeholder="{{ __('gmao.common.location') }}"></div>
                <div class="col-md-2"><input class="form-control form-control-sm" type="number" min="0" max="24" step="0.25" name="actual_hours" value="{{ $task->actual_hours }}" placeholder="{{ __('gmao.maintenance.actual_hours') }}"></div>
                <div class="col-md-4"><select class="form-select form-select-sm" name="execution_status" required><option value="in_progress" @selected($task->status === 'in_progress')>{{ __('gmao.enum.status.in_progress') }}</option><option value="completed" @selected($task->status === 'completed')>{{ __('gmao.enum.status.completed') }}</option><option value="stopped" @selected($task->status === 'stopped')>{{ __('gmao.enum.status.stopped') }}</option></select></div>

                <div class="col-md-6"><textarea class="form-control form-control-sm" rows="2" name="execution_note" placeholder="{{ __('gmao.maintenance.execution_note') }}">{{ $task->execution_note }}</textarea></div>
                <div class="col-md-6"><textarea class="form-control form-control-sm anomaly-note" rows="2" name="anomaly_note" placeholder="{{ __('gmao.maintenance.anomaly_note') }}">{{ $task->anomaly_note }}</textarea></div>

                <div class="col-md-4 anomaly-request-wrap">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="create_request_on_anomaly" value="1">
                        <label class="form-check-label">{{ __('gmao.maintenance.create_request_on_anomaly') }}</label>
                    </div>
                </div>
                <div class="col-md-8 text-end">
                    <button class="btn btn-sm btn-primary">{{ __('gmao.common.save') }}</button>
                </div>
            </form>
        </div>
    @empty
        <div class="text-center text-muted py-4">{{ __('gmao.common.none') }}</div>
    @endforelse

    {{ $roundTasks->links() }}
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.round-form').forEach((form) => {
    const anomalyToggle = form.querySelector('.anomaly-toggle');
    const anomalyNote = form.querySelector('.anomaly-note');
    const requestWrap = form.querySelector('.anomaly-request-wrap');

    function sync() {
        const active = !!anomalyToggle?.checked;
        if (anomalyNote) anomalyNote.required = active;
        if (requestWrap) requestWrap.classList.toggle('d-none', !active);
    }

    anomalyToggle?.addEventListener('change', sync);
    sync();
});
</script>
@endpush
