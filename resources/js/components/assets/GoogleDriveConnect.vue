<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { onMounted, onUnmounted } from 'vue';
import { toast } from 'vue-sonner';

import { Button } from '@/components/ui/button';
import { connect } from '@/routes/app/social/google-drive';

const emit = defineEmits<{
    connected: [];
}>();

const openOAuthPopup = () => {
    const url = connect.url();
    const width = 600;
    const height = 700;
    const left = window.screenX + (window.outerWidth - width) / 2;
    const top = window.screenY + (window.outerHeight - height) / 2;

    window.open(
        url,
        'google-drive-oauth',
        `width=${width},height=${height},left=${left},top=${top},scrollbars=yes,resizable=yes`,
    );
};

const handleOAuthMessage = (event: MessageEvent) => {
    if (event.origin !== window.location.origin) return;
    if (event.data?.type !== 'google-drive-oauth-callback') return;

    if (event.data?.success) {
        toast.success(trans('assets.google_drive.connected'));
        emit('connected');
    } else {
        toast.error(event.data?.message || trans('assets.google_drive.error_connecting'));
    }
};

onMounted(() => {
    window.addEventListener('message', handleOAuthMessage);
});

onUnmounted(() => {
    window.removeEventListener('message', handleOAuthMessage);
});
</script>

<template>
    <div class="flex flex-col items-center justify-center gap-4 rounded-2xl border-2 border-dashed border-foreground/25 bg-card p-8 text-center">
        <div class="inline-flex size-12 items-center justify-center rounded-2xl border-2 border-foreground bg-blue-200 shadow-2xs">
            <svg class="size-6 text-foreground" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 0C5.37 0 0 5.37 0 12s5.37 12 12 12 12-5.37 12-12S18.63 0 12 0zm0 22.5C6.21 22.5 1.5 17.79 1.5 12S6.21 1.5 12 1.5s10.5 4.71 10.5 10.5-4.71 10.5-10.5 10.5zm3-10.5c0 1.66-1.34 3-3 3s-3-1.34-3-3 1.34-3 3-3 3 1.34 3 3z" />
            </svg>
        </div>

        <div>
            <h3 class="text-base font-semibold text-foreground">{{ trans('assets.google_drive.title') }}</h3>
            <p class="mt-1 text-sm text-foreground/60">{{ trans('assets.google_drive.description') }}</p>
        </div>

        <Button @click="openOAuthPopup" class="mt-4">
            {{ trans('assets.google_drive.connect_cta') }}
        </Button>
    </div>
</template>
