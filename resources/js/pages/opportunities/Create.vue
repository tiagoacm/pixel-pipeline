<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import OpportunityController from '@/actions/App/Http/Controllers/OpportunityController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import opportunities, { create } from '@/routes/opportunities';

type UserOption = {
    id: number;
    name: string;
};

type StatusOption = {
    value: string;
    label: string;
};

type Props = {
    users: UserOption[];
};

defineProps<Props>();
const responsibleUserId = ref('');
const status = ref('nova');

const statusOptions: StatusOption[] = [
    { value: 'nova', label: 'Nova' },
    { value: 'validacao', label: 'Validação' },
    { value: 'aguardando_cliente', label: 'Aguardando Cliente' },
    { value: 'aprovada', label: 'Aprovada' },
    { value: 'em_execucao', label: 'Em Execução' },
    { value: 'finalizada', label: 'Finalizada' },
    { value: 'reprovada', label: 'Reprovada' },
    { value: 'cancelada', label: 'Cancelada' },
];

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Cadastrar oportunidade',
                href: create(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Cadastrar oportunidade" />

    <div class="mx-auto flex w-full max-w-5xl flex-col space-y-6 px-4 py-5 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <Heading
                variant="small"
                title="Cadastro de oportunidade"
                description="Registre uma nova oportunidade comercial"
            />

            <Button as-child variant="secondary">
                <Link :href="opportunities.index()">Voltar para listagem</Link>
            </Button>
        </div>

        <Form
            v-bind="OpportunityController.store.form()"
            class="space-y-6"
            v-slot="{ errors, processing, wasSuccessful }"
        >
            <Card>
                <CardHeader class="space-y-2 pb-4">
                    <CardTitle>Dados do cliente</CardTitle>
                    <CardDescription>
                        Informações principais para identificação da oportunidade.
                    </CardDescription>
                </CardHeader>
                <CardContent class="grid gap-5 pt-0 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="client_name">Cliente</Label>
                        <Input
                            id="client_name"
                            name="client_name"
                            placeholder="Nome do cliente"
                            required
                        />
                        <InputError :message="errors.client_name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="segment">Segmento</Label>
                        <Input
                            id="segment"
                            name="segment"
                            placeholder="Ex.: Varejo, Saúde"
                            required
                        />
                        <InputError :message="errors.segment" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="space-y-2 pb-4">
                    <CardTitle>Contatos</CardTitle>
                    <CardDescription>
                        Dados de contato comercial e técnico da oportunidade.
                    </CardDescription>
                </CardHeader>
                <CardContent class="grid gap-5 pt-0 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="commercial_contact">Contato comercial</Label>
                        <Input
                            id="commercial_contact"
                            name="commercial_contact"
                            placeholder="Nome do contato comercial"
                            required
                        />
                        <InputError :message="errors.commercial_contact" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="commercial_phone">Telefone comercial</Label>
                        <Input
                            id="commercial_phone"
                            name="commercial_phone"
                            placeholder="(11) 99999-9999"
                            required
                        />
                        <InputError :message="errors.commercial_phone" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="technical_contact">Contato técnico</Label>
                        <Input
                            id="technical_contact"
                            name="technical_contact"
                            placeholder="Nome do contato técnico"
                            required
                        />
                        <InputError :message="errors.technical_contact" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="technical_phone">Telefone técnico</Label>
                        <Input
                            id="technical_phone"
                            name="technical_phone"
                            placeholder="(11) 99999-9999"
                            required
                        />
                        <InputError :message="errors.technical_phone" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="space-y-2 pb-4">
                    <CardTitle>Responsável e contexto</CardTitle>
                    <CardDescription>
                        Defina quem conduzirá a oportunidade e descreva o cenário.
                    </CardDescription>
                </CardHeader>
                <CardContent class="grid gap-5 pt-0">
                    <div class="grid gap-2">
                        <Label for="status">Situação da proposta</Label>
                        <Select v-model="status" name="status">
                            <SelectTrigger id="status" class="w-full">
                                <SelectValue placeholder="Selecione a situação" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="option in statusOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.status" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="responsible_user_id">Usuário responsável</Label>
                        <Select
                            v-model="responsibleUserId"
                            name="responsible_user_id"
                        >
                            <SelectTrigger id="responsible_user_id" class="w-full">
                                <SelectValue placeholder="Selecione um usuário" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="user in users"
                                    :key="user.id"
                                    :value="String(user.id)"
                                >
                                    {{ user.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.responsible_user_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="opportunity_description">Descrição da oportunidade</Label>
                        <textarea
                            id="opportunity_description"
                            name="opportunity_description"
                            rows="5"
                            class="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:ring-ring w-full rounded-md border px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] focus-visible:ring-1 focus-visible:outline-none"
                            placeholder="Descreva objetivos, escopo e contexto da oportunidade"
                            required
                        />
                        <InputError :message="errors.opportunity_description" />
                    </div>
                </CardContent>
            </Card>

            <p v-if="wasSuccessful" class="text-sm font-medium text-green-600">
                Oportunidade salva com sucesso.
            </p>

            <div class="flex items-center gap-4">
                <Button type="submit" :disabled="processing">
                    {{ processing ? 'Salvando...' : 'Salvar oportunidade' }}
                </Button>
            </div>
        </Form>
    </div>
</template>
