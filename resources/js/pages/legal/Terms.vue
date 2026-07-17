<script setup lang="ts">
import LegalLayout from './LegalLayout.vue';

interface Section {
    heading: string;
    body: string;
}

defineProps<{
    lastUpdated: string;
    contactEmail: string;
    sections: Section[];
}>();
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
