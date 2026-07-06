<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { IconBookmarks, IconCalendarPlus, IconPencil, IconSparkles } from '@tabler/icons-vue';
import { trans } from 'laravel-vue-i18n';
import { computed, ref } from 'vue';

import { index as templatesIndex } from '@/actions/App/Http/Controllers/App/PostTemplateController';
import PageHeader from '@/components/PageHeader.vue';
import AiPostWizard from '@/components/posts/create/AiPostWizard.vue';
import BulkScheduleWizard from '@/components/posts/create/BulkScheduleWizard.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useFeatureAccess } from '@/composables/useFeatureAccess';
import { useUpgradeDialog } from '@/composables/useUpgradeDialog';
import { store as storePost } from '@/routes/app/posts';

interface SocialAccount {
    id: string;
    platform: string;
    display_name: string;
    username: string;
    avatar_url: string | null;
}

interface Props {
    /** ISO date (YYYY-MM-DD). When set, the manual "start from scratch" path
     *  pre-schedules the new post on this date. */
    date?: string | null;
    socialAccounts: SocialAccount[];
    signatures?: { id: string; name: string }[];
}

const props = withDefaults(defineProps<Props>(), {
    date: null,
    signatures: () => [],
});

type View = 'choice' | 'ai' | 'bulk';

const view = ref<View>('choice');
const submitting = ref(false);

const aiHeader = ref<{ title: string; description: string } | null>(null);
const bulkHeader = ref<{ title: string; description: string } | null>(null);

const { canUseBulkSchedule } = useFeatureAccess();
const { openUpgrade } = useUpgradeDialog();

const hasConnectedAccounts = computed(() => props.socialAccounts.length > 0);

const startFromScratch = () => {
    if (submitting.value) return;
    submitting.value = true;
    const url = props.date ? storePost.url({ query: { date: props.date } }) : storePost.url();
    router.post(url, {}, {
        onFinish: () => {
            submitting.value = false;
        },
    });
};

const openBulkWizard = () => {
    if (!canUseBulkSchedule.value) {
        openUpgrade(trans('posts.create.bulk_upgrade_reason'));
        return;
    }
    if (!hasConnectedAccounts.value) return;
    view.value = 'bulk';
};

const pageTitle = computed(() => trans('posts.create.title'));

const stepHeader = computed(() => {
    if (view.value === 'ai' && aiHeader.value) return aiHeader.value;
    if (view.value === 'bulk' && bulkHeader.value) return bulkHeader.value;
    return {
        title: trans('posts.create.title'),
        description: trans('posts.create.description'),
    };
});
</script>

