<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('the users index page can be rendered', function () {
    $this->withoutVite();

    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('users.index'));

    $response->assertSuccessful();
});

test('users can be created', function () {
    $this->withoutVite();

    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('users.store'), [
            'name' => 'Maria Silva',
            'email' => 'maria.silva@example.com',
            'password' => 'nova-senha-123',
            'password_confirmation' => 'nova-senha-123',
        ]);

    $response->assertRedirect(route('users.index'));

    $createdUser = User::query()->where('email', 'maria.silva@example.com')->first();

    expect($createdUser)->not()->toBeNull();
    expect(Hash::check('nova-senha-123', $createdUser->password))->toBeTrue();
});

test('users can be updated', function () {
    $this->withoutVite();

    $authenticatedUser = User::factory()->create();
    $managedUser = User::factory()->create([
        'name' => 'Nome Antigo',
        'email' => 'antigo@example.com',
    ]);

    $response = $this
        ->actingAs($authenticatedUser)
        ->patch(route('users.update', $managedUser), [
            'name' => 'Nome Novo',
            'email' => 'novo@example.com',
            'password' => 'senha-atualizada-123',
            'password_confirmation' => 'senha-atualizada-123',
        ]);

    $response->assertRedirect(route('users.index'));

    $managedUser->refresh();

    expect($managedUser->name)->toBe('Nome Novo');
    expect($managedUser->email)->toBe('novo@example.com');
    expect(Hash::check('senha-atualizada-123', $managedUser->password))->toBeTrue();
});

test('users can be deleted', function () {
    $this->withoutVite();

    $authenticatedUser = User::factory()->create();
    $managedUser = User::factory()->create();

    $response = $this
        ->actingAs($authenticatedUser)
        ->delete(route('users.destroy', $managedUser));

    $response->assertRedirect(route('users.index'));

    $this->assertDatabaseMissing('users', [
        'id' => $managedUser->id,
    ]);
});

test('a user cannot delete their own account from users crud', function () {
    $this->withoutVite();

    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from(route('users.index'))
        ->delete(route('users.destroy', $user));

    $response->assertRedirect(route('users.index'));
    $response->assertSessionHasErrors('user');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
    ]);
});
