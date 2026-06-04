<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Footer from '../components/Footer.vue';

interface EscoFile {
    present: boolean;
    name: string;
    relative_path: string;
}

const props = defineProps<{
    occupations_count: number;
    esco_file: EscoFile;
}>();

const page = usePage();
const flash = computed(() => page.props.flash as { success?: string; error?: string });

const importing = ref(false);
const generating = ref(false);

const canGenerate = computed(() => props.occupations_count > 0);

const formattedCount = computed(() =>
    new Intl.NumberFormat('fr-FR').format(props.occupations_count),
);

function importOccupations(): void {
    if (importing.value) {
        return;
    }

    importing.value = true;

    router.visit('/occupations/import', {
        preserveScroll: true,
        onFinish: () => {
            importing.value = false;
        },
    });
}

function generateIdeas(): void {
    if (! canGenerate.value || generating.value) {
        return;
    }

    generating.value = true;

    router.visit('/ideas/generate', {
        preserveScroll: true,
        onFinish: () => {
            generating.value = false;
        },
    });
}
</script>

<template>
    <Head title="Accueil" />

    <main class="flex min-h-screen w-full flex-col bg-slate-50 text-slate-900">
        <!-- En-tête -->
        <header class="shrink-0 border-b border-slate-200/80 bg-white/90 px-6 py-4 backdrop-blur-sm lg:px-10">
            <div class="mx-auto flex max-w-[1920px] flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600">
                        WorkHelper
                    </p>
                    <h1 class="text-xl font-semibold tracking-tight text-slate-900 lg:text-2xl">
                        Assistant projets métiers
                    </h1>
                </div>

                <Link
                    href="/generations"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 active:scale-[0.98]"
                >
                    Historique
                    <svg class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </Link>
            </div>

            <!-- Flash -->
            <div
                v-if="flash.success || flash.error"
                class="mx-auto mt-4 max-w-[1920px]"
            >
                <p
                    v-if="flash.success"
                    class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 transition-all duration-300"
                >
                    <svg class="mt-0.5 size-5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    {{ flash.success }}
                </p>
                <p
                    v-if="flash.error"
                    class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800 transition-all duration-300"
                >
                    <svg class="mt-0.5 size-5 shrink-0 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    {{ flash.error }}
                </p>
            </div>
        </header>

        <!-- Hero -->
        <section class="border-b border-slate-200/60 bg-white px-6 py-14 lg:px-10 lg:py-20">
            <div class="mx-auto max-w-[1920px]">
                <div class="max-w-3xl space-y-6">
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-100"
                    >
                        <span class="relative flex size-2">
                            <span class="absolute inline-flex size-full animate-ping rounded-full bg-indigo-400 opacity-75" />
                            <span class="relative inline-flex size-2 rounded-full bg-indigo-500" />
                        </span>
                        ESCO · Intelligence métier · Idées SaaS
                    </span>

                    <h2 class="text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl lg:text-6xl">
                        Transformez un métier en
                        <span class="bg-gradient-to-r from-indigo-600 to-violet-600 bg-clip-text text-transparent">
                            opportunités produit
                        </span>
                    </h2>

                    <p class="max-w-2xl text-lg leading-relaxed text-slate-600">
                        WorkHelper importe la taxonomie européenne des métiers (ESCO), puis génère
                        trois concepts de projets numériques adaptés à un métier — prêts à être explorés
                        et affinés.
                    </p>
                </div>
            </div>
        </section>

        <!-- Contenu principal -->
        <section class="flex flex-1 flex-col px-6 py-10 lg:px-10 lg:py-14">
            <div class="mx-auto flex w-full max-w-[1920px] flex-1 flex-col gap-10">
                <!-- Statistique -->
                <div
                    class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,2fr)] lg:items-stretch"
                >
                    <article
                        class="flex flex-col justify-between rounded-2xl border border-slate-200/80 bg-white p-8 shadow-sm transition duration-300 hover:shadow-md hover:shadow-slate-200/50"
                    >
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">
                                Base métiers
                            </p>
                            <p class="mt-3 text-5xl font-bold tracking-tight text-slate-900 tabular-nums">
                                {{ formattedCount }}
                            </p>
                            <p class="mt-2 text-sm text-slate-500">
                                métiers importés depuis ESCO
                            </p>
                        </div>

                        <div
                            class="mt-8 flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium"
                            :class="occupations_count > 0
                                ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100'
                                : 'bg-amber-50 text-amber-800 ring-1 ring-amber-100'"
                        >
                            <svg
                                class="size-4 shrink-0"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    v-if="occupations_count > 0"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                                />
                                <path
                                    v-else
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"
                                />
                            </svg>
                            {{
                                occupations_count > 0
                                    ? 'Prêt pour la génération d\'idées'
                                    : esco_file.present
                                        ? 'Fichier ESCO prêt — cliquez sur « Intégrer » pour charger la base'
                                        : 'Fichier ESCO manquant dans database/data/'
                            }}
                        </div>
                    </article>

                    <!-- Étapes -->
                    <article
                        class="rounded-2xl border border-slate-200/80 bg-white p-8 shadow-sm"
                    >
                        <h3 class="text-lg font-semibold text-slate-900">
                            Comment ça fonctionne
                        </h3>
                        <ol class="mt-6 grid gap-6 sm:grid-cols-3">
                            <li class="flex gap-4">
                                <span
                                    class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-sm font-bold text-indigo-700"
                                >
                                    1
                                </span>
                                <div>
                                    <p class="font-semibold text-slate-800">
                                        Importer ESCO
                                    </p>
                                    <p class="mt-1 text-sm leading-relaxed text-slate-500">
                                        Le fichier est déjà fourni avec l'application ; un clic suffit pour
                                        l'intégrer en base de données.
                                    </p>
                                </div>
                            </li>
                            <li class="flex gap-4">
                                <span
                                    class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-sm font-bold text-violet-700"
                                >
                                    2
                                </span>
                                <div>
                                    <p class="font-semibold text-slate-800">
                                        Générer des idées
                                    </p>
                                    <p class="mt-1 text-sm leading-relaxed text-slate-500">
                                        Un métier aléatoire alimente le moteur pour produire 3 concepts.
                                    </p>
                                </div>
                            </li>
                            <li class="flex gap-4">
                                <span
                                    class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-sm font-bold text-emerald-700"
                                >
                                    3
                                </span>
                                <div>
                                    <p class="font-semibold text-slate-800">
                                        Explorer les résultats
                                    </p>
                                    <p class="mt-1 text-sm leading-relaxed text-slate-500">
                                        Comparez stack, modèle économique et revenus estimés.
                                    </p>
                                </div>
                            </li>
                        </ol>
                    </article>
                </div>

                <!-- Actions -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Import -->
                    <article
                        class="group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-8 shadow-sm transition duration-300 hover:border-indigo-200/80 hover:shadow-lg hover:shadow-slate-200/60"
                    >
                        <div
                            class="pointer-events-none absolute -right-8 -top-8 size-32 rounded-full bg-indigo-50 opacity-80 transition group-hover:scale-110"
                        />

                        <div class="relative flex flex-1 flex-col">
                            <div
                                class="mb-5 inline-flex size-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600"
                            >
                                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12M12 16.5V3" />
                                </svg>
                            </div>

                            <h3 class="text-xl font-bold text-slate-900">
                                Intégrer les occupations ESCO
                            </h3>

                            <div
                                v-if="esco_file.present"
                                class="mt-4 flex items-start gap-3 rounded-xl border border-sky-100 bg-sky-50/80 px-4 py-3 text-sm text-sky-900"
                            >
                                <svg class="mt-0.5 size-5 shrink-0 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                                <div>
                                    <p class="font-semibold">
                                        Fichier déjà présent
                                    </p>
                                    <p class="mt-1 leading-relaxed text-sky-800/90">
                                        <code class="rounded bg-white/80 px-1.5 py-0.5 font-mono text-xs text-sky-900">{{ esco_file.relative_path }}</code>
                                        est disponible. Appuyez sur le bouton ci-dessous pour intégrer
                                        les métiers et descriptions dans la base de données.
                                    </p>
                                </div>
                            </div>
                            <p
                                v-else
                                class="mt-4 rounded-xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-900"
                            >
                                Placez le fichier <code class="font-mono text-xs">{{ esco_file.name }}</code>
                                dans <code class="font-mono text-xs">{{ esco_file.relative_path }}</code>
                                avant de lancer l'intégration.
                            </p>

                            <p class="mt-4 flex-1 text-sm leading-relaxed text-slate-500">
                                Aucun téléchargement requis : l'intégration lit le fichier local et met à jour
                                ou enrichit les métiers déjà en base.
                            </p>

                            <button
                                type="button"
                                class="mt-8 inline-flex w-full items-center justify-center gap-2 rounded-xl border-2 border-indigo-600 bg-white px-5 py-3.5 text-sm font-semibold text-indigo-600 transition hover:bg-indigo-50 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="importing"
                                @click="importOccupations"
                            >
                                <svg
                                    v-if="importing"
                                    class="size-5 animate-spin text-indigo-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                </svg>
                                <svg
                                    v-else
                                    class="size-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12M12 16.5V3" />
                                </svg>
                                {{ importing ? 'Intégration en cours…' : 'Intégrer les données ESCO' }}
                            </button>
                        </div>
                    </article>

                    <!-- Génération (CTA principal) -->
                    <article
                        class="group relative flex flex-col overflow-hidden rounded-2xl border border-indigo-200/60 bg-gradient-to-br from-indigo-600 via-indigo-600 to-violet-700 p-8 text-white shadow-xl shadow-indigo-600/20 transition duration-300 hover:shadow-2xl hover:shadow-indigo-600/30"
                        :class="{ 'opacity-90': !canGenerate && !generating }"
                    >
                        <div
                            class="pointer-events-none absolute -bottom-12 -left-12 size-48 rounded-full bg-white/10 blur-2xl transition group-hover:scale-110"
                        />

                        <div class="relative flex flex-1 flex-col">
                            <div
                                class="mb-5 inline-flex size-12 items-center justify-center rounded-2xl bg-white/15 text-white ring-1 ring-white/20"
                            >
                                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                                </svg>
                            </div>

                            <span
                                class="mb-4 inline-flex w-fit items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-indigo-100 ring-1 ring-white/20"
                            >
                                Action principale
                            </span>

                            <h3 class="text-xl font-bold">
                                Générer des idées de projets
                            </h3>
                            <p class="mt-2 flex-1 text-sm leading-relaxed text-indigo-100">
                                Lancez l'analyse sur un métier tiré au hasard et obtenez trois propositions
                                complètes : stack, modèle économique et revenu estimé.
                            </p>

                            <p
                                v-if="!canGenerate"
                                class="mt-4 rounded-lg bg-white/10 px-3 py-2 text-xs font-medium text-indigo-100 ring-1 ring-white/15"
                            >
                                {{
                                    esco_file.present
                                        ? 'Le fichier ESCO est prêt : intégrez les données en base pour activer cette action.'
                                        : 'Ajoutez le fichier ESCO puis intégrez les données pour activer cette action.'
                                }}
                            </p>

                            <button
                                type="button"
                                class="mt-8 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-5 py-4 text-sm font-bold text-indigo-700 shadow-lg shadow-indigo-900/20 transition hover:bg-indigo-50 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70"
                                :disabled="!canGenerate || generating"
                                @click="generateIdeas"
                            >
                                <svg
                                    v-if="generating"
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
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                                </svg>
                                {{ generating ? 'Génération en cours…' : 'Générer 3 idées de projets' }}
                            </button>
                        </div>
                    </article>
                </div>

                <!-- Overlay chargement global -->
                <div
                    v-if="importing || generating"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/20 px-6 backdrop-blur-[2px] transition-opacity duration-300"
                    aria-live="polite"
                    aria-busy="true"
                >
                    <div
                        class="flex max-w-sm flex-col items-center gap-4 rounded-2xl border border-slate-200/80 bg-white px-8 py-7 text-center shadow-2xl"
                    >
                        <svg
                            class="size-10 animate-spin text-indigo-600"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        <div>
                            <p class="font-semibold text-slate-900">
                                {{ generating ? 'Génération des idées' : 'Intégration des métiers ESCO' }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                {{
                                    generating
                                        ? 'Analyse du métier et création des 3 concepts…'
                                        : 'Lecture du fichier local et écriture en base de données…'
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <Footer />
    </main>
</template>
