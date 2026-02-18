<?php

namespace App\Http\Controllers;

use App\Http\Requests\Assets\ExpertMissionRequest;
use App\Http\Requests\Assets\IndustrialMachineRequest;
use App\Http\Requests\Assets\LogisticAssetRequest;
use App\Http\Requests\Assets\ReportFileRequest;
use App\Http\Requests\Assets\SparePartRequest;
use App\Http\Requests\Assets\TechnicalAssetRequest;
use App\Models\ExpertMission;
use App\Models\IndustrialMachine;
use App\Models\LogisticAsset;
use App\Models\MaintenanceRequest;
use App\Models\MaintenanceTask;
use App\Models\PreventivePlan;
use App\Models\Project;
use App\Models\ReportFile;
use App\Models\SparePart;
use App\Models\TechnicalAsset;
use App\Support\GmaoOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function createIndustrial(): View
    {
        $this->authorize('create', IndustrialMachine::class);

        return view('assets.industrial-create', ['sectors' => GmaoOptions::SECTORS]);
    }

    public function industrial(Request $request): View
    {
        $this->authorize('viewAny', IndustrialMachine::class);

        $query = IndustrialMachine::query()->latest();
        if (!$request->user()?->isRole('super_admin') && !empty($request->user()?->sector)) {
            $query->where('sector', $request->user()->sector);
        }
        if ($request->filled('sector')) {
            $query->where('sector', $request->query('sector'));
        }

        return view('assets.industrial', [
            'items' => $query->paginate(15)->withQueryString(),
            'sectors' => GmaoOptions::SECTORS,
            'selectedSector' => (string) $request->query('sector', ''),
        ]);
    }

    public function storeIndustrial(IndustrialMachineRequest $request): RedirectResponse
    {
        $this->authorize('create', IndustrialMachine::class);

        IndustrialMachine::create($request->validated());

        return back()->with('success', __('gmao.msg.asset_created'));
    }

    public function updateIndustrial(IndustrialMachineRequest $request, IndustrialMachine $industrialMachine): RedirectResponse
    {
        $this->authorize('update', $industrialMachine);

        $industrialMachine->update($request->validated());

        return back()->with('success', __('gmao.msg.asset_updated'));
    }

    public function destroyIndustrial(IndustrialMachine $industrialMachine): RedirectResponse
    {
        $this->authorize('delete', $industrialMachine);

        $industrialMachine->delete();

        return back()->with('success', __('gmao.msg.asset_deleted'));
    }

    public function technical(Request $request): View
    {
        $this->authorize('viewAny', TechnicalAsset::class);

        $query = TechnicalAsset::query()->latest();
        if (!$request->user()?->isRole('super_admin') && !empty($request->user()?->sector)) {
            $query->where('sector', $request->user()->sector);
        }
        if ($request->filled('sector')) {
            $query->where('sector', $request->query('sector'));
        }

        return view('assets.technical', [
            'items' => $query->paginate(15)->withQueryString(),
            'sectors' => GmaoOptions::SECTORS,
            'selectedSector' => (string) $request->query('sector', ''),
        ]);
    }

    public function createTechnical(): View
    {
        $this->authorize('create', TechnicalAsset::class);

        return view('assets.technical-create', ['sectors' => GmaoOptions::SECTORS]);
    }

    public function storeTechnical(TechnicalAssetRequest $request): RedirectResponse
    {
        $this->authorize('create', TechnicalAsset::class);

        TechnicalAsset::create($request->validated());

        return back()->with('success', __('gmao.msg.asset_created'));
    }

    public function updateTechnical(TechnicalAssetRequest $request, TechnicalAsset $technicalAsset): RedirectResponse
    {
        $this->authorize('update', $technicalAsset);

        $technicalAsset->update($request->validated());

        return back()->with('success', __('gmao.msg.asset_updated'));
    }

    public function destroyTechnical(TechnicalAsset $technicalAsset): RedirectResponse
    {
        $this->authorize('delete', $technicalAsset);

        $technicalAsset->delete();

        return back()->with('success', __('gmao.msg.asset_deleted'));
    }

    public function spareParts(Request $request): View
    {
        $this->authorize('viewAny', SparePart::class);

        $query = SparePart::query()->latest();
        if (!$request->user()?->isRole('super_admin') && !empty($request->user()?->sector)) {
            $query->where('sector', $request->user()->sector);
        }
        if ($request->filled('sector')) {
            $query->where('sector', $request->query('sector'));
        }

        return view('assets.spare-parts', [
            'items' => $query->paginate(15)->withQueryString(),
            'sectors' => GmaoOptions::SECTORS,
            'selectedSector' => (string) $request->query('sector', ''),
        ]);
    }

    public function createSparePart(): View
    {
        $this->authorize('create', SparePart::class);

        return view('assets.spare-parts-create', ['sectors' => GmaoOptions::SECTORS]);
    }

    public function storeSparePart(SparePartRequest $request): RedirectResponse
    {
        $this->authorize('create', SparePart::class);

        SparePart::create($request->validated());

        return back()->with('success', __('gmao.msg.asset_created'));
    }

    public function updateSparePart(SparePartRequest $request, SparePart $sparePart): RedirectResponse
    {
        $this->authorize('update', $sparePart);

        $sparePart->update($request->validated());

        return back()->with('success', __('gmao.msg.asset_updated'));
    }

    public function destroySparePart(SparePart $sparePart): RedirectResponse
    {
        $this->authorize('delete', $sparePart);

        $sparePart->delete();

        return back()->with('success', __('gmao.msg.asset_deleted'));
    }

    public function experts(): View
    {
        $this->authorize('viewAny', ExpertMission::class);

        return view('assets.experts', ['items' => ExpertMission::latest()->paginate(15)]);
    }

    public function createExpert(): View
    {
        $this->authorize('create', ExpertMission::class);

        return view('assets.experts-create');
    }

    public function storeExpert(ExpertMissionRequest $request): RedirectResponse
    {
        $this->authorize('create', ExpertMission::class);

        ExpertMission::create($request->validated());

        return back()->with('success', __('gmao.msg.asset_created'));
    }

    public function updateExpert(ExpertMissionRequest $request, ExpertMission $expertMission): RedirectResponse
    {
        $this->authorize('update', $expertMission);

        $expertMission->update($request->validated());

        return back()->with('success', __('gmao.msg.asset_updated'));
    }

    public function destroyExpert(ExpertMission $expertMission): RedirectResponse
    {
        $this->authorize('delete', $expertMission);

        $expertMission->delete();

        return back()->with('success', __('gmao.msg.asset_deleted'));
    }

    public function reports(Request $request): View
    {
        $this->authorize('viewAny', ReportFile::class);

        $query = ReportFile::query()->latest();
        $user = $request->user();
        if ($user && !$user->isRole('super_admin') && !empty($user->sector)) {
            $query->where(function ($q) use ($user): void {
                $q->where('sector', $user->sector)->orWhereNull('sector');
            });
        }

        if ($request->filled('sector')) {
            $query->where('sector', $request->query('sector'));
        }
        if ($request->filled('context_type')) {
            $query->where('context_type', $request->query('context_type'));
        }
        if ($request->filled('context_id')) {
            $query->where('context_id', (int) $request->query('context_id'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('report_date', '>=', $request->query('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('report_date', '<=', $request->query('date_to'));
        }

        $contextOptions = $this->reportContextOptions($request);
        $selectedContextType = (string) $request->query('context_type', '');
        $selectedContextId = (string) $request->query('context_id', '');

        return view('assets.reports', [
            'items' => $query->paginate(15)->withQueryString(),
            'contextOptions' => $contextOptions,
            'contextTypes' => array_keys(ReportFile::CONTEXT_TYPES),
            'selectedContextType' => $selectedContextType,
            'selectedContextId' => $selectedContextId,
            'selectedSector' => (string) $request->query('sector', ''),
            'selectedDateFrom' => (string) $request->query('date_from', ''),
            'selectedDateTo' => (string) $request->query('date_to', ''),
            'sectors' => GmaoOptions::SECTORS,
        ]);
    }

    public function createReport(Request $request): View
    {
        $this->authorize('create', ReportFile::class);

        $contextOptions = $this->reportContextOptions($request);
        $contextTypes = array_keys(ReportFile::CONTEXT_TYPES);
        $selectedContextType = (string) $request->query('context_type', '');
        if (!in_array($selectedContextType, $contextTypes, true)) {
            $selectedContextType = '';
        }
        $selectedContextId = (string) $request->query('context_id', '');
        if ($selectedContextType === '' || $selectedContextId === '') {
            $selectedContextId = '';
        } else {
            $exists = collect($contextOptions[$selectedContextType] ?? [])
                ->contains(fn (array $item): bool => (string) $item['id'] === $selectedContextId);
            if (!$exists) {
                $selectedContextId = '';
            }
        }

        return view('assets.reports-create', [
            'contextOptions' => $contextOptions,
            'contextTypes' => $contextTypes,
            'selectedContextType' => $selectedContextType,
            'selectedContextId' => $selectedContextId,
        ]);
    }

    public function storeReport(ReportFileRequest $request): RedirectResponse
    {
        $this->authorize('create', ReportFile::class);

        $validated = $request->validated();
        $contextPayload = $this->resolveReportContextPayload($validated['context_type'], (int) $validated['context_id']);
        unset($validated['report_file']);
        $filePath = $request->hasFile('report_file') ? $request->file('report_file')->store('reports', 'public') : null;

        ReportFile::create([
            ...$validated,
            ...$contextPayload,
            'file_path' => $filePath,
            'created_by' => $request->user()->id,
        ]);

        return back()->with('success', __('gmao.msg.report_uploaded'));
    }

    public function updateReport(ReportFileRequest $request, ReportFile $reportFile): RedirectResponse
    {
        $this->authorize('update', $reportFile);

        $validated = $request->validated();
        $contextPayload = $this->resolveReportContextPayload($validated['context_type'], (int) $validated['context_id']);
        unset($validated['report_file']);

        if ($request->hasFile('report_file')) {
            if ($reportFile->file_path) {
                Storage::disk('public')->delete($reportFile->file_path);
            }

            $validated['file_path'] = $request->file('report_file')->store('reports', 'public');
        }

        $reportFile->update([
            ...$validated,
            ...$contextPayload,
        ]);

        return back()->with('success', __('gmao.msg.report_updated'));
    }

    public function destroyReport(ReportFile $reportFile): RedirectResponse
    {
        $this->authorize('delete', $reportFile);

        if ($reportFile->file_path) {
            Storage::disk('public')->delete($reportFile->file_path);
        }

        $reportFile->delete();

        return back()->with('success', __('gmao.msg.report_deleted'));
    }

    public function logistics(Request $request): View
    {
        $this->authorize('viewAny', LogisticAsset::class);

        $query = LogisticAsset::query()->latest();
        if (!$request->user()?->isRole('super_admin') && !empty($request->user()?->sector)) {
            $query->where('sector', $request->user()->sector);
        }
        if ($request->filled('sector')) {
            $query->where('sector', $request->query('sector'));
        }

        return view('assets.logistics', [
            'items' => $query->paginate(15)->withQueryString(),
            'sectors' => GmaoOptions::SECTORS,
            'selectedSector' => (string) $request->query('sector', ''),
        ]);
    }

    public function createLogistic(): View
    {
        $this->authorize('create', LogisticAsset::class);

        return view('assets.logistics-create', ['sectors' => GmaoOptions::SECTORS]);
    }

    public function storeLogistic(LogisticAssetRequest $request): RedirectResponse
    {
        $this->authorize('create', LogisticAsset::class);

        LogisticAsset::create($request->validated());

        return back()->with('success', __('gmao.msg.asset_created'));
    }

    public function updateLogistic(LogisticAssetRequest $request, LogisticAsset $logisticAsset): RedirectResponse
    {
        $this->authorize('update', $logisticAsset);

        $logisticAsset->update($request->validated());

        return back()->with('success', __('gmao.msg.asset_updated'));
    }

    public function destroyLogistic(LogisticAsset $logisticAsset): RedirectResponse
    {
        $this->authorize('delete', $logisticAsset);

        $logisticAsset->delete();

        return back()->with('success', __('gmao.msg.asset_deleted'));
    }

    private function reportContextOptions(Request $request): array
    {
        $user = $request->user();
        $sector = (!$user?->isRole('super_admin') && !empty($user?->sector)) ? $user->sector : null;

        $scoped = function ($query) use ($sector) {
            return $query->when(!empty($sector), fn ($q) => $q->where('sector', $sector));
        };

        return [
            'project' => $scoped(Project::query())->latest()->take(200)->get(['id', 'name', 'code', 'sector'])->map(fn ($r) => [
                'id' => $r->id,
                'label' => trim(($r->code ? $r->code . ' - ' : '') . $r->name),
                'sector' => $r->sector,
            ])->values()->all(),
            'maintenance_request' => $scoped(MaintenanceRequest::query())->latest()->take(200)->get(['id', 'request_code', 'asset_reference', 'sector'])->map(fn ($r) => [
                'id' => $r->id,
                'label' => trim(($r->request_code ?: ('#' . $r->id)) . ' - ' . ($r->asset_reference ?: '-')),
                'sector' => $r->sector,
            ])->values()->all(),
            'maintenance_task' => $scoped(MaintenanceTask::query())->latest()->take(200)->get(['id', 'title', 'sector'])->map(fn ($r) => [
                'id' => $r->id,
                'label' => trim('#' . $r->id . ' - ' . $r->title),
                'sector' => $r->sector,
            ])->values()->all(),
            'preventive_plan' => $scoped(PreventivePlan::query())->latest()->take(200)->get(['id', 'title', 'asset_reference', 'sector'])->map(fn ($r) => [
                'id' => $r->id,
                'label' => trim($r->title . ' - ' . ($r->asset_reference ?: '-')),
                'sector' => $r->sector,
            ])->values()->all(),
            'industrial_machine' => $scoped(IndustrialMachine::query())->latest()->take(200)->get(['id', 'name', 'code', 'sector'])->map(fn ($r) => [
                'id' => $r->id,
                'label' => trim(($r->code ? $r->code . ' - ' : '') . $r->name),
                'sector' => $r->sector,
            ])->values()->all(),
            'technical_asset' => $scoped(TechnicalAsset::query())->latest()->take(200)->get(['id', 'name', 'code', 'sector'])->map(fn ($r) => [
                'id' => $r->id,
                'label' => trim(($r->code ? $r->code . ' - ' : '') . $r->name),
                'sector' => $r->sector,
            ])->values()->all(),
            'logistic_asset' => $scoped(LogisticAsset::query())->latest()->take(200)->get(['id', 'name', 'code', 'sector'])->map(fn ($r) => [
                'id' => $r->id,
                'label' => trim(($r->code ? $r->code . ' - ' : '') . $r->name),
                'sector' => $r->sector,
            ])->values()->all(),
            'spare_part' => $scoped(SparePart::query())->latest()->take(200)->get(['id', 'name', 'sku', 'sector'])->map(fn ($r) => [
                'id' => $r->id,
                'label' => trim(($r->sku ? $r->sku . ' - ' : '') . $r->name),
                'sector' => $r->sector,
            ])->values()->all(),
            'expert_mission' => ExpertMission::query()->latest()->take(200)->get(['id', 'mission_title', 'expert_name'])->map(fn ($r) => [
                'id' => $r->id,
                'label' => trim($r->mission_title . ' - ' . $r->expert_name),
                'sector' => null,
            ])->values()->all(),
        ];
    }

    private function resolveReportContextPayload(string $contextType, int $contextId): array
    {
        $class = ReportFile::contextClass($contextType);
        $record = $class::query()->findOrFail($contextId);

        $label = method_exists($record, 'getAttribute')
            ? (string) (
                $record->getAttribute('name')
                ?? $record->getAttribute('title')
                ?? $record->getAttribute('request_code')
                ?? $record->getAttribute('code')
                ?? $record->getAttribute('sku')
                ?? ('#' . $record->id)
            )
            : ('#' . $contextId);

        return [
            'context_type' => $contextType,
            'context_id' => $contextId,
            'context_label' => trim($label),
            'sector' => $record->sector ?? null,
        ];
    }

}
