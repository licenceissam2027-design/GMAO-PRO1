@extends('layouts.app')
@section('content')
<div class="card p-3 form-shell req-builder">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-1">{{ __('gmao.maintenance.new_request') }}</h5>
            <div class="small text-muted">{{ __('gmao.maintenance.request_page_subtitle') }}</div>
        </div>
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('maintenance.requests') }}">{{ __('gmao.common.open') }}</a>
    </div>

    <form method="POST" action="{{ route('maintenance.requests.store') }}" class="row g-3" id="maintenance-request-form">
        @csrf

        <div class="col-lg-8">
            <div class="req-section-card">
                <div class="req-section-title">{{ __('gmao.maintenance.quick_issue_pick') }}</div>
                <div class="row g-2">
                    <div class="col-md-4">
                        <select class="form-select" name="sector" id="sector_select" required>
                            <option value="">{{ __('gmao.common.sector') }}</option>
                            @foreach($sectors as $sector)
                                <option value="{{ $sector }}" @selected(old('sector') === $sector)>{{ __('gmao.enum.sector.'.$sector) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" name="asset_type" id="asset_type_select">
                            @foreach(['industrial','technical','logistic','other'] as $t)
                                <option value="{{ $t }}" @selected(old('asset_type', 'industrial') === $t)>{{ __('gmao.enum.type.'.$t) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" name="issue_category" id="issue_category_select">
                            @foreach(['breakdown','quality','safety','software','electrical','mechanical','other'] as $c)
                                <option value="{{ $c }}" @selected(old('issue_category', 'breakdown') === $c)>{{ __('gmao.enum.issue.'.$c) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <select class="form-select" name="maintenance_domain" id="maintenance_domain" required>
                            <option value="">{{ __('gmao.maintenance.domain') }}</option>
                            @foreach($maintenanceDomains as $domain)
                                <option value="{{ $domain }}" @selected(old('maintenance_domain') === $domain)>{{ $domainLabels[$domain] ?? $domain }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <select class="form-select" name="failure_mode" id="failure_mode" required>
                            <option value="">{{ __('gmao.maintenance.failure_mode') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="req-section-card mt-3">
                <div class="req-section-title">{{ __('gmao.maintenance.asset_identification') }}</div>
                <div class="row g-2">
                    <div class="col-md-6 asset-group" id="industrial_group">
                        <select class="form-select" name="industrial_machine_id" id="industrial_machine_id">
                            <option value="">{{ __('gmao.maintenance.machine') }}</option>
                            @foreach($machines as $machine)
                                <option value="{{ $machine->id }}" data-sector="{{ $machine->sector }}" data-code="{{ $machine->code }}" data-name="{{ $machine->name }}" @selected((string) old('industrial_machine_id') === (string) $machine->id)>{{ $machine->code }} - {{ $machine->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 asset-group d-none" id="technical_group">
                        <select class="form-select" name="technical_asset_id" id="technical_asset_id">
                            <option value="">{{ __('gmao.nav.technical') }}</option>
                            @foreach($technicalAssets as $asset)
                                <option value="{{ $asset->id }}" data-sector="{{ $asset->sector }}" data-code="{{ $asset->code }}" data-name="{{ $asset->name }}" @selected((string) old('technical_asset_id') === (string) $asset->id)>{{ $asset->code }} - {{ $asset->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 asset-group d-none" id="logistic_group">
                        <select class="form-select" name="logistic_asset_id" id="logistic_asset_id">
                            <option value="">{{ __('gmao.nav.logistics') }}</option>
                            @foreach($logisticAssets as $asset)
                                <option value="{{ $asset->id }}" data-sector="{{ $asset->sector }}" data-code="{{ $asset->code }}" data-name="{{ $asset->name }}" @selected((string) old('logistic_asset_id') === (string) $asset->id)>{{ $asset->code }} - {{ $asset->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control" name="asset_reference" id="asset_reference" value="{{ old('asset_reference') }}" placeholder="{{ __('gmao.common.reference') }}">
                    </div>
                    <div class="col-md-6">
                        <input class="form-control" name="location" id="location_input" value="{{ old('location') }}" placeholder="{{ __('gmao.common.location') }}">
                    </div>
                </div>
            </div>

            <div class="req-section-card mt-3">
                <div class="req-section-title">{{ __('gmao.maintenance.symptoms') }}</div>
                <div class="req-symptom-grid">
                    @foreach(['unusual_noise','vibration','overheating','leak','pressure_drop','no_power','no_start','intermittent_stop','alarm_active','communication_loss'] as $symptom)
                        <label class="req-symptom-item">
                            <input class="form-check-input symptom-check" type="checkbox" value="{{ $symptom }}">
                            <span>{{ __('gmao.maintenance.symptom_'.$symptom) }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="small text-muted mt-2">{{ __('gmao.maintenance.issue_hint') }}</div>
            </div>

            <div class="req-section-card mt-3">
                <div class="req-section-title">{{ __('gmao.maintenance.impact_assessment') }}</div>
                <div class="row g-2">
                    <div class="col-md-3">
                        <select class="form-select" name="severity" id="severity_select">
                            @foreach(['low','medium','high','critical'] as $p)
                                <option value="{{ $p }}" @selected(old('severity', 'medium') === $p)>{{ __('gmao.enum.priority.'.$p) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input class="form-control" type="datetime-local" name="occurrence_at" id="occurrence_at" value="{{ old('occurrence_at') }}">
                    </div>
                    <div class="col-md-3">
                        <input class="form-control" type="number" min="0" name="downtime_minutes" id="downtime_minutes" value="{{ old('downtime_minutes') }}" placeholder="{{ __('gmao.maintenance.downtime_minutes') }}">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="assigned_to" id="assigned_to">
                            <option value="">{{ __('gmao.maintenance.assign_tech') }}</option>
                            @foreach($technicians as $t)
                                <option value="{{ $t->id }}" data-sector="{{ $t->sector }}" @selected((string) old('assigned_to') === (string) $t->id)>{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="req-section-card mt-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="req-section-title mb-0">{{ __('gmao.maintenance.guided_description') }}</div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="generate_description">{{ __('gmao.maintenance.generate_description') }}</button>
                </div>
                <textarea class="form-control" name="description" id="description_input" rows="5" required placeholder="{{ __('gmao.maintenance.request_desc') }}">{{ old('description') }}</textarea>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="req-side-card">
                <h6 class="mb-2">{{ __('gmao.maintenance.report_summary') }}</h6>
                <div class="req-preview-list">
                    <div><span>{{ __('gmao.common.reference') }}</span><strong id="preview_reference">-</strong></div>
                    <div><span>{{ __('gmao.maintenance.domain') }}</span><strong id="preview_domain">-</strong></div>
                    <div><span>{{ __('gmao.maintenance.failure_mode') }}</span><strong id="preview_failure">-</strong></div>
                    <div><span>{{ __('gmao.common.priority') }}</span><strong id="preview_severity">-</strong></div>
                    <div><span>{{ __('gmao.maintenance.downtime_minutes') }}</span><strong id="preview_downtime">-</strong></div>
                </div>
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
const domainLabels = @json($domainLabels);
const symptomLabels = {
    unusual_noise: @json(__('gmao.maintenance.symptom_unusual_noise')),
    vibration: @json(__('gmao.maintenance.symptom_vibration')),
    overheating: @json(__('gmao.maintenance.symptom_overheating')),
    leak: @json(__('gmao.maintenance.symptom_leak')),
    pressure_drop: @json(__('gmao.maintenance.symptom_pressure_drop')),
    no_power: @json(__('gmao.maintenance.symptom_no_power')),
    no_start: @json(__('gmao.maintenance.symptom_no_start')),
    intermittent_stop: @json(__('gmao.maintenance.symptom_intermittent_stop')),
    alarm_active: @json(__('gmao.maintenance.symptom_alarm_active')),
    communication_loss: @json(__('gmao.maintenance.symptom_communication_loss')),
};
const oldFailureMode = @json(old('failure_mode'));

const domainSelect = document.getElementById('maintenance_domain');
const failureModeSelect = document.getElementById('failure_mode');
const sectorSelect = document.getElementById('sector_select');
const assetTypeSelect = document.getElementById('asset_type_select');
const issueCategorySelect = document.getElementById('issue_category_select');
const industrialGroup = document.getElementById('industrial_group');
const technicalGroup = document.getElementById('technical_group');
const logisticGroup = document.getElementById('logistic_group');
const industrialSelect = document.getElementById('industrial_machine_id');
const technicalSelect = document.getElementById('technical_asset_id');
const logisticSelect = document.getElementById('logistic_asset_id');
const assignedSelect = document.getElementById('assigned_to');
const severitySelect = document.getElementById('severity_select');
const assetReferenceInput = document.getElementById('asset_reference');
const locationInput = document.getElementById('location_input');
const descriptionInput = document.getElementById('description_input');
const occurrenceInput = document.getElementById('occurrence_at');
const downtimeInput = document.getElementById('downtime_minutes');
const symptomChecks = document.querySelectorAll('.symptom-check');
const generateBtn = document.getElementById('generate_description');

const previewRef = document.getElementById('preview_reference');
const previewDomain = document.getElementById('preview_domain');
const previewFailure = document.getElementById('preview_failure');
const previewSeverity = document.getElementById('preview_severity');
const previewDowntime = document.getElementById('preview_downtime');

function renderFailureModes(domain, selected = '') {
    const options = failureTaxonomy[domain] || [];
    failureModeSelect.innerHTML = '';
    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = @json(__('gmao.maintenance.failure_mode'));
    failureModeSelect.appendChild(placeholder);
    options.forEach((mode) => {
        const opt = document.createElement('option');
        opt.value = mode;
        opt.textContent = failureModeLabels[mode] || mode.replaceAll('_', ' ');
        if (selected && selected === mode) opt.selected = true;
        failureModeSelect.appendChild(opt);
    });
}

function filterBySector(select, sector) {
    if (!select) return;
    [...select.options].forEach((opt, idx) => {
        if (idx === 0) return;
        const rowSector = opt.dataset.sector || '';
        opt.hidden = !!sector && rowSector !== sector;
        if (opt.hidden && opt.selected) select.value = '';
    });
}

function syncAssetTypeUI() {
    const type = assetTypeSelect?.value || 'other';
    industrialGroup?.classList.toggle('d-none', type !== 'industrial');
    technicalGroup?.classList.toggle('d-none', type !== 'technical');
    logisticGroup?.classList.toggle('d-none', type !== 'logistic');
}

function syncSectorFilters() {
    const sector = sectorSelect?.value || '';
    filterBySector(industrialSelect, sector);
    filterBySector(technicalSelect, sector);
    filterBySector(logisticSelect, sector);
    filterBySector(assignedSelect, sector);
}

function currentAssetOption() {
    const type = assetTypeSelect?.value || 'other';
    if (type === 'industrial' && industrialSelect.value) return industrialSelect.options[industrialSelect.selectedIndex];
    if (type === 'technical' && technicalSelect.value) return technicalSelect.options[technicalSelect.selectedIndex];
    if (type === 'logistic' && logisticSelect.value) return logisticSelect.options[logisticSelect.selectedIndex];
    return null;
}

function syncAssetReference() {
    const opt = currentAssetOption();
    if (!opt) return;
    const code = opt.dataset.code || '';
    if (code && !assetReferenceInput.value) {
        assetReferenceInput.value = code;
    }
}

function selectedSymptoms() {
    return [...symptomChecks]
        .filter((node) => node.checked)
        .map((node) => symptomLabels[node.value] || node.value);
}

function autoMapIssueCategory() {
    const domain = domainSelect.value;
    if (['electrical', 'plc', 'it_support', 'telephony'].includes(domain)) {
        issueCategorySelect.value = domain === 'electrical' ? 'electrical' : 'software';
        return;
    }
    if (['mechanical', 'hydraulic', 'pneumatic', 'logistic', 'climatisation'].includes(domain)) {
        issueCategorySelect.value = 'mechanical';
        return;
    }
    if (domain === 'safety') {
        issueCategorySelect.value = 'safety';
    }
}

function generateGuidedDescription() {
    const domainText = domainSelect.value ? (domainLabels[domainSelect.value] || domainSelect.options[domainSelect.selectedIndex].text) : '-';
    const failureText = failureModeSelect.value ? (failureModeLabels[failureModeSelect.value] || failureModeSelect.options[failureModeSelect.selectedIndex].text) : '-';
    const assetOpt = currentAssetOption();
    const assetLabel = assetOpt ? assetOpt.text : (assetReferenceInput.value || '-');
    const symptoms = selectedSymptoms();
    const downtime = downtimeInput.value ? `${downtimeInput.value} ${@json(__('gmao.maintenance.downtime_minutes'))}` : '-';
    const place = locationInput.value || '-';

    const lines = [
        `${@json(__('gmao.maintenance.domain'))}: ${domainText}`,
        `${@json(__('gmao.maintenance.failure_mode'))}: ${failureText}`,
        `${@json(__('gmao.maintenance.machine'))}: ${assetLabel}`,
        `${@json(__('gmao.common.location'))}: ${place}`,
        `${@json(__('gmao.maintenance.downtime_minutes'))}: ${downtime}`,
        `${@json(__('gmao.maintenance.symptoms'))}: ${symptoms.length ? symptoms.join(' / ') : '-'}`,
        `${@json(__('gmao.maintenance.request_desc'))}: `,
    ];

    descriptionInput.value = lines.join('\n');
}

function syncPreview() {
    previewRef.textContent = assetReferenceInput.value || currentAssetOption()?.dataset.code || '-';
    previewDomain.textContent = domainSelect.value ? (domainLabels[domainSelect.value] || '-') : '-';
    previewFailure.textContent = failureModeSelect.value ? (failureModeLabels[failureModeSelect.value] || '-') : '-';
    previewSeverity.textContent = severitySelect.options[severitySelect.selectedIndex]?.text || '-';
    previewDowntime.textContent = downtimeInput.value ? `${downtimeInput.value} min` : '-';
}

if (occurrenceInput && !occurrenceInput.value) {
    const now = new Date();
    occurrenceInput.value = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
}

renderFailureModes(domainSelect.value, oldFailureMode);
syncAssetTypeUI();
syncSectorFilters();
syncPreview();

domainSelect.addEventListener('change', (e) => {
    renderFailureModes(e.target.value);
    autoMapIssueCategory();
    syncPreview();
});
failureModeSelect.addEventListener('change', syncPreview);
assetTypeSelect.addEventListener('change', () => { syncAssetTypeUI(); syncAssetReference(); syncPreview(); });
sectorSelect.addEventListener('change', syncSectorFilters);
industrialSelect.addEventListener('change', () => { syncAssetReference(); syncPreview(); });
technicalSelect.addEventListener('change', () => { syncAssetReference(); syncPreview(); });
logisticSelect.addEventListener('change', () => { syncAssetReference(); syncPreview(); });
assetReferenceInput.addEventListener('input', syncPreview);
severitySelect.addEventListener('change', syncPreview);
downtimeInput.addEventListener('input', syncPreview);
symptomChecks.forEach((node) => node.addEventListener('change', syncPreview));
generateBtn.addEventListener('click', generateGuidedDescription);
</script>
@endpush

