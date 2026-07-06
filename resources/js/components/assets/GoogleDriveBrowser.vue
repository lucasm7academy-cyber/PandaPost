<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import {
    IconBrandGoogleDrive,
    IconCheck,
    IconFolderOpen,
    IconLoader2,
    IconPhoto,
    IconVideo,
} from '@tabler/icons-vue';
import { trans } from 'laravel-vue-i18n';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue-sonner';

import EmptyState from '@/components/EmptyState.vue';
import GoogleDriveConnect from '@/components/assets/GoogleDriveConnect.vue';
import { Skeleton } from '@/components/ui/skeleton';
import { download as downloadFromFolder } from '@/routes/app/assets/google-drive/from-folder';
import { files as folderFilesRoute, index as foldersRoute } from '@/routes/app/workspace/google-drive-folders';

interface GoogleDriveFolder {
    id: string;
    folder_id: string;
    folder_name: string;
    folder_link: string;
    is_active: boolean;
    added_by: string;
    created_at: string;
}

interface GoogleDriveFile {
    id: string;
    name: string;
    mimeType: string;
    thumbnailLink?: string;
    webContentLink?: string;
    modifiedTime: string;
    size?: number;
}

interface PickedMedia {
    id: string;
    path: string;
    url: string;
    type: string;
    mime_type: string;
    original_filename?: string;
    size?: number;
    meta?: Record<string, unknown>;
    source?: 'ai' | 'unsplash' | 'giphy' | 'google-drive' | string;
}

const props = defineProps<{
    mode: 'standalone' | 'picker';
    selected: PickedMedia[];
}>();

const emit = defineEmits<{
    'update:selected': [selected: PickedMedia[]];
}>();

const page = usePage();
const workspace = computed(() => page.props.auth.currentWorkspace as { id: string } | null);

const folders = ref<GoogleDriveFolder[]>([]);
const files = ref<GoogleDriveFile[]>([]);
const selectedFolder = ref<GoogleDriveFolder | null>(null);
const loading = ref(false);
const downloadingIds = ref<Set<string>>(new Set());
const optimisticSelectedIds = ref<Set<string>>(new Set());
const downloadQueue = ref<GoogleDriveFile[]>([]);
const isQueueProcessing = ref(false);
const hasConnection = ref<boolean | null>(null);
const lastSelectedFileIndex = ref<number | null>(null);

const selectedDriveIds = computed(
    () =>
        new Set(
            props.selected
                .filter((m) => m.source === 'google-drive')
                .map((m) => (m.meta as { google_drive_id?: string } | undefined)?.google_drive_id)
                .filter((v): v is string => !!v),
        ),
);
const isFileSelected = (fileId: string) => selectedDriveIds.value.has(fileId) || optimisticSelectedIds.value.has(fileId);
const selectionIndex = (id: string) => {
    // Check if it's actually in props.selected
    const idx = props.selected.findIndex(m => m.id === id || (m.meta as { google_drive_id?: string })?.google_drive_id === id);
    if (idx !== -1) return idx + 1;
    
    // Find index in optimistic set
    const optimisticArray = Array.from(optimisticSelectedIds.value);
    const optimisticIdx = optimisticArray.indexOf(id);
    if (optimisticIdx !== -1) {
        return props.selected.length + optimisticIdx + 1;
    }
    
    return props.selected.length + 1;
};

const isImage = (mimeType: string) => mimeType.startsWith('image/');
const isVideo = (mimeType: string) => mimeType.startsWith('video/');

