@extends('layouts.app')
@section('content')
<div class="card p-3 form-shell">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">{{ __('gmao.team.title') }}</h5>
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('team.index') }}">{{ __('gmao.common.open') }}</a>
    </div>

    <form method="POST" action="{{ route('team.store') }}" class="row g-2">
        @csrf
        <div class="col-md-4">
            <input class="form-control" name="name" value="{{ old('name') }}" placeholder="{{ __('gmao.auth.name') }}" required>
        </div>
        <div class="col-md-4">
            <input class="form-control" name="email" type="email" value="{{ old('email') }}" placeholder="{{ __('gmao.auth.email') }}" required>
        </div>
        <div class="col-md-4">
            <div class="input-group">
                <input class="form-control password-field" name="password" type="password" placeholder="{{ __('gmao.auth.password') }}" required>
                <button class="btn btn-outline-secondary toggle-password" type="button">{{ __('gmao.team.show_password') }}</button>
            </div>
        </div>
        <div class="col-md-3">
            <select class="form-select" name="role" required>
                @foreach(['super_admin','manager','technician','employee'] as $role)
                    <option value="{{ $role }}" @selected(old('role', 'employee') === $role)>{{ __('gmao.enum.role.'.$role) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" name="sector">
                <option value="">{{ __('gmao.common.sector') }}</option>
                @foreach($sectors as $sector)
                    <option value="{{ $sector }}" @selected(old('sector') === $sector)>{{ __('gmao.enum.sector.'.$sector) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <input class="form-control" name="phone" value="{{ old('phone') }}" placeholder="{{ __('gmao.auth.phone') }}">
        </div>
        <div class="col-md-3">
            <input class="form-control" name="job_title" value="{{ old('job_title') }}" placeholder="{{ __('gmao.auth.job_title') }}">
        </div>
        <div class="col-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', '1') == '1')>
                <label class="form-check-label" for="is_active">{{ __('gmao.common.active') }}</label>
            </div>
        </div>
        <div class="col-12">
            <button class="btn btn-primary">{{ __('gmao.common.save') }}</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.toggle-password').forEach((btn) => {
    btn.addEventListener('click', () => {
        const input = btn.closest('.input-group')?.querySelector('.password-field');
        if (!input) return;
        input.type = input.type === 'password' ? 'text' : 'password';
    });
});
</script>
@endpush
