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
import opportunities, { edit } from '@/routes/opportunities';

type UserOption = {
    id: number;
    name: string;
};

type StatusOption = {
    value: string;
    label: string;
};

type OpportunityFormData = {
    id: number;
    status: string;
    client_name: string;
    segment: string;
    commercial_contact: string;
    commercial_phone: string;
    technical_contact: string;
    technical_phone: string;
    opportunity_description: string;
    responsible_user_id: number;
};

type Props = {
    users: UserOption[];
    opportunity: OpportunityFormData;
};

const props = defineProps<Props>();
const status = ref(props.opportunity.status);
const responsibleUserId = ref(String(props.opportunity.responsible_user_id));

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
    layout: (pageProps: Props) => ({
        breadcrumbs: [
            {
                title: 'Oportunidades',
                href: opportunities.index(),
            },
            {
                title: 'Editar oportunidade',
                href: edit(pageProps.opportunity.id),
            },
        ],
    }),
});
</script>

<template>
    <Head title="Editar oportunidade" />

    <div class="mx-auto flex w-full max-w-5xl flex-col space-y-6 px-4 py-5 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <Heading
                variant="small"
                title="Editar oportunidade"
                description="Atualize os dados da oportunidade"
            />

            <Button as-child variant="secondary">
                <Link :href="opportunities.index()">Voltar para listagem</Link>
            </Button>
        </div>

        <Form
            v-bind="OpportunityController.update.form(props.opportunity.id)"
            class="space-y-6"
            v-slot="{ errors, processing, wasSuccessful }"
        >
            <Card>
                <CardHeader class="space-y-2 pb-4">
                    <CardTitle>Dados do cliente</CardTitle>
                    <CardDescription>
                        Atualize as informações principais da oportunidade.
                    </CardDescription>
                </CardHeader>
                <CardContent class="grid gap-5 pt-0 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="client_name">Cliente</Label>
                        <Input
                            id="client_name"
                            name="client_name"
                            :default-value="props.opportunity.client_name"
                            required
                        />
                        <InputError :message="errors.client_name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="segment">Segmento</Label>
                        <Input
                            id="segment"
                            name="segment"
                            :default-value="props.opportunity.segment"
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
                        Revise os pontos de contato comercial e técnico.
                    </CardDescription>
                </CardHeader>
                <CardContent class="grid gap-5 pt-0 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="commercial_contact">Contato comercial</Label>
                        <Input
                            id="commercial_contact"
                            name="commercial_contact"
                            :default-value="props.opportunity.commercial_contact"
                            required
                        />
                        <InputError :message="errors.commercial_contact" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="commercial_phone">Telefone comercial</Label>
                        <Input
                            id="commercial_phone"
                            name="commercial_phone"
                            :default-value="props.opportunity.commercial_phone"
                            required
                        />
                        <InputError :message="errors.commercial_phone" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="technical_contact">Contato técnico</Label>
                        <Input
                            id="technical_contact"
                            name="technical_contact"
                            :default-value="props.opportunity.technical_contact"
                            required
                        />
                        <InputError :message="errors.technical_contact" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="technical_phone">Telefone técnico</Label>
                        <Input
                            id="technical_phone"
                            name="technical_phone"
                            :default-value="props.opportunity.technical_phone"
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
                        Garanta o responsável correto e mantenha a descrição atualizada.
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
                                    v-for="user in props.users"
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
                            v-text="props.opportunity.opportunity_description"
                            required
                        ></textarea>
                        <InputError :message="errors.opportunity_description" />
                    </div>
                </CardContent>
            </Card>

            <p v-if="wasSuccessful" class="text-sm font-medium text-green-600">
                Oportunidade atualizada com sucesso.
            </p>

            <div class="flex items-center gap-4">
                <Button type="submit" :disabled="processing">
                    {{ processing ? 'Salvando...' : 'Salvar alterações' }}
                </Button>
            </div>
        </Form>
    </div>
</template>
