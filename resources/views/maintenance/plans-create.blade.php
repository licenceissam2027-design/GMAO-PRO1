@extends('layouts.app')
@section('content')
<div class="card p-3 form-shell pm-builder">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-1">{{ __('gmao.maintenance.new_plan') }}</h5>
            <div class="text-muted small">{{ __('gmao.maintenance.plan_page_subtitle') }}</div>
        </div>
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('maintenance.plans') }}">{{ __('gmao.common.open') }}</a>
    </div>

    <form method="POST" action="{{ route('maintenance.plans.store') }}" id="preventive-plan-form" class="row g-3">
        @csrf

        <div class="col-lg-8">
            <div class="pm-section-card">
                <div class="pm-section-title">{{ __('gmao.maintenance.plan_scope') }}</div>
                <div class="row g-2">
                    <div class="col-md-6">
                        <input class="form-control" name="title" value="{{ old('title') }}" placeholder="{{ __('gmao.common.title') }}" required>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="sector" id="plan_sector">
                            <option value="">{{ __('gmao.common.sector') }}</option>
                            @foreach($sectors as $sector)
                                <option value="{{ $sector }}" @selected(old('sector') === $sector)>{{ __('gmao.enum.sector.'.$sector) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="asset_type" id="plan_asset_type">
                            @foreach(['industrial','technical','logistic','other'] as $t)
                                <option value="{{ $t }}" @selected(old('asset_type', 'industrial') === $t)>{{ __('gmao.enum.type.'.$t) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control" name="asset_reference" id="plan_asset_reference" value="{{ old('asset_reference') }}" placeholder="{{ __('gmao.common.reference') }}">
                    </div>
                    <div class="col-md-6 plan-asset-group" id="plan_industrial_group">
                        <select class="form-select" name="industrial_machine_id" id="plan_industrial_id">
                            <option value="">{{ __('gmao.assets.industrial_list') }}</option>
                            @foreach($machines as $machine)
                                <option value="{{ $machine->id }}" data-sector="{{ $machine->sector }}" data-code="{{ $machine->code }}" @selected((string) old('industrial_machine_id') === (string) $machine->id)>{{ $machine->code }} - {{ $machine->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 plan-asset-group d-none" id="plan_technical_group">
                        <select class="form-select" name="technical_asset_id" id="plan_technical_id">
                            <option value="">{{ __('gmao.assets.technical_list') }}</option>
                            @foreach($technicalAssets as $asset)
                                <option value="{{ $asset->id }}" data-sector="{{ $asset->sector }}" data-code="{{ $asset->code }}" @selected((string) old('technical_asset_id') === (string) $asset->id)>{{ $asset->code }} - {{ $asset->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 plan-asset-group d-none" id="plan_logistic_group">
                        <select class="form-select" name="logistic_asset_id" id="plan_logistic_id">
                            <option value="">{{ __('gmao.assets.logistics_list') }}</option>
                            @foreach($logisticAssets as $asset)
                                <option value="{{ $asset->id }}" data-sector="{{ $asset->sector }}" data-code="{{ $asset->code }}" @selected((string) old('logistic_asset_id') === (string) $asset->id)>{{ $asset->code }} - {{ $asset->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="pm-section-card mt-3">
                <div class="pm-section-title">{{ __('gmao.maintenance.operational_window') }}</div>
                <div class="row g-2">
                    <div class="col-md-2">
                        <select class="form-select" name="frequency" id="plan_frequency">
                            @foreach(['daily','weekly','monthly','quarterly','yearly'] as $f)
                                <option value="{{ $f }}" @selected(old('frequency', 'monthly') === $f)>{{ __('gmao.enum.type.'.$f) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input class="form-control" type="number" min="1" max="365" name="interval_value" value="{{ old('interval_value', 1) }}" placeholder="{{ __('gmao.maintenance.interval_value') }}" required>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="trigger_mode" id="plan_trigger_mode">
                            @foreach(['calendar','meter','both'] as $mode)
                                <option value="{{ $mode }}" @selected(old('trigger_mode', 'calendar') === $mode)>{{ __('gmao.enum.trigger.'.$mode) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2" id="plan_meter_threshold_wrap">
                        <input class="form-control" type="number" step="0.01" min="0" name="meter_threshold" value="{{ old('meter_threshold') }}" placeholder="{{ __('gmao.maintenance.meter_threshold') }}">
                    </div>
                    <div class="col-md-2">
                        <input class="form-control" type="date" name="next_due_date" value="{{ old('next_due_date') }}" required>
                    </div>
                    <div class="col-md-2">
                        <input class="form-control" type="number" min="5" max="1440" name="estimated_duration_minutes" value="{{ old('estimated_duration_minutes') }}" placeholder="{{ __('gmao.maintenance.estimated_duration') }}">
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" name="skill_level">
                            @foreach(['operator','technician','senior_technician','specialist'] as $skill)
                                <option value="{{ $skill }}" @selected(old('skill_level', 'technician') === $skill)>{{ __('gmao.enum.skill.'.$skill) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" name="responsible_id">
                            <option value="">{{ __('gmao.projects.manager') }}</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" @selected((string) old('responsible_id') === (string) $u->id)>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="requires_shutdown" value="1" @checked(old('requires_shutdown'))>
                            <label class="form-check-label">{{ __('gmao.maintenance.requires_shutdown') }}</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                            <label class="form-check-label">{{ __('gmao.common.active') }}</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pm-section-card mt-3">
                <div class="pm-section-title">{{ __('gmao.maintenance.domain') }}</div>
                <div class="row g-2">
                    <div class="col-md-5">
                        <select class="form-select" name="maintenance_domain" id="plan_domain" required>
                            <option value="">{{ __('gmao.maintenance.domain') }}</option>
                            @foreach($maintenanceDomains as $domain)
                                <option value="{{ $domain }}" @selected(old('maintenance_domain') === $domain)>{{ $domainLabels[$domain] ?? $domain }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <select class="form-select" name="failure_mode" id="plan_failure_mode" required>
                            <option value="">{{ __('gmao.maintenance.failure_mode') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-primary w-100" id="plan_fill_template">{{ __('gmao.maintenance.autofill_template') }}</button>
                    </div>
                </div>
            </div>

            <div class="pm-section-card mt-3">
                <div class="pm-section-title">{{ __('gmao.maintenance.work_package') }}</div>
                <div class="row g-3">
                    <div class="col-md-5">
                        <div class="small text-muted mb-1">{{ __('gmao.maintenance.smart_checklist_builder') }}</div>
                        <div class="pm-check-grid">
                            @foreach(['lubrication','measurement','inspection','replacement','cleaning','tightening','calibration','safety_test'] as $seed)
                                <label class="pm-check-item">
                                    <input class="form-check-input checklist-seed" type="checkbox" value="{{ $seed }}">
                                    <span>{{ __('gmao.maintenance.checklist_seed_'.$seed) }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="small text-muted mt-2">{{ __('gmao.maintenance.seed_hint') }}</div>
                    </div>
                    <div class="col-md-7">
                        <textarea class="form-control" name="checklist" id="plan_checklist" rows="7" required placeholder="{{ __('gmao.maintenance.template_checklist') }}">{{ old('checklist') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <textarea class="form-control" name="procedure_steps" id="plan_procedure_steps" rows="4" required placeholder="{{ __('gmao.maintenance.procedure_steps') }}">{{ old('procedure_steps') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <textarea class="form-control" name="safety_notes" id="plan_safety_notes" rows="4" placeholder="{{ __('gmao.maintenance.safety_notes') }}">{{ old('safety_notes') }}</textarea>
                    </div>
                    <div class="col-12">
                        <textarea class="form-control" name="spare_parts_list" id="plan_spare_parts_list" rows="3" placeholder="{{ __('gmao.maintenance.spare_parts_list') }}">{{ old('spare_parts_list') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="pm-side-card">
                <h6 class="mb-2">{{ __('gmao.maintenance.plan_preview') }}</h6>
                <div class="pm-preview-list">
                    <div><span>{{ __('gmao.common.reference') }}</span><strong id="preview_ref">-</strong></div>
                    <div><span>{{ __('gmao.common.frequency') }}</span><strong id="preview_frequency">-</strong></div>
                    <div><span>{{ __('gmao.maintenance.domain') }}</span><strong id="preview_domain">-</strong></div>
                    <div><span>{{ __('gmao.maintenance.failure_mode') }}</span><strong id="preview_failure">-</strong></div>
                    <div><span>{{ __('gmao.maintenance.estimated_duration') }}</span><strong id="preview_duration">-</strong></div>
                </div>
                <hr>
                <div class="small text-muted">{{ __('gmao.maintenance.plan_quality_hint') }}</div>
            </div>
            <button class="btn btn-primary w-100 mt-3">{{ __('gmao.common.save') }}</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const failureTaxonomy = @json($failureModes);
const failureModeLabels = @json($failureModeLabels);
const oldFailure = @json(old('failure_mode'));
const planDomain = document.getElementById('plan_domain');
const planFailure = document.getElementById('plan_failure_mode');
const planSector = document.getElementById('plan_sector');
const planType = document.getElementById('plan_asset_type');
const planRef = document.getElementById('plan_asset_reference');
const planTrigger = document.getElementById('plan_trigger_mode');
const meterWrap = document.getElementById('plan_meter_threshold_wrap');
const fillTemplateBtn = document.getElementById('plan_fill_template');
const checklistField = document.getElementById('plan_checklist');
const procedureField = document.getElementById('plan_procedure_steps');
const safetyField = document.getElementById('plan_safety_notes');
const sparesField = document.getElementById('plan_spare_parts_list');
const planFrequency = document.getElementById('plan_frequency');
const planDuration = document.querySelector('input[name="estimated_duration_minutes"]');
const seedChecks = document.querySelectorAll('.checklist-seed');
const groups = {
    industrial: document.getElementById('plan_industrial_group'),
    technical: document.getElementById('plan_technical_group'),
    logistic: document.getElementById('plan_logistic_group'),
};
const selectors = {
    industrial: document.getElementById('plan_industrial_id'),
    technical: document.getElementById('plan_technical_id'),
    logistic: document.getElementById('plan_logistic_id'),
};
const preview = {
    ref: document.getElementById('preview_ref'),
    frequency: document.getElementById('preview_frequency'),
    domain: document.getElementById('preview_domain'),
    failure: document.getElementById('preview_failure'),
    duration: document.getElementById('preview_duration'),
};

const seedLabels = {
    lubrication: @json(__('gmao.maintenance.checklist_seed_lubrication')),
    measurement: @json(__('gmao.maintenance.checklist_seed_measurement')),
    inspection: @json(__('gmao.maintenance.checklist_seed_inspection')),
    replacement: @json(__('gmao.maintenance.checklist_seed_replacement')),
    cleaning: @json(__('gmao.maintenance.checklist_seed_cleaning')),
    tightening: @json(__('gmao.maintenance.checklist_seed_tightening')),
    calibration: @json(__('gmao.maintenance.checklist_seed_calibration')),
    safety_test: @json(__('gmao.maintenance.checklist_seed_safety_test')),
};

function renderPlanFailureModes(domain, selected = '') {
    const options = failureTaxonomy[domain] || [];
    planFailure.innerHTML = '';
    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = @json(__('gmao.maintenance.failure_mode'));
    planFailure.appendChild(placeholder);
    options.forEach((mode) => {
        const opt = document.createElement('option');
        opt.value = mode;
        opt.textContent = failureModeLabels[mode] || mode.replaceAll('_', ' ');
        if (selected && selected === mode) opt.selected = true;
        planFailure.appendChild(opt);
    });
}

function filterOptionsBySector(select, sector) {
    if (!select) return;
    [...select.options].forEach((opt, idx) => {
        if (idx === 0) return;
        const rowSector = opt.dataset.sector || '';
        opt.hidden = !!sector && rowSector !== sector;
        if (opt.hidden && opt.selected) select.value = '';
    });
}

function syncPlanAssetType() {
    const type = planType?.value || 'other';
    Object.keys(groups).forEach((key) => groups[key].classList.toggle('d-none', key !== type));
}

function syncPlanSector() {
    const sector = planSector?.value || '';
    Object.values(selectors).forEach((select) => filterOptionsBySector(select, sector));
}

function syncReferenceFromSelection() {
    const type = planType?.value || 'other';
    const select = selectors[type];
    if (!select || !select.value) return;
    const selected = select.options[select.selectedIndex];
    if (selected?.dataset?.code && !planRef.value) {
        planRef.value = selected.dataset.code;
    }
}

function syncMeterVisibility() {
    meterWrap.classList.toggle('d-none', !(planTrigger.value === 'meter' || planTrigger.value === 'both'));
}

function buildPreventiveTemplate() {
    const domain = planDomain?.value || '';
    const mode = planFailure?.value || '';
    const frequency = planFrequency?.value || '';
    const reference = planRef?.value || '-';

    const domainText = domain ? (planDomain.options[planDomain.selectedIndex]?.text || domain) : '-';
    const modeText = mode ? (planFailure.options[planFailure.selectedIndex]?.text || mode) : '-';
    const frequencyText = frequency ? (planFrequency.options[planFrequency.selectedIndex]?.text || frequency) : '-';

    procedureField.value = [
        `1) ${@json(__('gmao.maintenance.template_procedure_step1'))}`,
        `2) ${@json(__('gmao.maintenance.template_procedure_step2'))} ${domainText}.`,
        `3) ${@json(__('gmao.maintenance.template_procedure_step3'))} ${modeText}.`,
        `4) ${@json(__('gmao.maintenance.template_procedure_step4'))}`,
        `5) ${@json(__('gmao.maintenance.template_procedure_step5'))}`,
    ].join('\n');

    safetyField.value = [
        `- ${@json(__('gmao.maintenance.template_safety_1'))}`,
        `- ${@json(__('gmao.maintenance.template_safety_2'))}`,
        `- ${@json(__('gmao.maintenance.template_safety_3'))}`,
    ].join('\n');

    sparesField.value = [
        `- ${@json(__('gmao.maintenance.template_spare_1'))}`,
        `- ${@json(__('gmao.maintenance.template_spare_2'))}`,
        `- ${@json(__('gmao.maintenance.template_spare_3'))}`,
        `- ${@json(__('gmao.maintenance.template_spare_4'))}`,
    ].join('\n');

    if (!checklistField.value.trim()) {
        checklistField.value = [
            `1) ${@json(__('gmao.maintenance.template_checklist'))}: ${reference}`,
            `2) ${@json(__('gmao.maintenance.domain'))}: ${domainText}`,
            `3) ${@json(__('gmao.maintenance.failure_mode'))}: ${modeText}`,
            `4) ${@json(__('gmao.common.frequency'))}: ${frequencyText}`,
            `5) ${@json(__('gmao.maintenance.template_checklist'))}: ${@json(__('gmao.maintenance.template_checklist_points'))}`,
        ].join('\n');
    }
}

function appendChecklistFromSeeds() {
    const selected = [...seedChecks].filter((node) => node.checked).map((node) => seedLabels[node.value]).filter(Boolean);
    const fromCurrent = checklistField.value.split('\n').map((line) => line.trim().replace(/^\d+[\)\.\-\s]*/, '')).filter(Boolean);
    const merged = [...new Set([...fromCurrent, ...selected])];
    checklistField.value = merged.map((line, index) => `${index + 1}) ${line}`).join('\n');
}

function syncPreview() {
    preview.ref.textContent = planRef.value || '-';
    preview.frequency.textContent = planFrequency.options[planFrequency.selectedIndex]?.text || '-';
    preview.domain.textContent = planDomain.options[planDomain.selectedIndex]?.text || '-';
    preview.failure.textContent = planFailure.options[planFailure.selectedIndex]?.text || '-';
    preview.duration.textContent = planDuration.value ? `${planDuration.value} min` : '-';
}

if (planDomain) {
    renderPlanFailureModes(planDomain.value, oldFailure);
    planDomain.addEventListener('change', (e) => { renderPlanFailureModes(e.target.value); syncPreview(); });
}
if (planFailure) planFailure.addEventListener('change', syncPreview);
if (planType) planType.addEventListener('change', () => { syncPlanAssetType(); syncReferenceFromSelection(); syncPreview(); });
if (planSector) planSector.addEventListener('change', syncPlanSector);
if (planRef) planRef.addEventListener('input', syncPreview);
if (planFrequency) planFrequency.addEventListener('change', syncPreview);
if (planDuration) planDuration.addEventListener('input', syncPreview);
Object.values(selectors).forEach((select) => select?.addEventListener('change', () => { syncReferenceFromSelection(); syncPreview(); }));
if (planTrigger) planTrigger.addEventListener('change', syncMeterVisibility);
if (fillTemplateBtn) fillTemplateBtn.addEventListener('click', () => { buildPreventiveTemplate(); syncPreview(); });
seedChecks.forEach((node) => node.addEventListener('change', appendChecklistFromSeeds));

syncPlanAssetType();
syncPlanSector();
syncMeterVisibility();
syncPreview();
</script>
@endpush