const loadFolders = async () => {
    if (!workspace.value) {
        return;
    }
    loading.value = true;
    try {
        const url = foldersRoute.url({ workspace: workspace.value.id });
        const response = await fetch(url, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        if (!response.ok) {
            throw new Error('Failed to load folders');
        }
        const data = (await response.json()) as { folders: GoogleDriveFolder[] };
        folders.value = data.folders.filter((f) => f.is_active);
        hasConnection.value = true;
    } catch (error) {
        console.error(error);
        toast.error(trans('assets.google_drive.error_loading'));
        hasConnection.value = false;
    } finally {
        loading.value = false;
    }
};

const loadFiles = async (folder: GoogleDriveFolder) => {
    if (!workspace.value) {
        return;
    }
    loading.value = true;
    selectedFolder.value = folder;
    files.value = [];
    lastSelectedFileIndex.value = null;

    try {
        const url = folderFilesRoute.url({ workspace: workspace.value.id, folder: folder.id });
        const response = await fetch(url, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });

        const data = (await response.json()) as { files?: GoogleDriveFile[]; error?: string };

        if (!response.ok) {
            if (data.error === 'Google Drive not connected' || response.status === 400) {
                hasConnection.value = false;
                selectedFolder.value = null;
                toast.error(trans('assets.google_drive.error_no_connection'));
                return;
            }
            throw new Error(data.error || 'Failed to load files');
        }

        if (data.files) {
            files.value = data.files;
        }
    } catch (error) {
        console.error('Error loading files:', error);
        selectedFolder.value = null;
        toast.error(trans('assets.google_drive.error_loading'));
    } finally {
        loading.value = false;
    }
};

const processDownloadQueue = async () => {
    if (isQueueProcessing.value || downloadQueue.value.length === 0) return;
    
    isQueueProcessing.value = true;
    
    while (downloadQueue.value.length > 0) {
        const file = downloadQueue.value.shift();
        if (!file) continue;
        
        if (selectedDriveIds.value.has(file.id)) {
            continue;
        }

        downloadingIds.value.add(file.id);
        try {
            const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
            const response = await fetch(
                downloadFromFolder.url({ workspace: workspace.value!.id, folder: selectedFolder.value!.id }),
                {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        file_id: file.id,
                        file_name: file.name,
                        mime_type: file.mimeType,
                    }),
                },
            );

            if (!response.ok) {
                const errorData = (await response.json().catch(() => ({}))) as { error?: string };
                toast.error(errorData.error || trans('assets.google_drive.error_loading'));
                optimisticSelectedIds.value.delete(file.id);
                continue;
            }

            const media = (await response.json()) as PickedMedia;
            emit('update:selected', [...props.selected, media]);

            if (props.mode === 'standalone') {
                toast.success(trans('assets.google_drive.imported', { name: file.name }));
            }
        } catch (error) {
            console.error(error);
            toast.error(trans('assets.google_drive.error_loading'));
        } finally {
            optimisticSelectedIds.value.delete(file.id);
            downloadingIds.value.delete(file.id);
        }
    }
    
    isQueueProcessing.value = false;
};

const enqueueFile = (file: GoogleDriveFile) => {
    if (!workspace.value || !selectedFolder.value) {
        return;
    }
    if (optimisticSelectedIds.value.has(file.id) || selectedDriveIds.value.has(file.id)) {
        return;
    }
    
    optimisticSelectedIds.value.add(file.id);
    downloadQueue.value.push(file);
    void processDownloadQueue();
};

const pickFile = (file: GoogleDriveFile, event?: MouseEvent, index?: number) => {
    if (!workspace.value || !selectedFolder.value) {
        return;
    }

    if (event?.shiftKey && lastSelectedFileIndex.value !== null && index !== undefined) {
        const start = Math.min(lastSelectedFileIndex.value, index);
        const end = Math.max(lastSelectedFileIndex.value, index);
        const filesToSelect = files.value.slice(start, end + 1);

        const newFiles = filesToSelect.filter(f => !isFileSelected(f.id));

        for (const f of newFiles) {
            enqueueFile(f);
        }
        lastSelectedFileIndex.value = index;
        return;
    }

    lastSelectedFileIndex.value = index ?? null;

    // Toggle off if already in selection.
    if (isFileSelected(file.id)) {
        optimisticSelectedIds.value.delete(file.id);
        
        const queueIndex = downloadQueue.value.findIndex(f => f.id === file.id);
        if (queueIndex > -1) {
            downloadQueue.value.splice(queueIndex, 1);
        }
        
        const next = props.selected.filter((m) => {
            const driveId = (m.meta as { google_drive_id?: string } | undefined)?.google_drive_id;
            return driveId !== file.id;
        });
        emit('update:selected', next);
        return;
    }

    enqueueFile(file);
};

const goBackToFolders = () => {
    selectedFolder.value = null;
    files.value = [];
    lastSelectedFileIndex.value = null;
};

const handleConnected = async () => {
    hasConnection.value = true;
    await loadFolders();
};

onMounted(async () => {
    await loadFolders();
});
</script>

