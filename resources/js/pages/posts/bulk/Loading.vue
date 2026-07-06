<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { echo } from '@laravel/echo-vue';
import { IconCheck, IconLoader2, IconX } from '@tabler/icons-vue';
import { trans } from 'laravel-vue-i18n';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { calendar as calendarRoute } from '@/routes/app';
import { index as postsIndex } from '@/routes/app/posts';

const props = defineProps<{
    bulkScheduleId: string;
    channel: string;
    totalPosts: number;
    createdPosts: number;
    status: string;
}>();

type Status = 'pending' | 'processing' | 'completed' | 'failed';

const createdPosts = ref<number>(props.createdPosts);
const totalPosts = ref<number>(props.totalPosts);
const status = ref<Status>((props.status as Status) ?? 'pending');
const errorMessage = ref<string>('');

let echoChannel: ReturnType<ReturnType<typeof echo>['private']> | null = null;

const progress = computed(() => {
    if (totalPosts.value === 0) return 0;
    return Math.min(1, createdPosts.value / totalPosts.value);
});

const progressLabel = computed(() =>
    trans('posts.bulk.loading.progress', {
        created: String(createdPosts.value),
        total: String(totalPosts.value),
    }),
);

const subscribe = () => {
    echoChannel = echo()
        .private(props.channel)
        .listen('.bulk-schedule.progress', (e: {
            created_posts: number;
            total_posts: number;
            status: Status;
            error_message: string | null;
        }) => {
            createdPosts.value = e.created_posts;
            totalPosts.value = e.total_posts;
            status.value = e.status;
            errorMessage.value = e.error_message ?? '';
        });
};

const unsubscribe = () => {
    if (echoChannel) {
        echo().leave(`private-${props.channel}`);
        echoChannel = null;
    }
};

const goToScheduled = () => router.visit(postsIndex('scheduled').url);
const goToCalendar = () => router.visit(calendarRoute().url);

onMounted(() => {
    subscribe();
});

onBeforeUnmount(() => {
    unsubscribe();
});
</script>

<template>
    <Head :title="$t('posts.bulk.loading.page_title')" />

    <AppLayout>
        <div class="mx-auto flex w-full max-w-2xl flex-col items-center gap-6 px-4 py-12">
            <div
                class="inline-flex size-14 -rotate-2 items-center justify-center rounded-2xl border-2 border-foreground shadow-2xs"
                :class="{
                    'bg-violet-200': status === 'pending' || status === 'processing',
                    'bg-emerald-200': status === 'completed',
                    'bg-rose-200': status === 'failed',
                }"
            >
                <IconLoader2 v-if="status === 'pending' || status === 'processing'" class="size-7 animate-spin text-foreground" stroke-width="2" />
                <IconCheck v-else-if="status === 'completed'" class="size-7 text-foreground" stroke-width="2.5" />
                <IconX v-else class="size-7 text-foreground" stroke-width="2.5" />
            </div>

            <h1 class="text-center text-2xl font-bold text-foreground">
                <template v-if="status === 'completed'">{{ $t('posts.bulk.loading.completed_title') }}</template>
                <template v-else-if="status === 'failed'">{{ $t('posts.bulk.loading.failed_title') }}</template>
                <template v-else>{{ $t('posts.bulk.loading.page_title') }}</template>
            </h1>

            <p class="text-center text-sm text-foreground/70">{{ progressLabel }}</p>

            <div class="w-full max-w-md">
                <div class="h-2 w-full overflow-hidden rounded-full border-2 border-foreground bg-card">
                    <div
                        class="h-full bg-foreground transition-[width] duration-500 ease-out"
                        :style="{ width: `${Math.round(progress * 100)}%` }"
                    ></div>
                </div>
            </div>

            <div
                v-if="status === 'failed'"
                class="w-full max-w-lg rounded-xl border-2 border-foreground bg-rose-50 p-4 shadow-2xs"
            >
                <p class="text-center text-sm font-semibold text-rose-700">
                    {{ errorMessage || $t('posts.bulk.loading.failed_body') }}
                </p>
            </div>

            <div class="mt-4 flex w-full max-w-lg flex-col items-center gap-3 rounded-2xl border-2 border-foreground bg-card p-5 text-center shadow-2xs">
                <p class="text-base font-bold text-foreground">{{ $t('posts.bulk.loading.leave_title') }}</p>
                <p class="text-sm text-foreground/70">{{ $t('posts.bulk.loading.leave_body') }}</p>
                <div class="flex flex-wrap items-center justify-center gap-2 pt-1">
                    <Button dusk="bulk-loading-scheduled" @click="goToScheduled">
                        {{ $t('posts.bulk.loading.view_scheduled') }}
                    </Button>
                    <Button variant="outline" dusk="bulk-loading-calendar" @click="goToCalendar">
                        {{ $t('posts.bulk.loading.view_calendar') }}
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
