<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        return Inertia::render('users/Index', [
            'users' => User::query()
                ->select(['id', 'name', 'email', 'created_at'])
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($nestedQuery) use ($search) {
                        $nestedQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                })
                ->orderBy('name')
                ->get()
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at?->toISOString(),
                ]),
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User created.')]);

        return to_route('users.index');
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        if (($validated['password'] ?? null) === null) {
            unset($validated['password']);
        }

        $user->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User updated.')]);

        return to_route('users.index');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->withErrors([
                'user' => __('You cannot delete your own account from this screen.'),
            ]);
        }

        $user->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User deleted.')]);

        return to_route('users.index');
    }
}
