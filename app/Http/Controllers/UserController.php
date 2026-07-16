<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', User::class);

        $users = User::query()
            ->with('roles:id,name')
            ->orderBy('surname')
            ->orderBy('name')
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->roles->first()?->name,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
            ]);

        return Inertia::render('users/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        Gate::authorize('create', User::class);

        return Inertia::render('users/Create', [
            'roles' => $this->roleOptions(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::query()->create([
            ...Arr::except($validated, ['role', 'password_confirmation']),
            'email_verified_at' => now(),
        ]);

        $user->syncRoles([$validated['role']]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User created.')]);

        return to_route('users.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): Response
    {
        Gate::authorize('update', $user);

        $user->load('roles:id,name');

        return Inertia::render('users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'email' => $user->email,
                'role' => $user->roles->first()?->name ?? Role::User->value,
            ],
            'roles' => $this->roleOptions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $user->fill(Arr::except($validated, ['role', 'password', 'password_confirmation']));

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
        $user->syncRoles([$validated['role']]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User updated.')]);

        return to_route('users.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $user->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User deleted.')]);

        return to_route('users.index');
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function roleOptions(): array
    {
        return [
            ['value' => Role::Admin->value, 'label' => 'Admin'],
            ['value' => Role::User->value, 'label' => 'User'],
        ];
    }
}
