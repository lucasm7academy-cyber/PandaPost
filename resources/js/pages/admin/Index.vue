<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface Owner {
    id: string;
    name: string;
    email: string;
}

interface Plan {
    id: string;
    name: string;
    slug: string;
}

interface Usage {
    workspaceCount: number;
    socialAccountCount: number;
    memberCount: number;
    pendingInviteCount: number;
    postCount: number;
    creditsUsed: number;
}

interface Account {
    id: string;
    name: string;
    owner: Owner | null;
    plan: Plan | null;
    trial_ends_at: string | null;
    is_on_trial: boolean;
    usage: Usage;
}

defineProps<{
    accounts: Account[];
    plans: Plan[];
}>();

const addTrialDays = (accountId: string, days: number) => {
    router.post(`/admin/trial-days/${accountId}`, { days }, {
        preserveScroll: true,
    });
};

const handlePlanChange = (accountId: string, planId: string) => {
    router.post(`/admin/change-plan/${accountId}`, { plan_id: planId }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Admin Panel" />

    <AppLayout>
        <div class="mx-auto max-w-7xl space-y-8 px-6 py-8">
            <PageHeader
                title="Admin Panel"
                description="Manage all accounts, subscription plans, trial extensions, and view usage statistics."
            />

            <div class="rounded-md border border-muted bg-card">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Account / Owner</TableHead>
                            <TableHead>Current Plan</TableHead>
                            <TableHead>Trial Settings</TableHead>
                            <TableHead>Workspaces / Members</TableHead>
                            <TableHead>Accounts / Posts</TableHead>
                            <TableHead>AI Credits</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="account in accounts" :key="account.id">
                            <TableCell>
                                <div class="font-semibold text-foreground">{{ account.name }}</div>
                                <div v-if="account.owner" class="text-xs text-muted-foreground">
                                    {{ account.owner.name }} ({{ account.owner.email }})
                                </div>
                                <div v-else class="text-xs text-muted-foreground">No Owner</div>
                            </TableCell>
                            <TableCell>
                                <div class="flex flex-col gap-2">
                                    <div class="flex items-center gap-2">
                                        <Badge variant="secondary" class="capitalize">
                                            {{ account.plan ? account.plan.name : 'Free' }}
                                        </Badge>
                                        <Badge v-if="account.is_on_trial" variant="outline" class="border-amber-500 text-amber-500 bg-amber-500/5">
                                            Trial
                                        </Badge>
                                    </div>
                                    <div class="w-48">
                                        <Select
                                            :model-value="account.plan?.id || ''"
                                            @update:model-value="(val) => handlePlanChange(account.id, val)"
                                        >
                                            <SelectTrigger class="h-9 w-full">
                                                <SelectValue placeholder="Change Plan" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="p in plans" :key="p.id" :value="p.id">
                                                    {{ p.name }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex flex-col gap-2">
                                    <div class="text-xs text-muted-foreground">
                                        <span v-if="account.trial_ends_at">
                                            Ends: {{ new Date(account.trial_ends_at).toLocaleDateString() }}
                                        </span>
                                        <span v-else>No trial set</span>
                                    </div>
                                    <div class="flex gap-1">
                                        <Button size="sm" variant="outline" @click="addTrialDays(account.id, 7)">
                                            +7d
                                        </Button>
                                        <Button size="sm" variant="outline" @click="addTrialDays(account.id, 14)">
                                            +14d
                                        </Button>
                                        <Button size="sm" variant="outline" @click="addTrialDays(account.id, 30)">
                                            +30d
                                        </Button>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="text-sm">
                                    Workspaces: <span class="font-medium text-foreground">{{ account.usage.workspaceCount }}</span>
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    Members: <span class="font-medium">{{ account.usage.memberCount }}</span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="text-sm">
                                    Social accounts: <span class="font-medium text-foreground">{{ account.usage.socialAccountCount }}</span>
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    Posts: <span class="font-medium">{{ account.usage.postCount }}</span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="text-sm">
                                    Credits used: <span class="font-medium text-foreground">{{ account.usage.creditsUsed }}</span>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
