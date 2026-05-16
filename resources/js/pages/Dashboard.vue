<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { CalendarDays, CheckCircle2, Clock3, Filter, FolderKanban, Plus, UserRound } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetFooter,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { dashboard } from '@/routes';
import opportunitiesRoutes, { create, edit, updateStatus } from '@/routes/opportunities';
import type { Team } from '@/types';

type KanbanColumn = {
    key: string;
    label: string;
};

type DashboardOpportunity = {
    id: number;
    status: string;
    client_name: string;
    segment: string;
    responsible_user_name?: string | null;
    created_at?: string | null;
};

type ResponsibleUserOption = {
    id: number;
    name: string;
};

type Props = {
    statusColumns: KanbanColumn[];
    opportunities: DashboardOpportunity[];
    responsibleUsers: ResponsibleUserOption[];
    filters: {
        client_name: string;
        responsible_user_id: number | null;
    };
    currentTeam?: Team | null;
};

const props = defineProps<Props>();
const localOpportunities = ref<DashboardOpportunity[]>([...props.opportunities]);
const draggingOpportunityId = ref<number | null>(null);
const dragOverColumnKey = ref<string | null>(null);
const updatingOpportunityIds = ref<number[]>([]);
const isFiltersOpen = ref(false);
const clientNameFilter = ref(props.filters.client_name ?? '');
const responsibleUserFilter = ref(
    props.filters.responsible_user_id ? String(props.filters.responsible_user_id) : '__all__',
);

watch(
    () => props.opportunities,
    (nextOpportunities) => {
        localOpportunities.value = [...nextOpportunities];
    },
);

watch(
    () => props.filters,
    (nextFilters) => {
        clientNameFilter.value = nextFilters.client_name ?? '';
        responsibleUserFilter.value = nextFilters.responsible_user_id
            ? String(nextFilters.responsible_user_id)
            : '__all__';
    },
    { deep: true },
);

const groupedOpportunities = computed(() => {
    const columns = Object.fromEntries(
        props.statusColumns.map((column) => [column.key, [] as DashboardOpportunity[]]),
    );

    for (const opportunity of localOpportunities.value) {
        if (!columns[opportunity.status]) {
            columns[opportunity.status] = [];
        }

        columns[opportunity.status].push(opportunity);
    }

    return columns;
});

const displayedColumns = computed(() => {
    return props.statusColumns.map((column) => ({
        ...column,
        opportunities: groupedOpportunities.value[column.key] ?? [],
    }));
});

const totalOpportunities = computed(() => localOpportunities.value.length);
const waitingClientOpportunities = computed(
    () => localOpportunities.value.filter((opportunity) => opportunity.status === 'aguardando_cliente').length,
);
const approvedOpportunities = computed(
    () => localOpportunities.value.filter((opportunity) => opportunity.status === 'aprovada').length,
);
const activeFiltersCount = computed(() => {
    let count = 0;

    if (clientNameFilter.value.trim() !== '') {
        count++;
    }

    if (responsibleUserFilter.value !== '__all__') {
        count++;
    }

    return count;
});

function applyFilters(): void {
    if (!props.currentTeam) {
        return;
    }

    router.get(
        dashboard(props.currentTeam.slug).url,
        {
            client_name: clientNameFilter.value.trim(),
            responsible_user_id:
                responsibleUserFilter.value === '__all__'
                    ? ''
                    : Number(responsibleUserFilter.value),
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onSuccess: () => {
                isFiltersOpen.value = false;
            },
        },
    );
}

function clearFilters(): void {
    clientNameFilter.value = '';
    responsibleUserFilter.value = '__all__';
    applyFilters();
}

function onDragStart(opportunityId: number): void {
    draggingOpportunityId.value = opportunityId;
}

function onDragEnd(): void {
    draggingOpportunityId.value = null;
    dragOverColumnKey.value = null;
}

function onDragOver(columnKey: string, event: DragEvent): void {
    event.preventDefault();
    dragOverColumnKey.value = columnKey;
}

function onDrop(columnKey: string, event: DragEvent): void {
    event.preventDefault();

    if (!draggingOpportunityId.value) {
        return;
    }

    const draggedOpportunityId = draggingOpportunityId.value;
    const targetOpportunity = localOpportunities.value.find(
        (opportunity) => opportunity.id === draggedOpportunityId,
    );

    if (!targetOpportunity) {
        onDragEnd();

        return;
    }

    const previousStatus = targetOpportunity.status;

    if (previousStatus === columnKey || updatingOpportunityIds.value.includes(draggedOpportunityId)) {
        onDragEnd();

        return;
    }

    targetOpportunity.status = columnKey;
    updatingOpportunityIds.value = [...updatingOpportunityIds.value, draggedOpportunityId];

    router.patch(
        updateStatus(draggedOpportunityId).url,
        { status: columnKey },
        {
            preserveScroll: true,
            preserveState: true,
            onError: () => {
                const opportunityToRollback = localOpportunities.value.find(
                    (opportunity) => opportunity.id === draggedOpportunityId,
                );

                if (opportunityToRollback) {
                    opportunityToRollback.status = previousStatus;
                }
            },
            onFinish: () => {
                updatingOpportunityIds.value = updatingOpportunityIds.value.filter(
                    (opportunityId) => opportunityId !== draggedOpportunityId,
                );
            },
        },
    );

    onDragEnd();
}

