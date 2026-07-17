<script setup lang="ts">
import { Head, InfiniteScroll, router } from '@inertiajs/vue3';
import {
    IconAffiliate,
    IconAlertCircle,
    IconDots,
    IconExternalLink,
    IconRefresh,
    IconSearch,
    IconTrash,
} from '@tabler/icons-vue';
import { trans } from 'laravel-vue-i18n';
import { computed, ref, watch } from 'vue';

import { index as accountsIndex } from '@/actions/App/Http/Controllers/Auth/SocialController';
import AddSocialDialog, {
    type AvailablePlatform,
} from '@/components/accounts/AddSocialDialog.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import EmptyState from '@/components/EmptyState.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableLoadMore,
    TableRow,
} from '@/components/ui/table';
import { useFeatureAccess } from '@/composables/useFeatureAccess';
import {
    getPlatformLabel,
    getPlatformLogo,
} from '@/composables/usePlatformLogo';
import { useUpgradeDialog } from '@/composables/useUpgradeDialog';
import date from '@/date';
import debounce from '@/debounce';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    disconnect as disconnectAccount,
    toggle as toggleAccount,
} from '@/routes/app/accounts';

interface SocialAccount {
    id: string;
    platform: string;
    platform_user_id: string;
    username: string;
    display_name: string;
    avatar_url: string;
    profile_url: string | null;
    status: 'connected' | 'disconnected' | 'token_expired' | null;
    is_active: boolean;
    error_message: string | null;
    last_used_at: string | null;
    created_at: string;
}

interface ScrollAccounts {
    data: SocialAccount[];
    meta: { hasNextPage: boolean };
}

interface Props {
    accounts: ScrollAccounts;
    platforms: AvailablePlatform[];
    filters: { search: string };
    openDialog: boolean;
}

const props = defineProps<Props>();

const isAddDialogOpen = ref(props.openDialog);
const deleteModal = ref<InstanceType<typeof ConfirmDeleteModal> | null>(null);
const searchQuery = ref(props.filters.search);

const { canConnectSocialAccount } = useFeatureAccess();
const { openUpgrade } = useUpgradeDialog();

const handleAddClick = () => {
    if (!canConnectSocialAccount.value) {
        openUpgrade(
            trans('billing.upgrade_dialog.reasons.social_account_limit'),
        );
        return;
    }
    isAddDialogOpen.value = true;
};

const search = debounce(() => {
    router.get(
        accountsIndex.url(),
        { search: searchQuery.value || undefined },
        { preserveState: true, preserveScroll: true, reset: ['accounts'] },
    );
}, 300);

watch(searchQuery, () => search());

const hasActiveSearch = computed(() => Boolean(searchQuery.value?.trim()));

const isDisconnected = (account: SocialAccount): boolean =>
    account.status === 'disconnected' || account.status === 'token_expired';

const openOAuthPopup = (platformValue: string) => {
    const url = `/connect/${platformValue}`;
    const w = 600;
    const h = 700;
    const left = window.screenX + (window.outerWidth - w) / 2;
    const top = window.screenY + (window.outerHeight - h) / 2;
    window.open(
        url,
        'oauth-popup',
        `width=${w},height=${h},left=${left},top=${top},scrollbars=yes,resizable=yes`,
    );
};

const handleToggle = (accountId: string) => {
    router.put(toggleAccount.url(accountId), {}, { preserveScroll: true });
};

const handleDisconnect = (account: SocialAccount) => {
    deleteModal.value?.open({
        url: disconnectAccount.url(account.id),
        confirmText: account.username,
    });
};
</script>

