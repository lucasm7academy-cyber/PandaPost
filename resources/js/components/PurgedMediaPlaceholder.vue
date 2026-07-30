<script setup lang="ts">
import { IconVideoOff } from '@tabler/icons-vue';

/**
 * Shown where a video used to render, after its file was deleted to reclaim
 * disk space. The original filename stays visible so the post history still
 * says which video was published here.
 */
withDefaults(
    defineProps<{
        filename?: string | null;
        compact?: boolean;
    }>(),
    {
        filename: null,
        compact: false,
    },
);
</script>

<template>
    <div
        class="flex h-full w-full flex-col items-center justify-center gap-1 bg-muted p-2 text-center text-muted-foreground"
    >
        <IconVideoOff :class="compact ? 'size-4' : 'size-6'" />

        <span :class="compact ? 'text-[10px] leading-tight' : 'text-xs'">
            {{ $t('common.purged_media.label') }}
        </span>

        <span
            v-if="filename && !compact"
            class="line-clamp-2 text-[10px] leading-tight break-all opacity-70"
        >
            {{ filename }}
        </span>
    </div>
</template>
