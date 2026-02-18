@extends('layouts.app')
@section('content')
<div class="card p-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">{{ __('gmao.team.accounts') }}</h5>
        @if(auth()->user()->isRole('super_admin'))
            <a class="action-icon" href="{{ route('team.create') }}">
                <i class="bi bi-person-plus"></i>
                <span>{{ __('gmao.common.add') }}</span>
            </a>
        @endif
    </div>
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead>
                <tr>
                    <th>{{ __('gmao.auth.name') }}</th>
                    <th>{{ __('gmao.auth.email') }}</th>
                    <th>{{ __('gmao.common.role') }}</th>
                    <th>{{ __('gmao.common.sector') }}</th>
                    <th>{{ __('gmao.auth.job_title') }}</th>
                    <th>{{ __('gmao.common.status') }}</th>
                    @if(auth()->user()->isRole('super_admin'))
                        <th>{{ __('gmao.common.actions') }}</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ __('gmao.enum.role.'.$u->role) }}</td>
                        <td>{{ $u->sector ? __('gmao.enum.sector.'.$u->sector) : '-' }}</td>
                        <td>{{ $u->job_title ?: '-' }}</td>
                        <td>{{ $u->is_active ? __('gmao.enum.status.active') : __('gmao.enum.status.stopped') }}</td>
                        @if(auth()->user()->isRole('super_admin'))
                            <td class="d-flex gap-1">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('team.edit', $u) }}">{{ __('gmao.common.edit') }}</a>
                                @if(auth()->id() !== $u->id)
                                    <form method="POST" action="{{ route('team.destroy', $u) }}" onsubmit="return confirm('{{ __('gmao.common.confirm_delete') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">{{ __('gmao.common.delete') }}</button>
                                    </form>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">{{ __('gmao.common.none') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $users->links() }}
</div>
@endsection
