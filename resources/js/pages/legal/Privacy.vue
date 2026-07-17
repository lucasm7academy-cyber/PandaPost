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
        :page-title="$t('legal.privacy.page_title')"
        :title="$t('legal.privacy.title')"
        :intro="$t('legal.privacy.intro')"
        :last-updated="lastUpdated"
        :contact-email="contactEmail"
        :sections="sections"
    />
</template>
