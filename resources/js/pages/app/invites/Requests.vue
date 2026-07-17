<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { IconCheck, IconInbox, IconUserPlus, IconX } from '@tabler/icons-vue';

import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import useDate from '@/date';
import AppLayout from '@/layouts/AppLayout.vue';
import { accept, decline } from '@/routes/app/invites';

interface InviteRequest {
    id: string;
    email: string;
    created_at: string;
    account: {
        id: string | null;
        name: string | null;
    };
    workspace: {
        id: string;
        name: string;
    } | null;
    role: {
        value: string;
        label: string;
    };
    invited_by: {
        id: string | null;
        name: string | null;
        email: string | null;
    };
}

defineProps<{
    invites: InviteRequest[];
}>();
</script>

<template>
    <Head :title="$t('invites.requests.page_title')" />

    <AppLayout>
        <div class="mx-auto max-w-3xl space-y-8 px-6 py-8">
            <PageHeader
                :title="$t('invites.requests.title')"
                :description="$t('invites.requests.description')"
            />

            <div v-if="invites.length > 0" class="space-y-4">
                <Card v-for="invite in invites" :key="invite.id">
                    <CardContent
                        class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="inline-flex size-10 shrink-0 -rotate-3 items-center justify-center rounded-xl border-2 border-foreground bg-violet-200 shadow-2xs"
                            >
                                <IconUserPlus
                                    class="size-5 text-foreground"
                                    stroke-width="2"
                                />
                            </div>
                            <div class="space-y-1">
                                <p class="text-base font-bold text-foreground">
                                    {{
                                        $t('invites.requests.invited_to', {
                                            workspace:
                                                invite.workspace?.name ??
                                                invite.account.name ??
                                                '',
                                        })
                                    }}
                                </p>
                                <div
                                    class="space-y-0.5 text-sm text-muted-foreground"
                                >
                                    <p>
                                        <span
                                            class="font-medium text-foreground/80"
                                            >{{
                                                $t('invites.requests.role')
                                            }}:</span
                                        >
                                        {{ invite.role.label }}
                                    </p>
                                    <p v-if="invite.invited_by.name">
                                        <span
                                            class="font-medium text-foreground/80"
                                            >{{
                                                $t(
                                                    'invites.requests.invited_by',
                                                )
                                            }}:</span
                                        >
                                        {{ invite.invited_by.name }}
                                    </p>
                                    <p class="text-xs">
                                        {{
                                            useDate.diffForHumans(
                                                invite.created_at,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex shrink-0 gap-2">
                            <Button as-child variant="outline" size="sm">
                                <Link
                                    :href="decline.url(invite.id)"
                                    method="post"
                                    as="button"
                                >
                                    <IconX class="size-4" />
                                    {{ $t('invites.requests.decline') }}
                                </Link>
                            </Button>
                            <Button as-child size="sm">
                                <Link
                                    :href="accept.url(invite.id)"
                                    method="post"
                                    as="button"
                                >
                                    <IconCheck class="size-4" />
                                    {{ $t('invites.requests.accept') }}
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div
                v-else
                class="flex flex-col items-center justify-center gap-4 rounded-2xl border-2 border-dashed border-foreground/20 bg-card px-6 py-16 text-center"
            >
                <div
                    class="inline-flex size-14 -rotate-3 items-center justify-center rounded-2xl border-2 border-foreground bg-violet-200 shadow-2xs"
                >
                    <IconInbox
                        class="size-7 text-foreground"
                        stroke-width="2"
                    />
                </div>
                <div class="space-y-1">
                    <p
                        class="text-lg font-bold text-foreground"
                        style="font-family: var(--font-display)"
                    >
                        {{ $t('invites.requests.empty_title') }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{ $t('invites.requests.empty_description') }}
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
