<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { CalendarDays, Pencil, Plus, Search, Trash2, UserRound } from 'lucide-vue-next';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import opportunities, { create, destroy, edit } from '@/routes/opportunities';

type OpportunityCard = {
    id: number;
    status: string;
    client_name: string;
    segment: string;
    commercial_contact: string;
    commercial_phone: string;
    technical_contact: string;
    technical_phone: string;
    opportunity_description: string;
    responsible_user_name?: string | null;
    created_at?: string | null;
};

type Props = {
    opportunities: OpportunityCard[];
    segments: string[];
    filters: {
        search: string;
        segment: string;
    };
};

const props = defineProps<Props>();
const isDeleteDialogOpen = ref(false);
const selectedOpportunity = ref<OpportunityCard | null>(null);

const filterForm = useForm({
    search: props.filters.search,
    segment: props.filters.segment || '__all__',
});

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Oportunidades',
                href: opportunities.index(),
            },
        ],
    },
});

function applyFilters(): void {
    router.get(
        opportunities.index().url,
        {
            search: filterForm.search,
            segment: filterForm.segment === '__all__' ? '' : filterForm.segment,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function clearFilters(): void {
    filterForm.search = '';
    filterForm.segment = '__all__';
    applyFilters();
}

function openDeleteDialog(opportunity: OpportunityCard): void {
    selectedOpportunity.value = opportunity;
    isDeleteDialogOpen.value = true;
}

function confirmDelete(): void {
    if (!selectedOpportunity.value) {
        return;
    }

    router.delete(destroy(selectedOpportunity.value.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            isDeleteDialogOpen.value = false;
            selectedOpportunity.value = null;
        },
    });
}

function statusLabel(status: string): string {
    const labels: Record<string, string> = {
        nova: 'Nova',
        validacao: 'Validação',
        aguardando_cliente: 'Aguardando Cliente',
        aprovada: 'Aprovada',
        em_execucao: 'Em Execução',
        finalizada: 'Finalizada',
        reprovada: 'Reprovada',
        cancelada: 'Cancelada',
    };

    return labels[status] ?? status;
}

function statusBadgeClass(status: string): string {
    const classes: Record<string, string> = {
        nova: 'border-sky-300 bg-sky-50 text-sky-700 dark:border-sky-700/60 dark:bg-sky-900/30 dark:text-sky-300',
        validacao:
            'border-violet-300 bg-violet-50 text-violet-700 dark:border-violet-700/60 dark:bg-violet-900/30 dark:text-violet-300',
        aguardando_cliente:
            'border-amber-300 bg-amber-50 text-amber-700 dark:border-amber-700/60 dark:bg-amber-900/30 dark:text-amber-300',
        aprovada:
            'border-emerald-300 bg-emerald-50 text-emerald-700 dark:border-emerald-700/60 dark:bg-emerald-900/30 dark:text-emerald-300',
        em_execucao:
            'border-cyan-300 bg-cyan-50 text-cyan-700 dark:border-cyan-700/60 dark:bg-cyan-900/30 dark:text-cyan-300',
        finalizada:
            'border-green-300 bg-green-50 text-green-700 dark:border-green-700/60 dark:bg-green-900/30 dark:text-green-300',
        reprovada:
            'border-rose-300 bg-rose-50 text-rose-700 dark:border-rose-700/60 dark:bg-rose-900/30 dark:text-rose-300',
        cancelada:
            'border-zinc-300 bg-zinc-100 text-zinc-700 dark:border-zinc-700/60 dark:bg-zinc-800/70 dark:text-zinc-300',
    };

    return classes[status] ?? 'border-border bg-muted text-muted-foreground';
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
</script>

<template>
    <Head title="Oportunidades" />

    <div class="mx-auto flex w-full max-w-7xl flex-col space-y-6 px-4 py-5 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <Heading
                variant="small"
                title="Oportunidades"
                description="Gerencie as oportunidades cadastradas"
            />

            <Button as-child>
                <Link :href="create()">
                    <Plus class="h-4 w-4" />
                    Cadastrar nova oportunidade
                </Link>
            </Button>
        </div>

        <Card>
            <CardHeader class="space-y-2 pb-4">
                <CardTitle>Filtros rápidos</CardTitle>
                <CardDescription>
                    Busque por cliente, segmento ou contato e refine o resultado por segmento.
                </CardDescription>
            </CardHeader>
            <CardContent class="pt-0">
                <form
                    class="grid gap-5 md:grid-cols-[1.4fr_1fr_auto_auto] md:items-end"
                    @submit.prevent="applyFilters"
                >
                    <div class="grid gap-2">
                        <Label for="search">Busca</Label>
                        <div class="relative">
                            <Search class="text-muted-foreground absolute top-2.5 left-3 h-4 w-4" />
                            <Input
                                id="search"
                                v-model="filterForm.search"
                                placeholder="Cliente, segmento ou contato"
                                class="pl-9"
                            />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="segment-filter">Segmento</Label>
                        <Select v-model="filterForm.segment" name="segment">
                            <SelectTrigger id="segment-filter" class="w-full">
                                <SelectValue placeholder="Todos os segmentos" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="__all__">Todos os segmentos</SelectItem>
                                <SelectItem
                                    v-for="segment in props.segments"
                                    :key="segment"
                                    :value="segment"
                                >
                                    {{ segment }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <Button type="submit">Aplicar</Button>
                    <Button type="button" variant="secondary" @click="clearFilters">
                        Limpar
                    </Button>
                </form>
            </CardContent>
        </Card>

        <div
            v-if="props.opportunities.length === 0"
            class="rounded-lg border border-dashed p-8 text-center text-muted-foreground"
        >
            Nenhuma oportunidade cadastrada até o momento.
        </div>

        <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <Card
                v-for="opportunity in props.opportunities"
                :key="opportunity.id"
                class="h-full border-l-4 border-l-primary/60"
            >
                <CardHeader class="space-y-2 pb-0">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <CardTitle class="text-base leading-tight">
                                {{ opportunity.client_name }}
                            </CardTitle>
                            <CardDescription>
                                Segmento: {{ opportunity.segment }}
                            </CardDescription>
                        </div>

                        <Badge variant="outline" :class="statusBadgeClass(opportunity.status)">
                            {{ statusLabel(opportunity.status) }}
                        </Badge>
                    </div>
                </CardHeader>

                <CardContent class="space-y-3 pt-0 text-sm">
                    <div class="grid gap-2">
                        <p>
                            <span class="font-medium">Contato comercial:</span>
                            {{ opportunity.commercial_contact }}
                        </p>
                        <p class="text-muted-foreground">
                            {{ opportunity.commercial_phone }}
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <p>
                            <span class="font-medium">Contato técnico:</span>
                            {{ opportunity.technical_contact }}
                        </p>
                        <p class="text-muted-foreground">
                            {{ opportunity.technical_phone }}
                        </p>
                    </div>

                    <p class="flex items-center gap-2">
                        <UserRound class="text-primary h-4 w-4" />
                        <span>{{ opportunity.responsible_user_name ?? 'Não definido' }}</span>
                    </p>

                    <p class="line-clamp-3 text-muted-foreground">
                        {{ opportunity.opportunity_description }}
                    </p>

                    <p class="text-muted-foreground flex items-center gap-2 text-xs">
                        <CalendarDays class="text-primary h-3.5 w-3.5" />
                        Criada em {{ formatDate(opportunity.created_at) }}
                    </p>
                </CardContent>

                <CardFooter class="flex items-center justify-end gap-2 pt-4">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="edit(opportunity.id)">
                            <Pencil class="h-4 w-4" />
                            Alterar
                        </Link>
                    </Button>

                    <Button
                        variant="destructive"
                        size="sm"
                        @click="openDeleteDialog(opportunity)"
                    >
                        <Trash2 class="h-4 w-4" />
                        Excluir
                    </Button>
                </CardFooter>
            </Card>
        </div>

        <Dialog v-model:open="isDeleteDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Excluir oportunidade</DialogTitle>
                    <DialogDescription>
                        Esta ação não pode ser desfeita.
                        <span v-if="selectedOpportunity" class="block pt-2 text-foreground">
                            Você está prestes a excluir a oportunidade de
                            {{ selectedOpportunity.client_name }}.
                        </span>
                    </DialogDescription>
                </DialogHeader>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary">Cancelar</Button>
                    </DialogClose>
                    <Button variant="destructive" @click="confirmDelete">
                        Confirmar exclusão
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
