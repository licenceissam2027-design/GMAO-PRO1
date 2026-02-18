<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class AuditableObserver
{
    public function created(Model $model): void
    {
        $this->write('created', $model, null, $this->sanitize($model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $changes = $this->sanitize($model->getChanges());
        unset($changes['updated_at']);
        if ($changes === []) {
            return;
        }

        $old = $this->sanitize(Arr::only($model->getOriginal(), array_keys($changes)));
        $this->write('updated', $model, $old, $changes);
    }

    public function deleted(Model $model): void
    {
        $this->write('deleted', $model, $this->sanitize($model->getOriginal()), null);
    }

    private function write(string $event, Model $model, ?array $oldValues, ?array $newValues): void
    {
        $request = request();

        AuditLog::create([
            'event' => $event,
            'auditable_type' => $model::class,
            'auditable_id' => (int) $model->getKey(),
            'user_id' => auth()->id(),
            'sector' => $model->getAttribute('sector'),
            'route_name' => $request?->route()?->getName(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'created_at' => now(),
        ]);

        // Keep dashboard/layout KPIs coherent after operational writes.
        $this->invalidateLayoutKpiCache($model->getAttribute('sector'));
    }

    private function sanitize(array $values): array
    {
        unset(
            $values['password'],
            $values['remember_token'],
            $values['updated_at'],
            $values['created_at'],
            $values['deleted_at']
        );

        return $values;
    }

    private function invalidateLayoutKpiCache(?string $sector): void
    {
        $locales = ['ar', 'fr', 'en'];

        $query = User::query()
            ->select(['id'])
            ->where('is_active', true);

        if (!empty($sector)) {
            $query->where(function ($builder) use ($sector): void {
                $builder->where('role', 'super_admin')
                    ->orWhere('sector', $sector);
            });
        }

        $query->chunkById(200, function ($users) use ($locales): void {
            foreach ($users as $user) {
                foreach ($locales as $locale) {
                    Cache::forget("gmao:layout-kpi:user:{$user->id}:locale:{$locale}");
                }
            }
        });
    }
}
