<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { IconArrowLeft } from '@tabler/icons-vue';

defineProps<{
    pageTitle: string;
    title: string;
    intro: string;
    lastUpdated: string;
    contactEmail: string;
    sections: { heading: string; body: string }[];
}>();
</script>

<template>
    <div class="min-h-svh bg-background text-foreground">
        <Head :title="pageTitle" />

        <header class="border-b-2 border-foreground/10 bg-card">
            <div
                class="mx-auto flex max-w-3xl items-center justify-between px-6 py-4"
            >
                <Link href="/" class="flex items-center gap-2 font-medium">
                    <img
                        src="/images/trypost/logo-light.png"
                        alt="PandaPost"
                        class="h-8 w-auto dark:hidden"
                    />
                    <img
                        src="/images/trypost/logo-dark.png"
                        alt="PandaPost"
                        class="hidden h-8 w-auto dark:block"
                    />
                </Link>
                <Link
                    href="/"
                    class="inline-flex items-center gap-1.5 text-sm font-medium text-foreground/70 transition-colors hover:text-foreground"
                >
                    <IconArrowLeft class="size-4" />
                    {{ $t('legal.back_home') }}
                </Link>
            </div>
        </header>

        <main class="mx-auto max-w-3xl px-6 py-12">
            <article class="space-y-8">
                <header class="space-y-3">
                    <h1
                        class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl"
                        style="font-family: var(--font-display)"
                    >
                        {{ title }}
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        {{ $t('legal.last_updated', { date: lastUpdated }) }}
                    </p>
                </header>

                <p class="text-base leading-relaxed text-foreground/90">
                    {{ intro }}
                </p>

                <section
                    v-for="(section, index) in sections"
                    :key="index"
                    class="space-y-3"
                >
                    <h2
                        class="text-xl font-bold text-foreground"
                        style="font-family: var(--font-display)"
                    >
                        {{ section.heading }}
                    </h2>
                    <p class="text-base leading-relaxed text-foreground/85">
                        {{ section.body }}
                    </p>
                </section>

                <footer
                    class="mt-12 border-t-2 border-dashed border-foreground/15 pt-6"
                >
                    <p class="text-sm text-muted-foreground">
                        {{ $t('legal.contact', { email: contactEmail }) }}
                    </p>
                </footer>
            </article>
        </main>
    </div>
</template>
