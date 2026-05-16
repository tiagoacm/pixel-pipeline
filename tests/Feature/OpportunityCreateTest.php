<?php

use App\Models\Opportunity;
use App\Models\User;

test('the opportunity index page can be rendered', function () {
    $this->withoutVite();

    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('opportunities.index'));

    $response->assertSuccessful();
});

test('the opportunity create page can be rendered', function () {
    $this->withoutVite();

    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('opportunities.create'));

    $response->assertSuccessful();
});

test('opportunities can be created', function () {
    $this->withoutVite();

    $user = User::factory()->create();
    $responsibleUser = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('opportunities.store'), [
            'client_name' => 'Empresa Exemplo',
            'segment' => 'Tecnologia',
            'commercial_contact' => 'Ana Comercial',
            'commercial_phone' => '(11) 99999-9999',
            'technical_contact' => 'Carlos Tecnico',
            'technical_phone' => '(11) 98888-8888',
            'opportunity_description' => 'Implementacao de novo portal B2B.',
            'responsible_user_id' => $responsibleUser->id,
        ]);

    $response->assertRedirect(route('opportunities.index'));

    $this->assertDatabaseHas('opportunities', [
        'client_name' => 'Empresa Exemplo',
        'status' => 'nova',
        'segment' => 'Tecnologia',
        'commercial_contact' => 'Ana Comercial',
        'commercial_phone' => '(11) 99999-9999',
        'technical_contact' => 'Carlos Tecnico',
        'technical_phone' => '(11) 98888-8888',
        'responsible_user_id' => $responsibleUser->id,
    ]);
});

test('opportunities index can be filtered by search and segment', function () {
    $this->withoutVite();

    $user = User::factory()->create();
    $responsibleUser = User::factory()->create();

    Opportunity::query()->create([
        'client_name' => 'Acme Indústria',
        'segment' => 'Industria',
        'commercial_contact' => 'Marina',
        'commercial_phone' => '(11) 90000-0001',
        'technical_contact' => 'Paulo',
        'technical_phone' => '(11) 90000-0002',
        'opportunity_description' => 'Projeto industrial.',
        'responsible_user_id' => $responsibleUser->id,
    ]);

    Opportunity::query()->create([
        'client_name' => 'Beta Saúde',
        'segment' => 'Saude',
        'commercial_contact' => 'Lia',
        'commercial_phone' => '(11) 90000-0003',
        'technical_contact' => 'Vitor',
        'technical_phone' => '(11) 90000-0004',
        'opportunity_description' => 'Projeto de saude.',
        'responsible_user_id' => $responsibleUser->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('opportunities.index', [
            'search' => 'Acme',
            'segment' => 'Industria',
        ]));

    $response->assertSuccessful();
    $response->assertSee('Acme Indústria');
    $response->assertDontSee('Beta Saúde');
});

test('the opportunity edit page can be rendered', function () {
    $this->withoutVite();

    $user = User::factory()->create();
    $responsibleUser = User::factory()->create();
    $opportunity = Opportunity::query()->create([
        'client_name' => 'Cliente Inicial',
        'segment' => 'Industria',
        'commercial_contact' => 'Contato Comercial',
        'commercial_phone' => '(11) 91111-1111',
        'technical_contact' => 'Contato Tecnico',
        'technical_phone' => '(11) 92222-2222',
        'opportunity_description' => 'Descricao inicial.',
        'responsible_user_id' => $responsibleUser->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('opportunities.edit', $opportunity));

    $response->assertSuccessful();
});

test('opportunities can be updated', function () {
    $this->withoutVite();

    $user = User::factory()->create();
    $responsibleUser = User::factory()->create();
    $newResponsibleUser = User::factory()->create();

    $opportunity = Opportunity::query()->create([
        'client_name' => 'Cliente Inicial',
        'segment' => 'Industria',
        'commercial_contact' => 'Contato Comercial',
        'commercial_phone' => '(11) 91111-1111',
        'technical_contact' => 'Contato Tecnico',
        'technical_phone' => '(11) 92222-2222',
        'opportunity_description' => 'Descricao inicial.',
        'responsible_user_id' => $responsibleUser->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->patch(route('opportunities.update', $opportunity), [
            'client_name' => 'Cliente Atualizado',
            'segment' => 'Tecnologia',
            'commercial_contact' => 'Novo Comercial',
            'commercial_phone' => '(11) 93333-3333',
            'technical_contact' => 'Novo Tecnico',
            'technical_phone' => '(11) 94444-4444',
            'opportunity_description' => 'Descricao atualizada.',
            'responsible_user_id' => $newResponsibleUser->id,
        ]);

    $response->assertRedirect(route('opportunities.index'));

    $this->assertDatabaseHas('opportunities', [
        'id' => $opportunity->id,
        'client_name' => 'Cliente Atualizado',
        'segment' => 'Tecnologia',
        'commercial_contact' => 'Novo Comercial',
        'commercial_phone' => '(11) 93333-3333',
        'technical_contact' => 'Novo Tecnico',
        'technical_phone' => '(11) 94444-4444',
        'opportunity_description' => 'Descricao atualizada.',
        'responsible_user_id' => $newResponsibleUser->id,
    ]);
});

test('opportunities can be deleted', function () {
    $this->withoutVite();

    $user = User::factory()->create();
    $responsibleUser = User::factory()->create();

    $opportunity = Opportunity::query()->create([
        'client_name' => 'Cliente Para Excluir',
        'segment' => 'Servicos',
        'commercial_contact' => 'Comercial Excluir',
        'commercial_phone' => '(11) 95555-5555',
        'technical_contact' => 'Tecnico Excluir',
        'technical_phone' => '(11) 96666-6666',
        'opportunity_description' => 'Descricao para exclusao.',
        'responsible_user_id' => $responsibleUser->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->delete(route('opportunities.destroy', $opportunity));

    $response->assertRedirect(route('opportunities.index'));

    $this->assertDatabaseMissing('opportunities', [
        'id' => $opportunity->id,
    ]);
});

test('opportunity status can be updated independently', function () {
    $this->withoutVite();

    $user = User::factory()->create();
    $responsibleUser = User::factory()->create();

    $opportunity = Opportunity::query()->create([
        'status' => 'nova',
        'client_name' => 'Cliente Drag Drop',
        'segment' => 'Servicos',
        'commercial_contact' => 'Comercial',
        'commercial_phone' => '(11) 97777-7777',
        'technical_contact' => 'Tecnico',
        'technical_phone' => '(11) 98888-8888',
        'opportunity_description' => 'Descricao para alterar status.',
        'responsible_user_id' => $responsibleUser->id,
    ]);

    $this
        ->actingAs($user)
        ->patch(route('opportunities.update-status', $opportunity), [
            'status' => 'em_execucao',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('opportunities', [
        'id' => $opportunity->id,
        'status' => 'em_execucao',
    ]);
});
