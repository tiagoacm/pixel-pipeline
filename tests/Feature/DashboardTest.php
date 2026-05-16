<?php

use App\Models\Opportunity;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $user = User::factory()->create();

    $response = $this->get(route('dashboard', ['current_team' => $user->currentTeam->slug]));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->get(route('dashboard', ['current_team' => $user->currentTeam->slug]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('statusColumns', 5)
            ->has('opportunities'),
        );
});

test('dashboard groups opportunities by status', function () {
    $this->withoutVite();

    $user = User::factory()->create();
    $responsibleUser = User::factory()->create();

    Opportunity::query()->create([
        'status' => 'nova',
        'client_name' => 'Cliente Kanban Novo',
        'segment' => 'Tecnologia',
        'commercial_contact' => 'Alice',
        'commercial_phone' => '(11) 90000-0001',
        'technical_contact' => 'Bruno',
        'technical_phone' => '(11) 90000-0002',
        'opportunity_description' => 'Descricao de oportunidade nova.',
        'responsible_user_id' => $responsibleUser->id,
    ]);

    Opportunity::query()->create([
        'status' => 'em_execucao',
        'client_name' => 'Cliente Kanban Execucao',
        'segment' => 'Servicos',
        'commercial_contact' => 'Carla',
        'commercial_phone' => '(11) 90000-0003',
        'technical_contact' => 'Diego',
        'technical_phone' => '(11) 90000-0004',
        'opportunity_description' => 'Descricao de oportunidade em execucao.',
        'responsible_user_id' => $responsibleUser->id,
    ]);

    $this
        ->actingAs($user)
        ->get(route('dashboard', ['current_team' => $user->currentTeam->slug]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('opportunities', 2)
            ->where('opportunities', fn ($opportunities) => collect($opportunities)
                ->contains(fn (array $opportunity) => $opportunity['client_name'] === 'Cliente Kanban Novo' && $opportunity['status'] === 'nova')
                && collect($opportunities)
                    ->contains(fn (array $opportunity) => $opportunity['client_name'] === 'Cliente Kanban Execucao' && $opportunity['status'] === 'em_execucao')),
        );
});

test('dashboard can be filtered by client and responsible user', function () {
    $this->withoutVite();

    $user = User::factory()->create();
    $responsibleA = User::factory()->create();
    $responsibleB = User::factory()->create();

    Opportunity::query()->create([
        'status' => 'nova',
        'client_name' => 'Cliente Filtro A',
        'segment' => 'Tecnologia',
        'commercial_contact' => 'Alice',
        'commercial_phone' => '(11) 90000-1001',
        'technical_contact' => 'Bruno',
        'technical_phone' => '(11) 90000-1002',
        'opportunity_description' => 'Oportunidade A.',
        'responsible_user_id' => $responsibleA->id,
    ]);

    Opportunity::query()->create([
        'status' => 'nova',
        'client_name' => 'Cliente Filtro B',
        'segment' => 'Servicos',
        'commercial_contact' => 'Carla',
        'commercial_phone' => '(11) 90000-2001',
        'technical_contact' => 'Diego',
        'technical_phone' => '(11) 90000-2002',
        'opportunity_description' => 'Oportunidade B.',
        'responsible_user_id' => $responsibleB->id,
    ]);

    $this
        ->actingAs($user)
        ->get(route('dashboard', [
            'current_team' => $user->currentTeam->slug,
            'client_name' => 'Filtro A',
            'responsible_user_id' => $responsibleA->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('opportunities', 1)
            ->where('opportunities.0.client_name', 'Cliente Filtro A')
            ->where('filters.client_name', 'Filtro A')
            ->where('filters.responsible_user_id', $responsibleA->id)
            ->has('responsibleUsers'),
        );
});
