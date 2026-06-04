<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Footer from '../../components/Footer.vue';

interface GenerationItem {
    id: number;
    occupation_name: string;
    occupation_description: string | null;
    ideas_count: number;
    created_at: string;
}

interface PaginatedGenerations {
    data: GenerationItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

defineProps<{
    generations: PaginatedGenerations;
}>();
</script>

<template>
    <Head title="Historique des générations" />

    <main class="flex min-h-screen w-full flex-col bg-slate-50 text-slate-900">
        <header class="shrink-0 border-b border-slate-200/80 bg-white/90 px-6 py-4 backdrop-blur-sm lg:px-10">
            <div class="mx-auto flex max-w-[1920px] flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600">
                        WorkHelper
                    </p>
                    <h1 class="text-xl font-semibold tracking-tight text-slate-900 lg:text-2xl">
                        Historique des générations
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Retrouvez toutes vos sessions d'idées produit par métier.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Link
                        href="/"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50"
                    >
                        Accueil
                    </Link>
                    <Link
                        href="/ideas/generate"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-indigo-600/25 transition hover:bg-indigo-500 active:scale-[0.98]"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Nouvelle génération
                    </Link>
                </div>
            </div>
        </header>

        <section class="flex-1 px-6 py-10 lg:px-10">
            <div class="mx-auto max-w-[1920px]">
                <!-- État vide -->
                <div
                    v-if="generations.data.length === 0"
                    class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white px-8 py-20 text-center"
                >
                    <div class="mb-4 inline-flex size-14 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
                        <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-slate-800">
                        Aucune génération enregistrée
                    </h2>
                    <p class="mt-2 max-w-md text-slate-500">
                        Lancez votre première génération depuis l'accueil pour constituer votre historique.
                    </p>
                    <Link
                        href="/"
                        class="mt-6 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500"
                    >
                        Aller à l'accueil
                    </Link>
                </div>

                <!-- Liste -->
                <div
                    v-else
                    class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3"
                >
                    <Link
                        v-for="item in generations.data"
                        :key="item.id"
                        :href="`/generations/${item.id}`"
                        class="group flex flex-col rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:border-indigo-200/80 hover:shadow-xl hover:shadow-slate-200/60"
                    >
                        <div class="mb-4 flex items-start justify-between gap-3">
                            <span
                                class="inline-flex rounded-full bg-indigo-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-indigo-700 ring-1 ring-indigo-100"
                            >
                                {{ item.ideas_count }} idée{{ item.ideas_count > 1 ? 's' : '' }}
                            </span>
                            <svg
                                class="size-5 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-indigo-500"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </div>

                        <h2 class="text-lg font-bold leading-snug text-slate-900 group-hover:text-indigo-700">
                            {{ item.occupation_name }}
                        </h2>

                        <p
                            v-if="item.occupation_description"
                            class="mt-3 line-clamp-3 flex-1 text-sm leading-relaxed text-slate-500"
                        >
                            {{ item.occupation_description }}
                        </p>

                        <div class="mt-5 flex items-center gap-2 border-t border-slate-100 pt-4 text-xs font-medium text-slate-400">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                            {{ item.created_at }}
                        </div>
                    </Link>
                </div>

                <!-- Pagination -->
                <nav
                    v-if="generations.last_page > 1"
                    class="mt-10 flex flex-wrap items-center justify-center gap-2"
                >
                    <template
                        v-for="(link, i) in generations.links"
                        :key="i"
                    >
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="rounded-lg px-3 py-2 text-sm font-medium transition"
                            :class="link.active
                                ? 'bg-indigo-600 text-white shadow-sm'
                                : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50'"
                            :preserve-scroll="true"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="cursor-not-allowed rounded-lg px-3 py-2 text-sm font-medium text-slate-300"
                            v-html="link.label"
                        />
                    </template>
                </nav>
            </div>
        </section>
        <Footer />
    </main>
</template>
