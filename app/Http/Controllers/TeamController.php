<?php

namespace App\Http\Controllers;

use App\Http\Requests\Team\StoreTeamUserRequest;
use App\Http\Requests\Team\UpdateTeamUserRequest;
use App\Models\User;
use App\Support\GmaoOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $viewer = auth()->user();
        $query = User::query()->orderBy('role');
        if ($viewer && !$viewer->isRole('super_admin') && !empty($viewer->sector)) {
            $query->where('sector', $viewer->sector);
        }

        return view('team.index', [
            'users' => $query->paginate(20),
            'sectors' => GmaoOptions::SECTORS,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('team.create', [
            'sectors' => GmaoOptions::SECTORS,
        ]);
    }

    public function store(StoreTeamUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validated();

        User::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return back()->with('success', __('gmao.msg.user_created'));
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('team.edit', [
            'user' => $user,
            'sectors' => GmaoOptions::SECTORS,
        ]);
    }

    public function update(UpdateTeamUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validated();
        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'sector' => $validated['sector'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ];

        if (!empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $user->update($payload);

        return redirect()->route('team.index')->with('success', __('gmao.msg.user_updated'));
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        if (auth()->id() === $user->id) {
            return back()->withErrors(['general' => __('gmao.msg.cannot_delete_self')]);
        }

        if ($user->role === 'super_admin') {
            $otherSuperAdmins = User::query()
                ->where('role', 'super_admin')
                ->whereKeyNot($user->id)
                ->count();

            if ($otherSuperAdmins === 0) {
                return back()->withErrors(['general' => __('gmao.msg.cannot_delete_last_super_admin')]);
            }
        }

        $user->delete();

        return back()->with('success', __('gmao.msg.user_deleted'));
    }
}

