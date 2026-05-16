<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Search, Trash2, UserRound } from 'lucide-vue-next';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
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
import users, { destroy, store, update } from '@/routes/users';

type UserCard = {
    id: number;
    name: string;
    email: string;
    created_at?: string | null;
};

type Props = {
    users: UserCard[];
    filters: {
        search: string;
    };
};

const props = defineProps<Props>();

const isFormDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);
const selectedUser = ref<UserCard | null>(null);

const filterForm = useForm({
    search: props.filters.search,
});

const saveForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Usuários',
                href: users.index(),
            },
        ],
    },
});

function applyFilters(): void {
    router.get(
        users.index().url,
        {
            search: filterForm.search,
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
    applyFilters();
}

function openCreateDialog(): void {
    selectedUser.value = null;
    saveForm.reset();
    saveForm.clearErrors();
    isFormDialogOpen.value = true;
}

function openEditDialog(user: UserCard): void {
    selectedUser.value = user;
    saveForm.name = user.name;
    saveForm.email = user.email;
    saveForm.password = '';
    saveForm.password_confirmation = '';
    saveForm.clearErrors();
    isFormDialogOpen.value = true;
}

function closeFormDialog(): void {
    isFormDialogOpen.value = false;
}

function saveUser(): void {
    if (selectedUser.value) {
        saveForm.patch(update(selectedUser.value.id).url, {
            preserveScroll: true,
            onSuccess: () => {
                closeFormDialog();
                selectedUser.value = null;
                saveForm.reset();
            },
        });

        return;
    }

    saveForm.post(store().url, {
        preserveScroll: true,
        onSuccess: () => {
            closeFormDialog();
            saveForm.reset();
        },
    });
}

function openDeleteDialog(user: UserCard): void {
    selectedUser.value = user;
    isDeleteDialogOpen.value = true;
}

function confirmDelete(): void {
    if (!selectedUser.value) {
        return;
    }

    router.delete(destroy(selectedUser.value.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            isDeleteDialogOpen.value = false;
            selectedUser.value = null;
        },
    });
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
    <Head title="Usuários" />

    <div class="mx-auto flex w-full max-w-7xl flex-col space-y-6 px-4 py-5 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <Heading
                variant="small"
                title="Usuários"
                description="Gerencie os usuários cadastrados no sistema"
            />

            <Button @click="openCreateDialog">
                <Plus class="h-4 w-4" />
                Cadastrar usuário
            </Button>
        </div>

        <Card>
            <CardHeader class="space-y-2 pb-4">
                <CardTitle>Filtros rápidos</CardTitle>
                <CardDescription>
                    Busque por nome ou e-mail para localizar usuários.
                </CardDescription>
            </CardHeader>
            <CardContent class="pt-0">
                <form
                    class="grid gap-5 md:grid-cols-[1.4fr_auto_auto] md:items-end"
                    @submit.prevent="applyFilters"
                >
                    <div class="grid gap-2">
                        <Label for="search">Busca</Label>
                        <div class="relative">
                            <Search class="text-muted-foreground absolute top-2.5 left-3 h-4 w-4" />
                            <Input
                                id="search"
                                v-model="filterForm.search"
                                placeholder="Nome ou e-mail"
                                class="pl-9"
                            />
                        </div>
                    </div>

                    <Button type="submit">Aplicar</Button>
                    <Button type="button" variant="secondary" @click="clearFilters">
                        Limpar
                    </Button>
                </form>
            </CardContent>
        </Card>

        <div
            v-if="props.users.length === 0"
            class="rounded-lg border border-dashed p-8 text-center text-muted-foreground"
        >
            Nenhum usuário encontrado.
        </div>

        <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <Card
                v-for="user in props.users"
                :key="user.id"
                class="h-full gap-4 border-l-4 border-l-primary/60 py-5"
            >
                <CardHeader class="space-y-2 pb-2">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <CardTitle class="text-base leading-tight">{{ user.name }}</CardTitle>
                            <CardDescription>{{ user.email }}</CardDescription>
                        </div>

                        <Badge variant="outline">ID #{{ user.id }}</Badge>
                    </div>
                </CardHeader>

                <CardContent class="space-y-2 pt-0 text-sm">
                    <p class="text-primary flex items-center gap-2">
                        <UserRound class="h-4 w-4" />
                        Cadastrado em {{ formatDate(user.created_at) }}
                    </p>
                </CardContent>

                <CardFooter class="flex items-center justify-end gap-2 pt-2">
                    <Button variant="outline" size="sm" @click="openEditDialog(user)">
                        <Pencil class="h-4 w-4" />
                        Alterar
                    </Button>

                    <Button variant="destructive" size="sm" @click="openDeleteDialog(user)">
                        <Trash2 class="h-4 w-4" />
                        Excluir
                    </Button>
                </CardFooter>
            </Card>
        </div>

        <Dialog v-model:open="isFormDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {{ selectedUser ? 'Editar usuário' : 'Cadastrar usuário' }}
                    </DialogTitle>
                    <DialogDescription>
                        {{
                            selectedUser
                                ? 'Atualize os dados do usuário. A senha é opcional na edição.'
                                : 'Preencha os dados para criar um novo usuário.'
                        }}
                    </DialogDescription>
                </DialogHeader>

                <form class="space-y-4" @submit.prevent="saveUser">
                    <div class="grid gap-2">
                        <Label for="name">Nome</Label>
                        <Input id="name" v-model="saveForm.name" placeholder="Nome completo" required />
                        <InputError :message="saveForm.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">E-mail</Label>
                        <Input
                            id="email"
                            v-model="saveForm.email"
                            type="email"
                            placeholder="usuario@empresa.com"
                            required
                        />
                        <InputError :message="saveForm.errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">Senha</Label>
                        <Input
                            id="password"
                            v-model="saveForm.password"
                            type="password"
                            :required="!selectedUser"
                            placeholder="********"
                        />
                        <InputError :message="saveForm.errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">Confirmar senha</Label>
                        <Input
                            id="password_confirmation"
                            v-model="saveForm.password_confirmation"
                            type="password"
                            :required="!selectedUser"
                            placeholder="********"
                        />
                    </div>

                    <DialogFooter class="gap-2">
                        <DialogClose as-child>
                            <Button type="button" variant="secondary">Cancelar</Button>
                        </DialogClose>
                        <Button type="submit" :disabled="saveForm.processing">
                            {{ selectedUser ? 'Salvar alterações' : 'Cadastrar usuário' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="isDeleteDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Excluir usuário</DialogTitle>
                    <DialogDescription>
                        Esta ação não pode ser desfeita.
                        <span v-if="selectedUser" class="block pt-2 text-foreground">
                            Você está prestes a excluir {{ selectedUser.name }}.
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