<template>
    <Head :title="$t('accounts.page_title')" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 px-4 py-6 md:px-6 md:py-8">
            <PageHeader :title="$t('accounts.page_title')" />

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="relative w-full sm:w-auto">
                    <IconSearch
                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="searchQuery"
                        :placeholder="$t('accounts.search')"
                        class="w-full sm:w-64 pl-9"
                    />
                </div>

                <Button @click="handleAddClick" class="w-full sm:w-auto">
                    {{ $t('accounts.add_social') }}
                </Button>
            </div>

            <EmptyState
                v-if="accounts.data.length === 0"
                :icon="IconAffiliate"
                :title="
                    hasActiveSearch
                        ? $t('accounts.no_search_results')
                        : $t('accounts.no_accounts')
                "
                :description="
                    hasActiveSearch
                        ? $t('accounts.try_different_search')
                        : $t('accounts.no_accounts_description')
                "
            />

            <div v-else>
                <InfiniteScroll
                    data="accounts"
                    items-element="#accounts-body"
                    preserve-url
                >
                    <Table class="block md:table">
                        <TableHeader class="hidden md:table-header-group">
                            <TableRow>
                                <TableHead>{{
                                    $t('accounts.table.account')
                                }}</TableHead>
                                <TableHead>{{
                                    $t('accounts.table.platform')
                                }}</TableHead>
                                <TableHead>{{
                                    $t('accounts.table.status')
                                }}</TableHead>
                                <TableHead>{{
                                    $t('accounts.table.last_used')
                                }}</TableHead>
                                <TableHead>{{
                                    $t('accounts.table.added')
                                }}</TableHead>
                                <TableHead class="text-right">{{
                                    $t('accounts.table.active')
                                }}</TableHead>
                                <TableHead class="w-10" />
                            </TableRow>
                        </TableHeader>
                        <TableBody id="accounts-body" class="block md:table-row-group">
                            <TableRow
                                v-for="account in accounts.data"
                                :key="account.id"
                                class="block md:table-row border-b border-muted/60 md:border-b-0 py-4 md:py-0"
                            >
                                <TableCell class="block md:table-cell p-0 md:px-4 md:py-3 w-full md:w-auto">
                                    <!-- Desktop View -->
                                    <div class="hidden md:flex items-center gap-3">
                                        <div class="relative shrink-0">
                                            <Avatar
                                                class="size-10 rounded-full border-2 border-foreground shadow-2xs"
                                            >
                                                <AvatarImage
                                                    v-if="account.avatar_url"
                                                    :src="account.avatar_url"
                                                />
                                                <AvatarFallback
                                                    class="rounded-full bg-violet-100 font-bold text-foreground"
                                                >
                                                    {{
                                                        account.display_name?.charAt(
                                                            0,
                                                        )
                                                    }}
                                                </AvatarFallback>
                                            </Avatar>
                                            <span
                                                class="absolute -right-1 -bottom-1 inline-flex size-5 items-center justify-center overflow-hidden rounded-full border-2 border-foreground bg-card shadow-2xs"
                                            >
                                                <img
                                                    :src="
                                                        getPlatformLogo(
                                                            account.platform,
                                                        )
                                                    "
                                                    :alt="account.platform"
                                                    class="size-full object-cover"
                                                />
                                            </span>
                                        </div>
                                        <div class="min-w-0">
                                            <a
                                                v-if="account.profile_url"
                                                :href="account.profile_url"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1 truncate text-sm font-bold text-foreground hover:underline"
                                            >
                                                {{ account.display_name }}
                                                <IconExternalLink
                                                    class="size-3.5 opacity-60"
                                                />
                                            </a>
                                            <p
                                                v-else
                                                class="truncate text-sm font-bold text-foreground"
                                            >
                                                {{ account.display_name }}
                                            </p>
                                            <p
                                                class="truncate text-xs font-medium text-foreground/60"
                                            >
                                                @{{
                                                    account.username ||
                                                    account.display_name
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Mobile View Card -->
                                    <div class="flex flex-col gap-3 md:hidden">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <div class="relative shrink-0">
                                                    <Avatar
                                                        class="size-10 rounded-full border-2 border-foreground shadow-2xs"
                                                    >
                                                        <AvatarImage
                                                            v-if="account.avatar_url"
                                                            :src="account.avatar_url"
                                                        />
                                                        <AvatarFallback
                                                            class="rounded-full bg-violet-100 font-bold text-foreground"
                                                        >
                                                            {{
                                                                account.display_name?.charAt(
                                                                    0,
                                                                )
                                                            }}
                                                        </AvatarFallback>
                                                    </Avatar>
                                                    <span
                                                        class="absolute -right-1 -bottom-1 inline-flex size-5 items-center justify-center overflow-hidden rounded-full border-2 border-foreground bg-card shadow-2xs"
                                                    >
                                                        <img
                                                            :src="
                                                                getPlatformLogo(
                                                                    account.platform,
                                                                )
                                                            "
                                                            :alt="account.platform"
                                                            class="size-full object-cover"
                                                        />
                                                    </span>
                                                </div>
                                                <div class="min-w-0">
                                                    <a
                                                        v-if="account.profile_url"
                                                        :href="account.profile_url"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="inline-flex items-center gap-1 truncate text-sm font-bold text-foreground hover:underline"
                                                    >
                                                        {{ account.display_name }}
                                                        <IconExternalLink
                                                            class="size-3.5 opacity-60"
                                                        />
                                                    </a>
                                                    <p
                                                        v-else
                                                        class="truncate text-sm font-bold text-foreground"
                                                    >
                                                        {{ account.display_name }}
                                                    </p>
                                                    <p
                                                        class="truncate text-xs font-medium text-foreground/60"
                                                    >
                                                        @{{
                                                            account.username ||
                                                            account.display_name
                                                        }}
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <Switch
                                                    :model-value="account.is_active"
                                                    :disabled="isDisconnected(account)"
                                                    @update:model-value="
                                                        handleToggle(account.id)
                                                    "
                                                />
                                                
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger as-child>
                                                        <Button
                                                            variant="ghost"
                                                            size="icon"
                                                            class="size-8"
                                                        >
                                                            <IconDots class="size-4" />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end">
                                                        <DropdownMenuItem
                                                            v-if="isDisconnected(account)"
                                                            @click="
                                                                openOAuthPopup(
                                                                    account.platform,
                                                                )
                                                            "
                                                        >
                                                            <IconRefresh class="size-4" />
                                                            {{
                                                                $t(
                                                                    'accounts.reconnect_account',
                                                                )
                                                            }}
                                                        </DropdownMenuItem>
                                                        <DropdownMenuSeparator
                                                            v-if="isDisconnected(account)"
                                                        />
                                                        <DropdownMenuItem
                                                            variant="destructive"
                                                            @click="
                                                                handleDisconnect(account)
                                                            "
                                                        >
                                                            <IconTrash class="size-4" />
                                                            {{ $t('accounts.disconnect') }}
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-between text-xs border-t border-muted/30 pt-2">
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-foreground/50">{{ $t('accounts.table.platform') }}:</span>
                                                <span class="font-medium text-foreground">{{ getPlatformLabel(account.platform) }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <Badge
                                                    v-if="!isDisconnected(account)"
                                                    variant="success"
                                                >
                                                    {{ $t('accounts.status.connected') }}
                                                </Badge>
                                                <Badge v-else variant="destructive">
                                                    <IconAlertCircle class="size-3" />
                                                    {{ $t('accounts.status.disconnected') }}
                                                </Badge>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-2 text-xs text-foreground/60 bg-muted/20 p-2 rounded-md">
                                            <div>
                                                <div class="text-[10px] uppercase tracking-wider text-foreground/45">{{ $t('accounts.table.last_used') }}</div>
                                                <div class="font-medium text-foreground">
                                                    <span v-if="account.last_used_at">{{
                                                        date.diffForHumans(account.last_used_at)
                                                    }}</span>
                                                    <span v-else>{{
                                                        $t('accounts.never_used')
                                                    }}</span>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-[10px] uppercase tracking-wider text-foreground/45">{{ $t('accounts.table.added') }}</div>
                                                <div class="font-medium text-foreground">
                                                    {{ date.diffForHumans(account.created_at) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </TableCell>

                                <TableCell class="hidden md:table-cell">
                                    {{ getPlatformLabel(account.platform) }}
                                </TableCell>

                                <TableCell class="hidden md:table-cell">
                                    <Badge
                                        v-if="!isDisconnected(account)"
                                        variant="success"
                                    >
                                        {{ $t('accounts.status.connected') }}
                                    </Badge>
                                    <Badge v-else variant="destructive">
                                        <IconAlertCircle class="size-3" />
                                        {{ $t('accounts.status.disconnected') }}
                                    </Badge>
                                </TableCell>

                                <TableCell class="hidden md:table-cell">
                                    <span v-if="account.last_used_at">{{
                                        date.diffForHumans(account.last_used_at)
                                    }}</span>
                                    <span v-else>{{
                                        $t('accounts.never_used')
                                    }}</span>
                                </TableCell>

                                <TableCell class="hidden md:table-cell">
                                    {{ date.diffForHumans(account.created_at) }}
                                </TableCell>

                                <TableCell class="hidden md:table-cell text-right">
                                    <Switch
                                        :model-value="account.is_active"
                                        :disabled="isDisconnected(account)"
                                        @update:model-value="
                                            handleToggle(account.id)
                                        "
                                    />
                                </TableCell>

                                <TableCell class="hidden md:table-cell text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                class="size-8"
                                            >
                                                <IconDots class="size-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem
                                                v-if="isDisconnected(account)"
                                                @click="
                                                    openOAuthPopup(
                                                        account.platform,
                                                    )
                                                "
                                            >
                                                <IconRefresh class="size-4" />
                                                {{
                                                    $t(
                                                        'accounts.reconnect_account',
                                                    )
                                                }}
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator
                                                v-if="isDisconnected(account)"
                                            />
                                            <DropdownMenuItem
                                                variant="destructive"
                                                @click="
                                                    handleDisconnect(account)
                                                "
                                            >
                                                <IconTrash class="size-4" />
                                                {{ $t('accounts.disconnect') }}
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <template #next="{ loading }">
                        <TableLoadMore v-if="loading" />
                    </template>
                </InfiniteScroll>
            </div>
        </div>
    </AppLayout>

    <AddSocialDialog v-model:open="isAddDialogOpen" :platforms="platforms" />

    <ConfirmDeleteModal
        ref="deleteModal"
        :title="$t('accounts.disconnect_modal.title')"
        :description="$t('accounts.disconnect_modal.description')"
        :action="$t('accounts.disconnect_modal.confirm')"
        :cancel="$t('accounts.disconnect_modal.cancel')"
    />
</template>