function formatDate(dateValue?: string | null): string {
    if (!dateValue) {
        return 'Data indisponível';
    }

    return new Intl.DateTimeFormat('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    }).format(new Date(dateValue));
}

function statusTone(status: string): string {
    const tones: Record<string, string> = {
        nova: 'bg-sky-100 text-sky-700 dark:bg-sky-900/50 dark:text-sky-200',
        validacao: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-200',
        aguardando_cliente: 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-200',
        aprovada: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-200',
        em_execucao: 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/50 dark:text-cyan-200',
        finalizada: 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-200',
        reprovada: 'bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-200',
        cancelada: 'bg-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200',
    };

    return tones[status] ?? 'bg-muted text-muted-foreground';
}

defineOptions({
    layout: (props: { currentTeam?: Team | null }) => ({
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: props.currentTeam ? dashboard(props.currentTeam.slug) : '/',
            },
        ],
    }),
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="relative flex h-full flex-1 flex-col gap-6 overflow-hidden rounded-xl p-4 md:p-6">
        <div
            class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(70%_40%_at_10%_10%,rgba(14,165,233,0.15),transparent),radial-gradient(45%_30%_at_90%_5%,rgba(16,185,129,0.16),transparent)]"
        />

        <Card class="border-border/70">
            <CardHeader class="space-y-4">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="space-y-1">
                        <CardTitle class="flex items-center gap-2 text-2xl tracking-tight">
                            <FolderKanban class="h-6 w-6 text-cyan-600 dark:text-cyan-300" />
                            Kanban de Oportunidades
                        </CardTitle>
                        <CardDescription>
                            Visualize rapidamente o pipeline comercial por situação e priorize as próximas ações.
                        </CardDescription>
                    </div>

                    <div class="flex items-center gap-2">
                        <Sheet v-model:open="isFiltersOpen">
                            <SheetTrigger as-child>
                                <Button variant="outline" class="relative">
                                    <Filter class="h-4 w-4" />
                                    Filtros
                                    <span
                                        v-if="activeFiltersCount > 0"
                                        class="bg-primary text-primary-foreground ml-1 inline-flex min-w-5 items-center justify-center rounded-full px-1.5 text-[10px] font-semibold"
                                    >
                                        {{ activeFiltersCount }}
                                    </span>
                                </Button>
                            </SheetTrigger>

                            <SheetContent
                                side="right"
                                class="w-full sm:max-w-md"
                                overlay-class="bg-black/40 backdrop-blur-[1px]"
                            >
                                <SheetHeader>
                                    <SheetTitle>Filtrar Kanban</SheetTitle>
                                    <SheetDescription>
                                        Refine por cliente e responsável sem ocupar espaço da grade.
                                    </SheetDescription>
                                </SheetHeader>

                                <div class="mt-2 space-y-5 px-4 pb-2">
                                    <div class="grid gap-2">
                                        <Label for="client_name_filter">Cliente</Label>
                                        <Input
                                            id="client_name_filter"
                                            v-model="clientNameFilter"
                                            placeholder="Digite o nome do cliente"
                                        />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="responsible_user_filter">Responsável</Label>
                                        <Select v-model="responsibleUserFilter">
                                            <SelectTrigger id="responsible_user_filter" class="w-full">
                                                <SelectValue placeholder="Todos os responsáveis" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="__all__">Todos os responsáveis</SelectItem>
                                                <SelectItem
                                                    v-for="user in props.responsibleUsers"
                                                    :key="user.id"
                                                    :value="String(user.id)"
                                                >
                                                    {{ user.name }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>

                                <SheetFooter class="mt-6">
                                    <Button variant="secondary" @click="clearFilters">Limpar</Button>
                                    <Button @click="applyFilters">Aplicar</Button>
                                </SheetFooter>
                            </SheetContent>
                        </Sheet>

                        <Button variant="outline" as-child>
                            <a :href="opportunitiesRoutes.index().url">Ver lista completa</a>
                        </Button>
                        <Button as-child>
                            <a :href="create().url">
                                <Plus class="h-4 w-4" />
                                Nova oportunidade
                            </a>
                        </Button>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="rounded-lg border border-border/70 bg-card px-4 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs uppercase tracking-[0.16em] text-muted-foreground">Total Oportunidades</p>
                                <p class="text-2xl font-semibold">{{ totalOpportunities }}</p>
                            </div>
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-900/50 dark:text-cyan-200">
                                <FolderKanban class="h-4 w-4" />
                            </span>
                        </div>
                    </div>
                    <div class="rounded-lg border border-border/70 bg-card px-4 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs uppercase tracking-[0.16em] text-muted-foreground">Aguardando Cliente</p>
                                <p class="text-2xl font-semibold">{{ waitingClientOpportunities }}</p>
                            </div>
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-200">
                                <Clock3 class="h-4 w-4" />
                            </span>
                        </div>
                    </div>
                    <div class="rounded-lg border border-border/70 bg-card px-4 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs uppercase tracking-[0.16em] text-muted-foreground">Aprovadas</p>
                                <p class="text-2xl font-semibold">{{ approvedOpportunities }}</p>
                            </div>
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-200">
                                <CheckCircle2 class="h-4 w-4" />
                            </span>
                        </div>
                    </div>
                </div>
            </CardHeader>
        </Card>

        <div
            v-if="totalOpportunities === 0"
            class="rounded-lg border border-dashed border-border/70 bg-card/70 p-10 text-center"
        >
            <h2 class="text-xl font-semibold">Sem oportunidades no pipeline</h2>
            <p class="mt-2 text-sm text-muted-foreground">
                Cadastre a primeira oportunidade para começar a organizar o fluxo no Kanban.
            </p>
            <Button class="mt-4" as-child>
                <a :href="create().url">Criar oportunidade</a>
            </Button>
        </div>

        <div v-else class="-mx-2 overflow-x-auto px-2 pb-3">
            <div class="flex min-w-max flex-nowrap gap-4">
                <div
                    v-for="column in displayedColumns"
                    :key="column.key"
                    class="w-[20rem] shrink-0 rounded-xl border border-border/70 bg-card p-3 shadow-sm"
                    :class="{
                        'border-cyan-400/80 ring-1 ring-cyan-300/60': dragOverColumnKey === column.key,
                    }"
                    @dragover="onDragOver(column.key, $event)"
                    @dragleave="dragOverColumnKey = null"
                    @drop="onDrop(column.key, $event)"
                >
                    <div class="mb-3 flex items-center justify-between border-b border-border/60 pb-2">
                        <h3 class="text-sm font-semibold uppercase tracking-[0.12em] text-muted-foreground">
                            {{ column.label }}
                        </h3>
                        <Badge variant="secondary">{{ column.opportunities.length }}</Badge>
                    </div>

                    <div class="space-y-3">
                        <article
                            v-for="opportunity in column.opportunities"
                            :key="opportunity.id"
                            draggable="true"
                            class="group cursor-grab rounded-lg border border-border/80 bg-background p-3 transition-all hover:-translate-y-0.5 hover:border-cyan-500/50 hover:shadow active:cursor-grabbing"
                            :class="{
                                'opacity-50': draggingOpportunityId === opportunity.id,
                            }"
                            @dragstart="onDragStart(opportunity.id)"
                            @dragend="onDragEnd"
                        >
                            <div class="mb-2 flex items-start justify-between gap-2">
                                <h4 class="line-clamp-2 text-sm font-semibold leading-snug">
                                    {{ opportunity.client_name }}
                                </h4>
                                <span
                                    class="rounded-full px-2 py-0.5 text-[11px] font-medium"
                                    :class="statusTone(opportunity.status)"
                                >
                                    {{ opportunity.segment }}
                                </span>
                            </div>

                            <p class="mb-3 flex items-center gap-1.5 text-xs text-muted-foreground">
                                <UserRound class="h-3.5 w-3.5" />
                                {{ opportunity.responsible_user_name ?? 'Responsável não definido' }}
                            </p>

                            <div class="flex items-center justify-between text-xs text-muted-foreground">
                                <span class="inline-flex items-center gap-1.5">
                                    <CalendarDays class="h-3.5 w-3.5" />
                                    {{ formatDate(opportunity.created_at) }}
                                </span>

                                <a
                                    :href="edit(opportunity.id).url"
                                    class="font-medium text-cyan-700 opacity-0 transition group-hover:opacity-100 dark:text-cyan-300"
                                >
                                    {{ updatingOpportunityIds.includes(opportunity.id) ? 'Salvando...' : 'Abrir' }}
                                </a>
                            </div>
                        </article>

                        <div
                            v-if="column.opportunities.length === 0"
                            class="rounded-lg border border-dashed border-border/70 px-3 py-6 text-center text-xs text-muted-foreground"
                        >
                            Nenhuma oportunidade nesta situação.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
