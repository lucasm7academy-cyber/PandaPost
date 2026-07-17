<script setup lang="ts">
import { IconBrandYoutube, IconCheck } from '@tabler/icons-vue';
import { ref } from 'vue';

import { Alert, AlertDescription } from '@/components/ui/alert';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import PopupLayout from '@/layouts/PopupLayout.vue';
import { select as selectChannel } from '@/routes/app/social/youtube';

interface Channel {
    id: string;
    title: string;
    custom_url: string | null;
    thumbnail: string | null;
    subscriber_count: string | number;
}

interface Workspace {
    id: string;
    name: string;
}

interface Props {
    workspace: Workspace;
    channels: Channel[];
    error?: string;
}

defineProps<Props>();

const formRef = ref<HTMLFormElement | null>(null);
const selectedChannelId = ref<string | null>(null);

const handleSelectChannel = (channel: Channel) => {
    selectedChannelId.value = channel.id;
    setTimeout(() => formRef.value?.submit(), 0);
};

const csrfToken =
    document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content') ?? '';

const formatSubscriberCount = (count: string | number): string => {
    const num = typeof count === 'string' ? parseInt(count, 10) : count;
    if (num >= 1000000) {
        return `${(num / 1000000).toFixed(1)}M subscribers`;
    }
    if (num >= 1000) {
        return `${(num / 1000).toFixed(1)}K subscribers`;
    }
    return `${num} subscribers`;
};
</script>

<template>
    <PopupLayout :title="$t('accounts.youtube.title')">
        <!-- Hidden form for regular POST submission -->
        <form
            ref="formRef"
            :action="selectChannel.url()"
            method="POST"
            class="hidden"
        >
            <input type="hidden" name="_token" :value="csrfToken" />
            <input type="hidden" name="channel_id" :value="selectedChannelId" />
        </form>

        <div class="flex flex-col gap-6">
            <div class="flex items-center gap-3">
                <img
                    src="/images/accounts/youtube.png"
                    alt="YouTube"
                    class="h-10 w-10"
                />
                <div>
                    <h1 class="text-xl font-bold tracking-tight">
                        {{ $t('accounts.youtube.title') }}
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        {{ $t('accounts.youtube.description') }}
                    </p>
                </div>
            </div>

            <Alert v-if="error" variant="destructive">
                <AlertDescription>{{ error }}</AlertDescription>
            </Alert>

            <div
                v-if="channels.length === 0 && !error"
                class="py-12 text-center"
            >
                <div
                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-muted"
                >
                    <IconBrandYoutube class="h-7 w-7 text-muted-foreground" />
                </div>
                <h3 class="mt-4 text-lg font-semibold">
                    {{ $t('accounts.youtube.no_channels') }}
                </h3>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ $t('accounts.youtube.no_channels_description') }}
                </p>
            </div>

            <div v-else class="grid gap-3">
                <button
                    v-for="channel in channels"
                    :key="channel.id"
                    @click="handleSelectChannel(channel)"
                    class="group relative overflow-hidden rounded-lg border bg-card p-4 text-left transition-all hover:border-primary hover:shadow-md focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:outline-none"
                >
                    <div class="flex items-center gap-4">
                        <Avatar class="h-12 w-12 rounded-lg">
                            <AvatarImage
                                v-if="channel.thumbnail"
                                :src="channel.thumbnail"
                                class="object-cover"
                            />
                            <AvatarFallback
                                class="rounded-lg bg-red-100 dark:bg-red-900"
                            >
                                <IconBrandYoutube
                                    class="h-6 w-6 text-red-600 dark:text-red-400"
                                />
                            </AvatarFallback>
                        </Avatar>
                        <div class="min-w-0 flex-1">
                            <h3
                                class="truncate font-semibold transition-colors group-hover:text-primary"
                            >
                                {{ channel.title }}
                            </h3>
                            <p class="truncate text-sm text-muted-foreground">
                                @{{ channel.custom_url || channel.id }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{
                                    formatSubscriberCount(
                                        channel.subscriber_count,
                                    )
                                }}
                            </p>
                        </div>
                        <div
                            class="shrink-0 opacity-0 transition-opacity group-hover:opacity-100"
                        >
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-primary-foreground"
                            >
                                <IconCheck class="h-4 w-4" />
                            </div>
                        </div>
                    </div>
                </button>
            </div>
        </div>
    </PopupLayout>
</template>
