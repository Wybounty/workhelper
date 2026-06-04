<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Project {
    title: string;
    application_type: string;
    description: string;
    why_useful: string;
    development_duration: string;
    recommended_stack: string;
    business_model: string;
    estimated_monthly_revenue: string | number;
}

const props = defineProps<{
    projects: Project[];
    occupation_name: string | null;
    occupation_description: string | null;
}>();

const page = usePage();
const flash = computed(() => page.props.flash as { success?: string; error?: string });

const hasProjects = computed(() => props.projects.length > 0);

const downloadingIndex = ref<number | null>(null);
const pdfError = ref<string | null>(null);

function slugifyTitle(title: string): string {
    return title
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '') || 'projet-workhelper';
}

function filenameFromDisposition(header: string | null, fallback: string): string {
    if (! header) {
        return fallback;
    }

    const utf8Match = header.match(/filename\*=UTF-8''([^;]+)/i);

    if (utf8Match?.[1]) {
        return decodeURIComponent(utf8Match[1]);
    }

    const match = header.match(/filename="?([^";\n]+)"?/i);

    return match?.[1]?.trim() ?? fallback;
}

async function downloadPdf(project: Project, index: number): Promise<void> {
    if (downloadingIndex.value !== null) {
        return;
    }

    downloadingIndex.value = index;
    pdfError.value = null;

    const fallbackFilename = `${slugifyTitle(project.title)}.pdf`;

    try {
        const token = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';

        const response = await fetch('/ideas/pdf', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/pdf',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                project,
                occupation_name: props.occupation_name,
                occupation_description: props.occupation_description,
            }),
        });

        if (! response.ok) {
            throw new Error('Génération du PDF impossible.');
        }

        const blob = await response.blob();
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filenameFromDisposition(
            response.headers.get('Content-Disposition'),
            fallbackFilename,
        );
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(url);
    } catch {
        pdfError.value = 'Impossible de télécharger le PDF. Réessayez dans un instant.';
    } finally {
        downloadingIndex.value = null;
    }
}

function formatRevenue(value: string | number): string {
    const numeric = typeof value === 'number'
        ? value
        : Number(String(value).replace(/[^\d.,-]/g, '').replace(',', '.'));

    if (! Number.isNaN(numeric) && numeric > 0) {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: 'EUR',
            maximumFractionDigits: 0,
        }).format(numeric);
    }

    return String(value);
}

const badgeStyles: Record<string, string> = {
    saas: 'bg-violet-100 text-violet-700 ring-violet-200',
    mobile: 'bg-sky-100 text-sky-700 ring-sky-200',
    web: 'bg-indigo-100 text-indigo-700 ring-indigo-200',
    api: 'bg-amber-100 text-amber-800 ring-amber-200',
    marketplace: 'bg-emerald-100 text-emerald-700 ring-emerald-200',
};

function badgeClass(type: string): string {
    const key = type.toLowerCase().replace(/\s+/g, '_');

    return badgeStyles[key] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
}
</script>

