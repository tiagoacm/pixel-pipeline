<?php

use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('dashboard', function (Request $request) {
            $activeStatuses = [
                'nova',
                'validacao',
                'aguardando_cliente',
                'aprovada',
                'em_execucao',
            ];

            $clientName = $request->string('client_name')->trim()->toString();
            $responsibleUserId = $request->filled('responsible_user_id')
                ? (int) $request->input('responsible_user_id')
                : null;

            return Inertia::render('Dashboard', [
                'statusColumns' => [
                    ['key' => 'nova', 'label' => 'Nova'],
                    ['key' => 'validacao', 'label' => 'Validação'],
                    ['key' => 'aguardando_cliente', 'label' => 'Aguardando Cliente'],
                    ['key' => 'aprovada', 'label' => 'Aprovada'],
                    ['key' => 'em_execucao', 'label' => 'Em Execução'],
                ],
                'opportunities' => Opportunity::query()
                    ->with('responsibleUser:id,name')
                    ->whereIn('status', $activeStatuses)
                    ->when($clientName !== '', function ($query) use ($clientName) {
                        $query->where('client_name', 'like', "%{$clientName}%");
                    })
                    ->when($responsibleUserId !== null, function ($query) use ($responsibleUserId) {
                        $query->where('responsible_user_id', $responsibleUserId);
                    })
                    ->latest()
                    ->get()
                    ->map(fn (Opportunity $opportunity) => [
                        'id' => $opportunity->id,
                        'status' => $opportunity->status,
                        'client_name' => $opportunity->client_name,
                        'segment' => $opportunity->segment,
                        'responsible_user_name' => $opportunity->responsibleUser?->name,
                        'created_at' => $opportunity->created_at?->toISOString(),
                    ]),
                'responsibleUsers' => User::query()
                    ->select(['id', 'name'])
                    ->orderBy('name')
                    ->get(),
                'filters' => [
                    'client_name' => $clientName,
                    'responsible_user_id' => $responsibleUserId,
                ],
            ]);
        })->name('dashboard');
    });

Route::middleware(['auth'])->group(function () {
    Route::get('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
});

require __DIR__.'/settings.php';
