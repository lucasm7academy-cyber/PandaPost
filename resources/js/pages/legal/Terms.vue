<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { computed } from 'vue';

import LegalLayout from './LegalLayout.vue';

defineProps<{
    lastUpdated: string;
    contactEmail: string;
}>();

interface Section {
    heading: string;
    body: string;
}

const sections = computed<Section[]>(() => {
    const raw = trans('legal.terms.sections');
    return Array.isArray(raw) ? (raw as Section[]) : [];
});
</script>

<template>
    <LegalLayout
        :page-title="$t('legal.terms.page_title')"
        :title="$t('legal.terms.title')"
        :intro="$t('legal.terms.intro')"
        :last-updated="lastUpdated"
        :contact-email="contactEmail"
        :sections="sections"
    />
</template>