<template>
    <Head title="Idées de projets" />

    <main class="flex min-h-screen w-full flex-col bg-slate-50 text-slate-900">
        <!-- Barre d'actions -->
        <header class="shrink-0 border-b border-slate-200/80 bg-white/90 px-6 py-4 backdrop-blur-sm lg:px-10">
            <div class="mx-auto flex max-w-[1920px] flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600">
                        WorkHelper
                    </p>
                    <h1 class="text-xl font-semibold tracking-tight text-slate-900 lg:text-2xl">
                        Idées de projets
                    </h1>
                </div>

                <Link
                    href="/ideas/generate"
                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-indigo-600/25 transition hover:bg-indigo-500 hover:shadow-md hover:shadow-indigo-500/30 active:scale-[0.98]"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Générer des idées
                </Link>
            </div>

            <div
                v-if="pdfError"
                class="mx-auto mt-4 max-w-[1920px]"
            >
                <p class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                    {{ pdfError }}
                </p>
            </div>

            <div
                v-if="flash.success || flash.error"
                class="mx-auto mt-4 max-w-[1920px]"
            >
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

        <!-- État vide -->
        <section
            v-if="!hasProjects"
            class="flex flex-1 flex-col items-center justify-center gap-6 px-6 py-16 text-center"
        >
            <div class="max-w-md space-y-3">
                <h2 class="text-2xl font-semibold tracking-tight text-slate-800">
                    Aucune idée pour l'instant
                </h2>
                <p class="text-slate-500">
                    Lancez la génération pour obtenir trois propositions de projets adaptées à un métier ESCO.
                </p>
            </div>
            <Link
                href="/ideas/generate"
                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500"
            >
                Générer des idées
            </Link>
        </section>

        <!-- Contenu avec projets -->
        <template v-else>
            <!-- Métier (affiché une seule fois) -->
            <section class="shrink-0 border-b border-slate-200/60 bg-white px-6 py-8 lg:px-10">
                <div class="mx-auto max-w-[1920px] space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">
                        Métier analysé
                    </p>
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900 lg:text-3xl">
                        {{ occupation_name }}
                    </h2>
                    <p
                        v-if="occupation_description"
                        class="max-w-4xl text-base leading-relaxed text-slate-600 lg:text-lg"
                    >
                        {{ occupation_description }}
                    </p>
                </div>
            </section>

            <!-- Grille des 3 projets -->
            <section class="flex min-h-0 flex-1 flex-col px-4 py-6 sm:px-6 lg:px-10 lg:py-8">
                <div
                    class="mx-auto grid h-full w-full max-w-[1920px] flex-1 grid-cols-1 gap-5 md:grid-cols-2 md:gap-6 xl:grid-cols-3 xl:gap-6"
                >
                    <article
                        v-for="(project, index) in projects"
                        :key="index"
                        class="group flex min-h-[28rem] flex-col rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:border-indigo-200/80 hover:shadow-xl hover:shadow-slate-200/60 xl:min-h-0"
                    >
                        <!-- En-tête card -->
                        <div class="mb-5 flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1 space-y-2">
                                <span
                                    class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider ring-1 ring-inset"
                                    :class="badgeClass(project.application_type)"
                                >
                                    {{ project.application_type }}
                                </span>
                                <h3 class="text-lg font-bold leading-snug text-slate-900 lg:text-xl">
                                    {{ project.title }}
                                </h3>
                            </div>
                            <span
                                class="shrink-0 rounded-lg bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-500"
                            >
                                #{{ index + 1 }}
                            </span>
                        </div>

                        <!-- Revenu estimé -->
                        <div
                            class="mb-5 rounded-xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-teal-50/80 px-4 py-3"
                        >
                            <p class="text-[10px] font-semibold uppercase tracking-widest text-emerald-700/80">
                                Revenu mensuel estimé
                            </p>
                            <p class="mt-0.5 text-2xl font-bold tracking-tight text-emerald-800">
                                {{ formatRevenue(project.estimated_monthly_revenue) }}
                            </p>
                        </div>

                        <!-- Corps scrollable si contenu long -->
                        <div class="flex flex-1 flex-col gap-4 overflow-y-auto text-sm leading-relaxed">
                            <div>
                                <p class="mb-1 text-[10px] font-semibold uppercase tracking-widest text-slate-400">
                                    Description
                                </p>
                                <p class="text-slate-600">
                                    {{ project.description }}
                                </p>
                            </div>

                            <div>
                                <p class="mb-1 text-[10px] font-semibold uppercase tracking-widest text-slate-400">
                                    Pourquoi c'est utile
                                </p>
                                <p class="text-slate-600">
                                    {{ project.why_useful }}
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div class="rounded-lg bg-slate-50 px-3 py-2.5 ring-1 ring-slate-100">
                                    <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">
                                        Durée de développement
                                    </p>
                                    <p class="mt-1 font-medium text-slate-800">
                                        {{ project.development_duration }}
                                    </p>
                                </div>
                                <div class="rounded-lg bg-slate-50 px-3 py-2.5 ring-1 ring-slate-100">
                                    <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">
                                        Modèle économique
                                    </p>
                                    <p class="mt-1 font-medium text-slate-800">
                                        {{ project.business_model }}
                                    </p>
                                </div>
                            </div>

                            <div class="rounded-lg border border-indigo-100 bg-indigo-50/50 px-3 py-3">
                                <p class="mb-1 text-[10px] font-semibold uppercase tracking-widest text-indigo-500">
                                    Stack recommandée
                                </p>
                                <p class="font-mono text-xs leading-relaxed text-indigo-900/90">
                                    {{ project.recommended_stack }}
                                </p>
                            </div>

                            <button
                                type="button"
                                class="mt-auto inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="downloadingIndex !== null"
                                @click="downloadPdf(project, index)"
                            >
                                <svg
                                    v-if="downloadingIndex === index"
                                    class="size-5 animate-spin text-indigo-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                </svg>
                                <svg
                                    v-else
                                    class="size-5 text-indigo-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                                {{
                                    downloadingIndex === index
                                        ? 'Génération du PDF…'
                                        : 'Télécharger le PDF'
                                }}
                            </button>
                        </div>
                    </article>
                </div>
            </section>
        </template>
    </main>
</template>
