<?php

namespace App\Http\Controllers;

use App\Http\Requests\Opportunities\StoreOpportunityRequest;
use App\Http\Requests\Opportunities\UpdateOpportunityRequest;
use App\Http\Requests\Opportunities\UpdateOpportunityStatusRequest;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OpportunityController extends Controller
{
    /**
     * Display a listing of opportunities.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();
        $segment = $request->string('segment')->toString();

        return Inertia::render('opportunities/Index', [
            'opportunities' => Opportunity::query()
                ->with(['responsibleUser:id,name'])
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($nestedQuery) use ($search) {
                        $nestedQuery
                            ->where('client_name', 'like', "%{$search}%")
                            ->orWhere('segment', 'like', "%{$search}%")
                            ->orWhere('commercial_contact', 'like', "%{$search}%")
                            ->orWhere('technical_contact', 'like', "%{$search}%");
                    });
                })
                ->when($segment !== '', fn ($query) => $query->where('segment', $segment))
                ->latest()
                ->get()
                ->map(fn (Opportunity $opportunity) => [
                    'id' => $opportunity->id,
                    'status' => $opportunity->status,
                    'client_name' => $opportunity->client_name,
                    'segment' => $opportunity->segment,
                    'commercial_contact' => $opportunity->commercial_contact,
                    'commercial_phone' => $opportunity->commercial_phone,
                    'technical_contact' => $opportunity->technical_contact,
                    'technical_phone' => $opportunity->technical_phone,
                    'opportunity_description' => $opportunity->opportunity_description,
                    'responsible_user_name' => $opportunity->responsibleUser?->name,
                    'created_at' => $opportunity->created_at?->toISOString(),
                ]),
            'segments' => Opportunity::query()
                ->select('segment')
                ->distinct()
                ->orderBy('segment')
                ->pluck('segment'),
            'filters' => [
                'search' => $search,
                'segment' => $segment,
            ],
        ]);
    }

    /**
     * Show the opportunity creation page.
     */
    public function create(): Response
    {
        return Inertia::render('opportunities/Create', [
            'users' => User::query()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Store a newly created opportunity.
     */
    public function store(StoreOpportunityRequest $request): RedirectResponse
    {
        Opportunity::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Opportunity created.')]);

        return to_route('opportunities.index');
    }

    /**
     * Show the opportunity edit page.
     */
    public function edit(Opportunity $opportunity): Response
    {
        return Inertia::render('opportunities/Edit', [
            'opportunity' => [
                'id' => $opportunity->id,
                'status' => $opportunity->status,
                'client_name' => $opportunity->client_name,
                'segment' => $opportunity->segment,
                'commercial_contact' => $opportunity->commercial_contact,
                'commercial_phone' => $opportunity->commercial_phone,
                'technical_contact' => $opportunity->technical_contact,
                'technical_phone' => $opportunity->technical_phone,
                'opportunity_description' => $opportunity->opportunity_description,
                'responsible_user_id' => $opportunity->responsible_user_id,
            ],
            'users' => User::query()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Update the specified opportunity.
     */
    public function update(UpdateOpportunityRequest $request, Opportunity $opportunity): RedirectResponse
    {
        $opportunity->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Opportunity updated.')]);

        return to_route('opportunities.index');
    }

    /**
     * Update only the status of the specified opportunity.
     */
    public function updateStatus(UpdateOpportunityStatusRequest $request, Opportunity $opportunity): RedirectResponse
    {
        $opportunity->update($request->validated());

        return back();
    }

    /**
     * Remove the specified opportunity.
     */
    public function destroy(Opportunity $opportunity): RedirectResponse
    {
        $opportunity->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Opportunity deleted.')]);

        return to_route('opportunities.index');
    }
}