<template>
    <div>
        <GoogleDriveConnect v-if="hasConnection === false" @connected="handleConnected" />

        <div v-else-if="loading && folders.length === 0" class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
            <Skeleton v-for="i in 8" :key="i" class="aspect-square rounded-xl" />
        </div>

        <EmptyState
            v-else-if="folders.length === 0"
            :icon="IconBrandGoogleDrive"
            :title="trans('assets.google_drive.empty.title')"
            :description="trans('assets.google_drive.empty.description')"
        />

        <!-- Folders list -->
        <div v-else-if="!selectedFolder" class="space-y-3">
            <button
                v-for="folder in folders"
                :key="folder.id"
                type="button"
                class="flex w-full items-center justify-between gap-3 rounded-lg border-2 border-border bg-card p-4 text-left transition-all hover:-translate-y-0.5 hover:shadow-md"
                @click="loadFiles(folder)"
            >
                <div class="flex min-w-0 flex-1 items-center gap-3">
                    <div class="flex size-12 flex-shrink-0 items-center justify-center rounded-lg bg-violet-100">
                        <IconFolderOpen class="size-6 text-violet-600" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-semibold text-foreground">{{ folder.folder_name }}</p>
                        <p class="truncate text-sm text-muted-foreground">{{ folder.folder_id }}</p>
                    </div>
                </div>
            </button>
        </div>

        <!-- Files grid -->
        <div v-else class="space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <IconFolderOpen class="size-5 text-violet-600" />
                    <h3 class="text-lg font-semibold">{{ selectedFolder.folder_name }}</h3>
                </div>
                <button type="button" class="text-sm text-blue-600 hover:underline" @click="goBackToFolders">
                    ← {{ trans('common.back') }}
                </button>
            </div>

            <div v-if="loading" class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                <Skeleton v-for="i in 8" :key="i" class="aspect-square rounded-xl" />
            </div>

            <div v-else-if="files.length === 0" class="py-8 text-center text-muted-foreground">
                {{ trans('assets.google_drive.no_files') }}
            </div>

            <div v-else class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                <button
                    v-for="(file, index) in files"
                    :key="file.id"
                    type="button"
                    class="group relative cursor-pointer overflow-hidden rounded-xl border-2 border-foreground bg-muted text-left shadow-2xs transition-all hover:-translate-y-0.5 hover:shadow-md"
                    :class="[isFileSelected(file.id) || downloadingIds.has(file.id) ? 'ring-2 ring-primary ring-offset-2 ring-offset-background' : '']"
                    :disabled="downloadingIds.has(file.id)"
                    @click="pickFile(file, $event, index)"
                >
                    <div class="flex aspect-square items-center justify-center bg-muted">
                        <img
                            v-if="file.thumbnailLink && isImage(file.mimeType)"
                            :src="file.thumbnailLink"
                            :alt="file.name"
                            class="size-full object-cover"
                            referrerpolicy="no-referrer"
                            loading="lazy"
                        />
                        <img
                            v-else-if="file.thumbnailLink"
                            :src="file.thumbnailLink"
                            :alt="file.name"
                            class="size-full object-cover"
                            referrerpolicy="no-referrer"
                            loading="lazy"
                        />
                        <IconVideo v-else-if="isVideo(file.mimeType)" class="size-10 text-foreground/50" />
                        <IconPhoto v-else class="size-10 text-foreground/50" />
                    </div>

                    <div class="space-y-0.5 bg-card p-2">
                        <p class="truncate text-xs font-semibold text-foreground" :title="file.name">{{ file.name }}</p>
                        <p class="truncate text-xs text-muted-foreground">{{ file.mimeType }}</p>
                    </div>

                    <div
                        v-if="downloadingIds.has(file.id)"
                        class="absolute inset-0 z-0 flex items-center justify-center bg-foreground/40"
                    >
                        <IconLoader2 class="size-6 animate-spin text-white" />
                    </div>

                    <div
                        v-if="isFileSelected(file.id) || downloadingIds.has(file.id)"
                        class="absolute right-2 top-2 z-10 inline-flex size-6 items-center justify-center rounded-full border-2 border-foreground bg-primary text-xs font-bold text-primary-foreground shadow-2xs"
                    >
                        {{ selectionIndex(file.id) }}
                    </div>
                </button>
            </div>
        </div>
    </div>
</template>
