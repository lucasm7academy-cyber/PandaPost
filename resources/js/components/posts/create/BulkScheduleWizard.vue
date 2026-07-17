<script setup lang="ts">
import { router, useHttp } from '@inertiajs/vue3';
import {
    IconArrowLeft,
    IconCheck,
    IconClock,
    IconPhoto,
    IconPlayerPlay,
    IconPlus,
    IconX,
} from '@tabler/icons-vue';
import { trans } from 'laravel-vue-i18n';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

import ImagePreviewDialog from '@/components/ImagePreviewDialog.vue';
import MediaPickerDialog from '@/components/posts/MediaPickerDialog.vue';
import MultiDateCalendar from '@/components/posts/bulk/MultiDateCalendar.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { getPlatformLogo } from '@/composables/usePlatformLogo';
import dayjs from '@/dayjs';
import {
    loading as loadingRoute,
    start as startRoute,
} from '@/routes/app/posts/bulk';

interface SocialAccount {
    id: string;
    platform: string;
    display_name: string;
    username: string;
    avatar_url: string | null;
}

interface PickedMedia {
    id: string;
    path: string;
    url: string;
    type: string;
    mime_type: string;
    original_filename?: string;
}

interface Props {
    socialAccounts: SocialAccount[];
    signatures: { id: string; name: string; content?: string }[];
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:stepHeader': [{ title: string; description: string }];
    cancel: [];
}>();

type Step = 'media' | 'platforms' | 'schedule' | 'prompt' | 'confirm';

const step = ref<Step>('media');

const selectedMedia = ref<PickedMedia[]>([]);
const selectedAccountIds = ref<string[]>([]);
const selectedDays = ref<string[]>([]);
const selectedTimes = ref<string[]>(['09:00', '13:00', '18:00']);
const newTime = ref('');
const promptText = ref('');

const selectedSignatureId = ref<string>('none');

const mediaTitles = ref<Record<string, string>>({});
const lightboxRef = ref<InstanceType<typeof ImagePreviewDialog> | null>(null);

const getTitleFromFilename = (filename?: string) => {
    if (!filename) return '';
    const lastDotIndex = filename.lastIndexOf('.');
    const nameWithoutExt =
        lastDotIndex === -1 ? filename : filename.substring(0, lastDotIndex);
    return nameWithoutExt.replace(/[_-]/g, ' ').replace(/\s+/g, ' ').trim();
};

watch(
    selectedMedia,
    (newMedia) => {
        newMedia.forEach((m) => {
            if (mediaTitles.value[m.id] === undefined) {
                mediaTitles.value[m.id] =
                    getTitleFromFilename(m.original_filename) || '';
            }
        });
    },
    { deep: true, immediate: true },
);

watch(
    mediaTitles,
    (newTitles) => {
        promptText.value = JSON.stringify(newTitles);
    },
    { deep: true },
);

const previewMedia = (media: PickedMedia) => {
    lightboxRef.value?.open(
        media.url,
        media.type === 'video' ? 'video' : 'image',
    );
};

const selectedSignatureContent = computed(() => {
    if (selectedSignatureId.value === 'none') return '';
    const sig = props.signatures.find((s) => String(s.id) === selectedSignatureId.value);
    return sig?.content || '';
});

const submitting = ref(false);

const mediaPickerRef = ref<InstanceType<typeof MediaPickerDialog> | null>(null);

const totalSlots = computed(
    () => selectedDays.value.length * selectedTimes.value.length,
);
const postsToCreate = computed(() =>
    Math.min(selectedMedia.value.length, totalSlots.value),
);
const discardedMedia = computed(() =>
    Math.max(0, selectedMedia.value.length - totalSlots.value),
);
const emptySlots = computed(() =>
    Math.max(0, totalSlots.value - selectedMedia.value.length),
);

const STEP_TITLES: Record<Step, { title: string; description: string }> = {
    media: {
        title: trans('posts.bulk.steps.media_title'),
        description: trans('posts.bulk.steps.media_description'),
    },
    platforms: {
        title: trans('posts.bulk.steps.platforms_title'),
        description: trans('posts.bulk.steps.platforms_description'),
    },
    schedule: {
        title: trans('posts.bulk.steps.schedule_title'),
        description: trans('posts.bulk.steps.schedule_description'),
    },
    prompt: {
        title: trans('posts.bulk.steps.prompt_title'),
        description: trans('posts.bulk.steps.prompt_description'),
    },
    confirm: {
        title: trans('posts.bulk.steps.confirm_title'),
        description: trans('posts.bulk.steps.confirm_description'),
    },
};