<template>
    <Head :title="pageTitle" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col p-4">
            <div
                class="mx-auto flex w-full flex-col gap-6"
                :class="view === 'bulk' ? 'max-w-4xl' : 'max-w-3xl'"
            >
                <PageHeader :title="stepHeader.title" :description="stepHeader.description" />

                <!-- Choice screen -->
                <template v-if="view === 'choice'">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <button
                            type="button"
                            class="group flex flex-col items-start gap-4 rounded-2xl border-2 border-foreground bg-card p-5 text-left shadow-2xs transition-all hover:-translate-y-0.5 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:translate-y-0 disabled:hover:shadow-2xs"
                            :disabled="submitting"
                            @click="startFromScratch"
                        >
                            <div class="inline-flex size-12 -rotate-2 items-center justify-center rounded-2xl border-2 border-foreground bg-violet-200 shadow-2xs transition-transform group-hover:rotate-0">
                                <IconPencil class="size-6 text-foreground" stroke-width="2" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-base font-bold text-foreground">{{ $t('posts.create.scratch_title') }}</p>
                                <p class="text-xs leading-relaxed text-foreground/70">
                                    {{ $t('posts.create.scratch_description') }}
                                </p>
                            </div>
                        </button>

                        <button
                            type="button"
                            class="group flex flex-col items-start gap-4 rounded-2xl border-2 border-foreground bg-card p-5 text-left shadow-2xs transition-all hover:-translate-y-0.5 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:translate-y-0 disabled:hover:shadow-2xs"
                            :disabled="!hasConnectedAccounts"
                            @click="view = 'ai'"
                        >
                            <div class="inline-flex size-12 rotate-1 items-center justify-center rounded-2xl border-2 border-foreground bg-amber-200 shadow-2xs transition-transform group-hover:rotate-0">
                                <IconSparkles class="size-6 text-foreground" stroke-width="2" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-base font-bold text-foreground">{{ $t('posts.create.ai_title') }}</p>
                                <p class="text-xs leading-relaxed text-foreground/70">
                                    <template v-if="!hasConnectedAccounts">
                                        {{ $t('posts.create.steps.connect_first') }}
                                    </template>
                                    <template v-else>
                                        {{ $t('posts.create.ai_description') }}
                                    </template>
                                </p>
                            </div>
                        </button>

                        <Link
                            :href="templatesIndex.url({ query: { date: props.date } })"
                            class="group flex flex-col items-start gap-4 rounded-2xl border-2 border-foreground bg-card p-5 text-left shadow-2xs transition-all hover:-translate-y-0.5 hover:shadow-md"
                        >
                            <div class="inline-flex size-12 -rotate-1 items-center justify-center rounded-2xl border-2 border-foreground bg-emerald-200 shadow-2xs transition-transform group-hover:rotate-0">
                                <IconBookmarks class="size-6 text-foreground" stroke-width="2" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-base font-bold text-foreground">{{ $t('posts.create.template_title') }}</p>
                                <p class="text-xs leading-relaxed text-foreground/70">
                                    {{ $t('posts.create.template_description') }}
                                </p>
                            </div>
                        </Link>

                        <button
                            type="button"
                            class="group relative flex flex-col items-start gap-4 rounded-2xl border-2 border-foreground bg-card p-5 text-left shadow-2xs transition-all hover:-translate-y-0.5 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:translate-y-0 disabled:hover:shadow-2xs"
                            :disabled="!hasConnectedAccounts && canUseBulkSchedule"
                            dusk="bulk-card"
                            @click="openBulkWizard"
                        >
                            <span
                                v-if="!canUseBulkSchedule"
                                class="absolute right-2 top-2 inline-flex items-center rounded-md border-2 border-foreground bg-amber-200 px-2 py-0.5 text-[10px] font-black uppercase tracking-widest text-foreground shadow-2xs"
                            >
                                {{ $t('posts.create.bulk_pro_badge') }}
                            </span>
                            <div class="inline-flex size-12 rotate-2 items-center justify-center rounded-2xl border-2 border-foreground bg-sky-200 shadow-2xs transition-transform group-hover:rotate-0">
                                <IconCalendarPlus class="size-6 text-foreground" stroke-width="2" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-base font-bold text-foreground">{{ $t('posts.create.bulk_title') }}</p>
                                <p class="text-xs leading-relaxed text-foreground/70">
                                    <template v-if="!hasConnectedAccounts && canUseBulkSchedule">
                                        {{ $t('posts.create.steps.connect_first') }}
                                    </template>
                                    <template v-else>
                                        {{ $t('posts.create.bulk_description') }}
                                    </template>
                                </p>
                            </div>
                        </button>
                    </div>
                </template>

                <!-- AI flow -->
                <AiPostWizard
                    v-else-if="view === 'ai'"
                    :social-accounts="socialAccounts"
                    :date="props.date"
                    @update:step-header="aiHeader = $event"
                    @cancel="view = 'choice'; aiHeader = null"
                />

                <!-- Bulk schedule flow -->
                <BulkScheduleWizard
                    v-else-if="view === 'bulk'"
                    :social-accounts="socialAccounts"
                    :signatures="signatures"
                    @update:step-header="bulkHeader = $event"
                    @cancel="view = 'choice'; bulkHeader = null"
                />
            </div>
        </div>
    </AppLayout>
</template>
