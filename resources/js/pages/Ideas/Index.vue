<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const flash = page.props.flash;

</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <main class="min-h-screen w-full bg-[#252525]">
        <section class="flex flex-col gap-4 items-center justify-center w-full min-h-screen text-white">
            <div v-if="flash.success" class="bg-green-500 text-white px-4 py-2 rounded-md">
                {{ flash.success }}
            </div>
            <a href="#" class="bg-blue-500 text-white px-4 py-2 rounded-md cursor-pointer">
                Générer des idées
            </a>

        </section>
    </main>
</template>
