<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';

interface Tab {
    label: string;
    href: string;
    name: string;
}

defineProps<{
    tabs: Tab[];
    active: string;
}>();
</script>

<template>
    <div class="w-full overflow-x-auto pb-1">
        <Tabs :model-value="active">
            <TabsList>
                <TabsTrigger
                    v-for="tab in tabs"
                    :key="tab.name"
                    :value="tab.name"
                    as-child
                >
                    <Link :href="tab.href">{{ tab.label }}</Link>
                </TabsTrigger>
            </TabsList>
        </Tabs>
    </div>
</template>
