<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import IdeaProjectGrid from '@/components/IdeaProjectGrid.vue';
import type { Project } from '@/components/IdeaProjectGrid.vue';
import Footer from '../../components/Footer.vue';

interface Generation {
    id: number;
    occupation_name: string;
    occupation_description: string | null;
    created_at: string;
    projects: Project[];
}

defineProps<{
    generation: Generation;
}>();

const page = usePage();
const flash = computed(() => page.props.flash as { success?: string; error?: string });
const pdfError = ref<string | null>(null);
</script>

<template>
    <Head :title="`Génération — ${generation.occupation_name}`" />

    <main class="flex min-h-screen w-full flex-col bg-slate-50 text-slate-900">
        <header class="shrink-0 border-b border-slate-200/80 bg-white/90 px-6 py-4 backdrop-blur-sm lg:px-10">
            <div class="mx-auto flex max-w-[1920px] flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600">
                        WorkHelper
                    </p>
                    <h1 class="text-xl font-semibold tracking-tight text-slate-900 lg:text-2xl">
                        Détail de la génération
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ generation.created_at }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Link
                        href="/generations"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 active:scale-[0.98]"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                        Historique
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

            <div
                v-if="pdfError || flash.success || flash.error"
                class="mx-auto mt-4 max-w-[1920px] space-y-3"
            >
                <p
                    v-if="pdfError"
                    class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800"
                >
                    {{ pdfError }}
                </p>
                <p
                    v-if="flash.success"
                    class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800"
                >
                    {{ flash.success }}
                </p>
                <p
                    v-if="flash.error"
                    class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800"
                >
                    {{ flash.error }}
                </p>
            </div>
        </header>

        <IdeaProjectGrid
            :projects="generation.projects"
            :occupation-name="generation.occupation_name"
            :occupation-description="generation.occupation_description"
            @pdf-error="pdfError = $event"
        />
    </main>
    <Footer />
</template>
