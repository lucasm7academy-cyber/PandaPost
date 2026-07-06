<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import {
    IconBrandGoogleDrive,
    IconFolderPlus,
    IconTrash,
} from '@tabler/icons-vue';
import { ref } from 'vue';

import GoogleDriveBrowser from '@/components/assets/GoogleDriveBrowser.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import PageHeader from '@/components/PageHeader.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { destroy, index as googleDriveFolders, store, toggle } from '@/routes/app/workspace/google-drive-folders';
import type { Workspace } from '@/types';

const props = defineProps<{
    workspace: Workspace;
    folders: Array<{
        id: string;
        folder_id: string;
        folder_name: string;
        folder_link: string;
        is_active: boolean;
        added_by: string;
        created_at: string;
    }>;
}>();

const showForm = ref(false);

const onSuccess = () => {
    showForm.value = false;
};

const toggleFolder = (folderId: string) => {
    router.patch(toggle.url({ workspace: props.workspace.id, folder: folderId }));
};

const deleteFolder = (folderId: string) => {
    if (confirm('Tem certeza que deseja remover esta pasta?')) {
        router.delete(destroy.url({ workspace: props.workspace.id, folder: folderId }));
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Google Drive Folders" />

        <PageHeader title="Google Drive" :icon="IconBrandGoogleDrive">
            <p class="text-sm text-muted-foreground">
                Conecte pastas do Google Drive para usar como fonte de mídia
            </p>
        </PageHeader>

        <div class="space-y-6">
            <!-- Add Folder Section -->
            <div class="rounded-lg border border-border bg-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-foreground">Adicionar Pasta</h3>
                    <Button
                        v-if="!showForm"
                        size="sm"
                        @click="showForm = true"
                        class="gap-2"
                    >
                        <IconFolderPlus class="w-4 h-4" />
                        Nova Pasta
                    </Button>
                </div>

                <Form
                    v-if="showForm"
                    v-bind="store.form({ workspace: workspace.id })"
                    class="space-y-4"
                    v-slot="{ errors, processing }"
                    @success="onSuccess"
                >
                    <div class="space-y-2">
                        <Label for="folder-name">Nome da Pasta</Label>
                        <Input
                            id="folder-name"
                            name="folder_name"
                            placeholder="Ex: Imagens de Campanha"
                            type="text"
                        />
                        <InputError :message="errors.folder_name" />
                    </div>

                    <div class="space-y-2">
                        <Label for="folder-link">Link da Pasta (Google Drive)</Label>
                        <Input
                            id="folder-link"
                            name="folder_link"
                            placeholder="https://drive.google.com/drive/folders/..."
                            type="url"
                        />
                        <p class="text-xs text-muted-foreground">
                            Cole o link de compartilhamento da pasta do seu Google Drive
                        </p>
                        <InputError :message="errors.folder_link" />
                    </div>

                    <div class="flex gap-2">
                        <Button
                            type="submit"
                            :disabled="processing"
                        >
                            {{ processing ? 'Adicionando...' : 'Adicionar Pasta' }}
                        </Button>
                        <Button
                            variant="outline"
                            type="button"
                            @click="showForm = false"
                            :disabled="processing"
                        >
                            Cancelar
                        </Button>
                    </div>
                </Form>
            </div>

            <!-- Folders List Section -->
            <div class="rounded-lg border border-border bg-card">
                <div class="px-6 py-4 border-b border-border">
                    <h3 class="font-semibold text-foreground">Pastas Conectadas</h3>
                </div>

                <div v-if="folders.length === 0" class="px-6 py-8 text-center">
                    <p class="text-muted-foreground text-sm">
                        Nenhuma pasta conectada ainda
                    </p>
                </div>

                <Table v-else>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nome da Pasta</TableHead>
                            <TableHead>Adicionada em</TableHead>
                            <TableHead class="w-20">Status</TableHead>
                            <TableHead class="w-20 text-right">Ações</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="folder in folders" :key="folder.id">
                            <TableCell>
                                <a
                                    :href="folder.folder_link"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-violet-600 hover:underline font-medium text-sm"
                                >
                                    {{ folder.folder_name }}
                                </a>
                            </TableCell>
                            <TableCell class="text-sm text-muted-foreground">
                                {{ folder.created_at }}
                            </TableCell>
                            <TableCell>
                                <Switch
                                    :checked="folder.is_active"
                                    @update:checked="() => toggleFolder(folder.id)"
                                />
                            </TableCell>
                            <TableCell class="text-right">
                                <button
                                    @click="() => deleteFolder(folder.id)"
                                    class="text-red-500 hover:text-red-700 transition-colors"
                                    title="Remover pasta"
                                >
                                    <IconTrash class="w-4 h-4" />
                                </button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- File Browser Section -->
            <div v-if="folders.length > 0" class="rounded-lg border border-border bg-card p-6">
                <div class="mb-4">
                    <h3 class="font-semibold text-foreground">Explorador de Arquivos</h3>
                    <p class="text-sm text-muted-foreground mt-1">
                        Navegue pelos arquivos das suas pastas do Google Drive
                    </p>
                </div>

                <GoogleDriveBrowser
                    mode="standalone"
                    :selected="[]"
                    @update:selected="() => {}"
                />
            </div>
        </div>
    </AppLayout>
</template>