watch(
    step,
    (current) => {
        emit('update:step-header', STEP_TITLES[current]);
    },
    { immediate: true },
);

const openMediaPicker = () => {
    mediaPickerRef.value?.open();
};

const handleMediaSelect = (items: PickedMedia[]) => {
    // Append unique items, preserving order
    const existing = new Set(selectedMedia.value.map((m) => m.id));
    selectedMedia.value = [
        ...selectedMedia.value,
        ...items.filter((i) => !existing.has(i.id)),
    ];
};

const removeMedia = (id: string) => {
    selectedMedia.value = selectedMedia.value.filter((m) => m.id !== id);
};

const toggleAccount = (id: string) => {
    if (selectedAccountIds.value.includes(id)) {
        selectedAccountIds.value = selectedAccountIds.value.filter(
            (a) => a !== id,
        );
    } else {
        selectedAccountIds.value = [...selectedAccountIds.value, id];
    }
};

const addTime = () => {
    const value = newTime.value.trim();
    if (!/^([01]\d|2[0-3]):[0-5]\d$/.test(value)) return;
    if (selectedTimes.value.includes(value)) {
        newTime.value = '';
        return;
    }
    selectedTimes.value = [...selectedTimes.value, value].sort();
    newTime.value = '';
};

const removeTime = (time: string) => {
    selectedTimes.value = selectedTimes.value.filter((t) => t !== time);
};

const canAdvance = computed(() => {
    if (step.value === 'media') return selectedMedia.value.length > 0;
    if (step.value === 'platforms') return selectedAccountIds.value.length > 0;
    if (step.value === 'schedule') {
        return selectedDays.value.length > 0 && selectedTimes.value.length > 0;
    }
    if (step.value === 'prompt') {
        const activeMedia = selectedMedia.value.slice(0, postsToCreate.value);
        if (activeMedia.length === 0) return false;
        return activeMedia.every(
            (m) => (mediaTitles.value[m.id] || '').trim().length >= 3,
        );
    }
    return true;
});

const goNext = () => {
    if (!canAdvance.value) return;
    const order: Step[] = [
        'media',
        'platforms',
        'schedule',
        'prompt',
        'confirm',
    ];
    const idx = order.indexOf(step.value);
    if (idx < order.length - 1) step.value = order[idx + 1];
};

const goBack = () => {
    const order: Step[] = [
        'media',
        'platforms',
        'schedule',
        'prompt',
        'confirm',
    ];
    const idx = order.indexOf(step.value);
    if (idx > 0) {
        step.value = order[idx - 1];
    } else {
        emit('cancel');
    }
};

const distributionPreview = computed(() => {
    const days = [...selectedDays.value].sort();
    const times = [...selectedTimes.value].sort();
    const slots: string[] = [];
    for (const d of days) {
        for (const t of times) {
            slots.push(dayjs(`${d} ${t}`).format('D MMM YYYY [·] HH:mm'));
            if (slots.length >= postsToCreate.value) break;
        }
        if (slots.length >= postsToCreate.value) break;
    }
    return selectedMedia.value
        .slice(0, postsToCreate.value)
        .map((media, i) => ({
            media,
            slot: slots[i] ?? '',
        }));
});

const httpStart = useHttp<{
    media_ids: string[];
    platforms: { social_account_id: string }[];
    days: string[];
    times: string[];
    timezone: string;
    prompt: string;
    signature_id: string | null;
}>({
    media_ids: [],
    platforms: [],
    days: [],
    times: [],
    timezone: 'UTC',
    prompt: '',
    signature_id: null,
});

const submit = async () => {
    if (submitting.value || postsToCreate.value === 0) return;

    submitting.value = true;

    httpStart.media_ids = selectedMedia.value
        .slice(0, postsToCreate.value)
        .map((m) => m.id);
    httpStart.platforms = selectedAccountIds.value.map((id) => ({
        social_account_id: id,
    }));
    httpStart.days = [...selectedDays.value].sort();
    httpStart.times = [...selectedTimes.value].sort();
    httpStart.timezone = dayjs.tz.guess() || 'UTC';
    httpStart.prompt = promptText.value.trim();
    httpStart.signature_id =
        selectedSignatureId.value === 'none' ? null : selectedSignatureId.value;

    try {
        const data = (await httpStart.post(startRoute.url())) as {
            bulk_schedule_id: string;
        };
        router.visit(loadingRoute(data.bulk_schedule_id).url);
    } catch (err: unknown) {
        const error = err as { response?: { data?: { message?: string } } };
        toast.error(
            error.response?.data?.message ??
                trans('posts.bulk.errors.start_failed'),
        );
        submitting.value = false;
    }
};
</script>

