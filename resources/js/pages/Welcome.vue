<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { dashboard, login, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const page = usePage();

const currentTeamSlug = computed(() => {
    if (page.props.currentTeam?.slug) {
        return page.props.currentTeam.slug;
    }

    const [, teamSlugFromUrl] = page.url.split('/');

    return teamSlugFromUrl || null;
});

const dashboardUrl = computed(() =>
    currentTeamSlug.value ? dashboard(currentTeamSlug.value).url : '/',
);
</script>

<template>
    <Head title="Bem-vindo ao Pixel Vibe">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="min-h-screen bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]">
        <!-- Cabeçalho -->
        <header class="absolute w-full p-6 lg:p-8">
            <div class="mx-auto flex max-w-7xl items-center justify-between">
                <div class="flex items-center gap-4">
                    <svg class="h-8 w-8 text-[#f53003]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.486H7.343v-2.343L11 7.343z" />
                    </svg>
                    <h1 class="text-2xl font-bold">Pixel Vibe</h1>
                </div>
                <nav class="flex items-center justify-end gap-4 text-sm">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboardUrl"
                        class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 leading-normal hover:border-[#1915014a] dark:border-[#3E3E3A] dark:hover:border-[#62605b]"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="inline-block rounded-sm border border-transparent px-5 py-1.5 leading-normal hover:border-[#19140035] dark:hover:border-[#3E3E3A]"
                        >
                            Log in
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="inline-block rounded-sm border border-[#19140035] bg-[#1b1b18] px-5 py-1.5 leading-normal text-white hover:bg-black dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                        >
                            Register
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <main class="flex flex-col items-center justify-center pt-32">
            <!-- Seção Hero -->
            <section class="w-full text-center lg:py-20">
                <div class="mx-auto max-w-4xl px-6">
                    <h2 class="text-4xl font-extrabold tracking-tight lg:text-6xl">
                        Organize Demandas e Oportunidades com <span class="text-[#f53003]">Pixel Vibe</span>
                    </h2>
                    <p class="mt-6 text-lg text-[#706f6c] dark:text-[#A1A09A]">
                        Centralize o cadastro de demandas, transforme cada solicitação em oportunidade e acompanhe todo o pipeline em um único lugar.
                    </p>
                    <div class="mt-8 flex justify-center gap-4">
                        <Link
                            :href="register()"
                            class="inline-block rounded-lg bg-[#f53003] px-8 py-3 text-lg font-semibold text-white shadow-lg hover:bg-opacity-90"
                        >
                            Cadastrar Oportunidade
                        </Link>
                        <a
                            href="#features"
                            class="inline-block rounded-lg border border-[#19140035] px-8 py-3 text-lg font-semibold dark:border-[#3E3E3A]"
                        >
                            Ver Funcionalidades
                        </a>
                    </div>
                </div>
            </section>

            <!-- Imagem de Apresentação -->
            <section class="w-full px-6 py-16 lg:px-8">
                <div class="mx-auto max-w-6xl">
                    <div class="overflow-hidden rounded-lg bg-white shadow-2xl dark:bg-[#161615]">
                        <img
                            src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=2071&auto=format&fit=crop"
                            alt="Equipe colaborando em um projeto"
                            class="h-full w-full object-cover"
                        />
                    </div>
                </div>
            </section>

            <!-- Seção de Recursos -->
            <section id="features" class="w-full bg-white py-20 dark:bg-[#161615]">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="text-center">
                        <h3 class="text-base font-semibold uppercase tracking-wider text-[#f53003]">Recursos</h3>
                        <p class="mt-2 text-3xl font-extrabold tracking-tight lg:text-4xl">
                            Da entrada da demanda até o fechamento da oportunidade
                        </p>
                        <p class="mx-auto mt-4 max-w-2xl text-xl text-[#706f6c] dark:text-[#A1A09A]">
                            Tenha visibilidade completa do funil, padronize o processo e acelere a tomada de decisão com dados claros.
                        </p>
                    </div>

                    <div class="mt-12 grid gap-10 md:grid-cols-2 lg:grid-cols-3">
                        <div class="flex flex-col items-center text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#f53003] text-white">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            </div>
                            <h4 class="mt-5 text-xl font-bold">Cadastro de Demandas</h4>
                            <p class="mt-2 text-base text-[#706f6c] dark:text-[#A1A09A]">
                                Registre cada demanda com contexto, prioridade, origem e responsável para não perder nenhuma oportunidade.
                            </p>
                        </div>

                        <div class="flex flex-col items-center text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#f53003] text-white">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </div>
                            <h4 class="mt-5 text-xl font-bold">Gestão de Oportunidades</h4>
                            <p class="mt-2 text-base text-[#706f6c] dark:text-[#A1A09A]">
                                Converta demandas em oportunidades com etapas definidas, histórico de interações e previsibilidade de entrega.
                            </p>
                        </div>

                        <div class="flex flex-col items-center text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#f53003] text-white">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            </div>
                            <h4 class="mt-5 text-xl font-bold">Acompanhamento de Pipeline</h4>
                            <p class="mt-2 text-base text-[#706f6c] dark:text-[#A1A09A]">
                                Visualize gargalos, acompanhe avanços por etapa e saiba exatamente o que priorizar para manter o pipeline saudável.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Rodapé -->
        <footer class="w-full bg-[#FDFDFC] py-8 dark:bg-[#0a0a0a]">
            <div class="mx-auto max-w-7xl px-6 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                <p>&copy; 2026 Pixel Vibe. Todos os direitos reservados.</p>
                <p class="mt-1">Laravel v{{ $page.props.versions.laravel }} (PHP v{{ $page.props.versions.php }})</p>
            </div>
        </footer>
    </div>
</template>