<template>
    <div class="space-y-6">
        <button
            type="button"
            class="group inline-flex cursor-pointer items-center gap-1.5 text-sm font-semibold text-foreground/70 transition-colors hover:text-foreground"
            dusk="bulk-back"
            @click="goBack"
        >
            <span
                class="inline-flex size-7 items-center justify-center rounded-md border-2 border-foreground bg-card shadow-2xs transition-transform group-hover:-translate-x-0.5"
            >
                <IconArrowLeft
                    class="size-3.5 text-foreground"
                    stroke-width="2.5"
                />
            </span>
            {{ $t('posts.create.steps.back') }}
        </button>

        <!-- Step: Media -->
        <div v-if="step === 'media'" class="space-y-4">
            <div class="flex items-center justify-between">
                <Label class="text-sm font-bold">{{
                    $t('posts.bulk.steps.media_label', {
                        count: String(selectedMedia.length),
                    })
                }}</Label>
                <Button
                    type="button"
                    variant="outline"
                    dusk="bulk-pick-media"
                    @click="openMediaPicker"
                >
                    <IconPhoto class="size-4" />
                    {{ $t('posts.bulk.steps.media_pick') }}
                </Button>
            </div>

            <div
                v-if="selectedMedia.length === 0"
                class="flex flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed border-foreground/25 bg-card p-10 text-center"
            >
                <IconPhoto
                    class="size-10 text-foreground/40"
                    stroke-width="1.5"
                />
                <p class="text-sm font-semibold text-foreground/70">
                    {{ $t('posts.bulk.steps.media_empty') }}
                </p>
            </div>

            <div
                v-else
                class="grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-5"
            >
                <div
                    v-for="(media, i) in selectedMedia"
                    :key="media.id"
                    class="group relative overflow-hidden rounded-xl border-2 border-foreground bg-muted shadow-2xs"
                >
                    <div class="aspect-square">
                        <video
                            v-if="media.type === 'video'"
                            :src="media.url"
                            class="size-full object-cover"
                            muted
                        />
                        <img
                            v-else
                            :src="media.url"
                            :alt="media.original_filename ?? ''"
                            class="size-full object-cover"
                            loading="lazy"
                        />
                    </div>
                    <span
                        class="absolute top-1.5 left-1.5 inline-flex size-6 items-center justify-center rounded-full border-2 border-foreground bg-card text-xs font-bold text-foreground shadow-2xs"
                    >
                        {{ i + 1 }}
                    </span>
                    <button
                        type="button"
                        class="absolute top-1.5 right-1.5 inline-flex size-6 cursor-pointer items-center justify-center rounded-full border-2 border-foreground bg-rose-100 text-rose-700 shadow-2xs transition-colors hover:bg-rose-200"
                        :aria-label="$t('posts.bulk.steps.media_remove')"
                        @click="removeMedia(media.id)"
                    >
                        <IconX class="size-3.5" stroke-width="3" />
                    </button>
                </div>
            </div>

            <MediaPickerDialog
                ref="mediaPickerRef"
                @select="handleMediaSelect"
            />
        </div>

        <!-- Step: Platforms -->
        <div v-else-if="step === 'platforms'" class="space-y-3">
            <Label class="text-sm font-bold">{{
                $t('posts.bulk.steps.platforms_label')
            }}</Label>
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                <button
                    v-for="account in socialAccounts"
                    :key="account.id"
                    type="button"
                    class="relative flex cursor-pointer items-center gap-2.5 rounded-xl border-2 border-foreground bg-card p-3 text-left text-sm shadow-2xs transition-all hover:bg-foreground/5"
                    :class="{
                        '!bg-violet-100 shadow-md': selectedAccountIds.includes(
                            account.id,
                        ),
                    }"
                    :dusk="`bulk-account-${account.id}`"
                    @click="toggleAccount(account.id)"
                >
                    <span
                        class="inline-flex size-9 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-foreground bg-card shadow-2xs"
                    >
                        <img
                            v-if="account.avatar_url"
                            :src="account.avatar_url"
                            :alt="account.display_name"
                            class="size-full object-cover"
                        />
                        <img
                            v-else
                            :src="getPlatformLogo(account.platform)"
                            :alt="account.platform"
                            class="size-5"
                        />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p
                            class="truncate text-xs leading-tight font-bold text-foreground"
                        >
                            {{ account.display_name }}
                        </p>
                        <p
                            v-if="account.username"
                            class="truncate text-xs font-medium text-foreground/60"
                        >
                            @{{ account.username }}
                        </p>
                    </div>
                    <IconCheck
                        v-if="selectedAccountIds.includes(account.id)"
                        class="size-4 text-foreground"
                        stroke-width="3"
                    />
                </button>
            </div>
        </div>

        <!-- Step: Schedule -->
        <div v-else-if="step === 'schedule'" class="space-y-6">
            <div class="space-y-2">
                <Label class="text-sm font-bold">{{
                    $t('posts.bulk.steps.days_label')
                }}</Label>
                <MultiDateCalendar v-model:selected-dates="selectedDays" />
                <p
                    v-if="selectedDays.length"
                    class="text-xs text-foreground/70"
                >
                    {{
                        $t('posts.bulk.steps.days_count', {
                            count: String(selectedDays.length),
                        })
                    }}
                </p>
            </div>

            <div class="space-y-2">
                <Label class="text-sm font-bold">{{
                    $t('posts.bulk.steps.times_label')
                }}</Label>
                <div class="flex flex-wrap gap-2">
                    <span
                        v-for="time in selectedTimes"
                        :key="time"
                        class="inline-flex items-center gap-1.5 rounded-full border-2 border-foreground bg-violet-100 px-3 py-1 text-sm font-bold text-foreground shadow-2xs"
                    >
                        <IconClock class="size-3.5" />
                        {{ time }}
                        <button
                            type="button"
                            class="ml-1 cursor-pointer text-foreground/60 hover:text-foreground"
                            :aria-label="$t('posts.bulk.steps.times_remove')"
                            @click="removeTime(time)"
                        >
                            <IconX class="size-3.5" stroke-width="3" />
                        </button>
                    </span>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <Input
                        v-model="newTime"
                        type="time"
                        class="w-32"
                        dusk="bulk-new-time"
                        @keydown.enter.prevent="addTime"
                    />
                    <Button
                        type="button"
                        variant="outline"
                        dusk="bulk-add-time"
                        @click="addTime"
                    >
                        <IconPlus class="size-4" />
                        {{ $t('posts.bulk.steps.times_add') }}
                    </Button>
                </div>
            </div>

            <div
                class="rounded-xl border-2 border-foreground bg-amber-50 p-3.5 text-sm"
            >
                <p class="font-bold text-foreground">
                    {{
                        $t('posts.bulk.summary.will_create', {
                            count: String(postsToCreate),
                        })
                    }}
                </p>
                <p class="mt-0.5 text-xs text-foreground/70">
                    {{
                        $t('posts.bulk.summary.breakdown', {
                            media: String(selectedMedia.length),
                            days: String(selectedDays.length),
                            times: String(selectedTimes.length),
                            slots: String(totalSlots),
                        })
                    }}
                </p>
                <p
                    v-if="discardedMedia > 0"
                    class="mt-1 text-xs font-semibold text-rose-700"
                >
                    ⚠
                    {{
                        $t('posts.bulk.summary.discarded', {
                            count: String(discardedMedia),
                        })
                    }}
                </p>
                <p
                    v-else-if="emptySlots > 0"
                    class="mt-1 text-xs font-semibold text-foreground/60"
                >
                    {{
                        $t('posts.bulk.summary.empty_slots', {
                            count: String(emptySlots),
                        })
                    }}
                </p>
            </div>
        </div>

        <!-- Step: Prompt -->
        <div v-else-if="step === 'prompt'" class="space-y-4">
            <!-- Signature selection at the top -->
            <div
                class="space-y-2 pb-2"
                v-if="signatures && signatures.length > 0"
            >
                <Label class="text-sm font-bold">Assinatura (Opcional)</Label>
                <Select v-model="selectedSignatureId">
                    <SelectTrigger
                        dusk="bulk-signature-trigger"
                        class="max-w-xs border-2 border-foreground"
                    >
                        <SelectValue
                            placeholder="Selecione uma assinatura..."
                        />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="none">Nenhuma assinatura</SelectItem>
                        <SelectItem
                            v-for="sig in signatures"
                            :key="sig.id"
                            :value="String(sig.id)"
                        >
                            {{ sig.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- List of posts to configure titles/captions -->
            <div class="space-y-3">
                <Label class="text-sm font-bold">{{
                    $t('posts.bulk.steps.prompt_title')
                }}</Label>
                <p class="text-xs text-foreground/60">
                    {{ $t('posts.bulk.steps.prompt_description') }}
                </p>

                <div
                    class="max-h-[450px] space-y-4 overflow-y-auto rounded-xl border-2 border-foreground bg-card p-3 pr-2"
                >
                    <div
                        v-for="(media, i) in selectedMedia.slice(
                            0,
                            postsToCreate,
                        )"
                        :key="media.id"
                        class="flex items-center gap-4 rounded-xl border border-foreground/10 bg-muted/20 p-4"
                    >
                        <!-- Column 1: Media Preview / Thumbnail -->
                        <div
                            class="group/thumb relative size-20 shrink-0 cursor-pointer overflow-hidden rounded-xl border-2 border-foreground bg-muted"
                            @click="previewMedia(media)"
                        >
                            <video
                                v-if="media.type === 'video'"
                                :src="media.url"
                                class="size-full object-cover"
                                muted
                            />
                            <img
                                v-else
                                :src="media.url"
                                class="size-full object-cover"
                            />
                            <!-- Play icon overlay for videos -->
                            <div
                                v-if="media.type === 'video'"
                                class="absolute inset-0 flex items-center justify-center bg-black/30 transition-colors group-hover/thumb:bg-black/40"
                            >
                                <IconPlayerPlay
                                    class="size-6 fill-current text-white"
                                />
                            </div>
                        </div>

                        <!-- Column 2: Title / Content Input -->
                        <div class="flex-1">
                            <Textarea
                                v-model="mediaTitles[media.id]"
                                :placeholder="
                                    $t('posts.bulk.steps.prompt_placeholder')
                                "
                                class="min-h-[70px] resize-none"
                                rows="2"
                            />
                            <!-- Signature preview inside the card -->
                            <div
                                v-if="selectedSignatureContent"
                                class="mt-2 rounded-lg border border-dashed border-foreground/20 bg-muted/30 p-2 text-xs text-foreground/60 whitespace-pre-wrap"
                            >
                                <span class="font-bold block text-[10px] uppercase tracking-wider text-foreground/40 mb-1">
                                    Assinatura:
                                </span>
                                {{ selectedSignatureContent }}
                            </div>
                        </div>
                    </div>
                </div>
                <p class="text-xs text-foreground/60">
                    {{ $t('posts.bulk.steps.prompt_hint') }}
                </p>
            </div>
        </div>

        <!-- Step: Confirm -->
        <div v-else-if="step === 'confirm'" class="space-y-4">
            <div
                class="rounded-xl border-2 border-foreground bg-card p-4 shadow-2xs"
            >
                <p class="text-base font-bold text-foreground">
                    {{
                        $t('posts.bulk.summary.will_create', {
                            count: String(postsToCreate),
                        })
                    }}
                </p>
                <p
                    v-if="discardedMedia > 0"
                    class="mt-1 text-xs font-semibold text-rose-700"
                >
                    ⚠
                    {{
                        $t('posts.bulk.summary.discarded', {
                            count: String(discardedMedia),
                        })
                    }}
                </p>
            </div>

            <div
                class="max-h-80 space-y-2 overflow-y-auto rounded-xl border-2 border-foreground bg-card p-3"
            >
                <div
                    v-for="(row, i) in distributionPreview"
                    :key="row.media.id"
                    class="flex items-center gap-3 rounded-lg border border-foreground/15 p-2"
                >
                    <span
                        class="inline-flex size-6 shrink-0 items-center justify-center rounded-full border-2 border-foreground bg-card text-xs font-bold text-foreground"
                    >
                        {{ i + 1 }}
                    </span>
                    <div
                        class="size-12 shrink-0 overflow-hidden rounded-md border-2 border-foreground bg-muted"
                    >
                        <video
                            v-if="row.media.type === 'video'"
                            :src="row.media.url"
                            class="size-full object-cover"
                            muted
                        />
                        <img
                            v-else
                            :src="row.media.url"
                            :alt="''"
                            class="size-full object-cover"
                        />
                    </div>
                    <p class="text-xs font-semibold text-foreground">
                        {{ row.slot }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Nav buttons -->
        <div class="flex items-center justify-end gap-2 pt-2">
            <Button
                v-if="step !== 'confirm'"
                type="button"
                :disabled="!canAdvance"
                dusk="bulk-next"
                @click="goNext"
            >
                {{ $t('posts.bulk.actions.next') }}
            </Button>
            <Button
                v-else
                type="button"
                :disabled="submitting || postsToCreate === 0"
                dusk="bulk-submit"
                @click="submit"
            >
                {{ $t('posts.bulk.actions.publish') }}
            </Button>
        </div>
    </div>

    <ImagePreviewDialog ref="lightboxRef" />
</template>
