<script setup lang="ts">
import { IconChevronLeft, IconChevronRight } from '@tabler/icons-vue';
import { computed, ref } from 'vue';

import dayjs from '@/dayjs';

interface Props {
    minDate?: string | null;
}

const props = withDefaults(defineProps<Props>(), {
    minDate: null,
});

const selectedDates = defineModel<string[]>('selectedDates', { default: () => [] });

const cursor = ref(dayjs().startOf('month'));

const minDay = computed(() => (props.minDate ? dayjs(props.minDate).startOf('day') : dayjs().startOf('day')));

const monthLabel = computed(() => cursor.value.format('MMMM YYYY'));

const weekdayNames = computed(() => {
    const names: string[] = [];
    const start = dayjs().startOf('week');
    for (let i = 0; i < 7; i++) {
        names.push(start.add(i, 'day').format('dd'));
    }
    return names;
});

interface CalendarCell {
    iso: string;
    label: number;
    inMonth: boolean;
    selectable: boolean;
}

const cells = computed<CalendarCell[]>(() => {
    const start = cursor.value.startOf('month').startOf('week');
    const end = cursor.value.endOf('month').endOf('week');
    const result: CalendarCell[] = [];
    let day = start;
    while (day.isBefore(end) || day.isSame(end, 'day')) {
        result.push({
            iso: day.format('YYYY-MM-DD'),
            label: day.date(),
            inMonth: day.isSame(cursor.value, 'month'),
            selectable: !day.isBefore(minDay.value, 'day'),
        });
        day = day.add(1, 'day');
    }
    return result;
});

const isSelected = (iso: string) => selectedDates.value.includes(iso);

const toggleDate = (cell: CalendarCell) => {
    if (!cell.selectable) return;
    if (isSelected(cell.iso)) {
        selectedDates.value = selectedDates.value.filter((d) => d !== cell.iso);
    } else {
        selectedDates.value = [...selectedDates.value, cell.iso].sort();
    }
};

const goPrev = () => {
    cursor.value = cursor.value.subtract(1, 'month');
};

const goNext = () => {
    cursor.value = cursor.value.add(1, 'month');
};
</script>

<template>
    <div class="rounded-2xl border-2 border-foreground bg-card p-4 shadow-2xs">
        <div class="mb-3 flex items-center justify-between">
            <button
                type="button"
                class="inline-flex size-8 cursor-pointer items-center justify-center rounded-md border-2 border-foreground bg-card shadow-2xs transition-transform hover:-translate-x-0.5"
                dusk="calendar-prev"
                @click="goPrev"
            >
                <IconChevronLeft class="size-4 text-foreground" stroke-width="2.5" />
            </button>
            <p class="text-sm font-bold capitalize text-foreground">{{ monthLabel }}</p>
            <button
                type="button"
                class="inline-flex size-8 cursor-pointer items-center justify-center rounded-md border-2 border-foreground bg-card shadow-2xs transition-transform hover:translate-x-0.5"
                dusk="calendar-next"
                @click="goNext"
            >
                <IconChevronRight class="size-4 text-foreground" stroke-width="2.5" />
            </button>
        </div>

        <div class="mb-2 grid grid-cols-7 gap-1 text-center">
            <p
                v-for="name in weekdayNames"
                :key="name"
                class="text-[10px] font-bold uppercase tracking-wider text-foreground/50"
            >
                {{ name }}
            </p>
        </div>

        <div class="grid grid-cols-7 gap-1">
            <button
                v-for="cell in cells"
                :key="cell.iso"
                type="button"
                :disabled="!cell.selectable"
                :class="[
                    'flex aspect-square cursor-pointer items-center justify-center rounded-md border-2 text-sm font-semibold transition-all',
                    !cell.inMonth ? 'text-foreground/30' : 'text-foreground',
                    isSelected(cell.iso)
                        ? 'border-foreground bg-violet-200 shadow-md'
                        : 'border-transparent hover:border-foreground hover:bg-foreground/5',
                    !cell.selectable ? 'cursor-not-allowed opacity-30 hover:border-transparent hover:bg-transparent' : '',
                ]"
                :dusk="`calendar-day-${cell.iso}`"
                @click="toggleDate(cell)"
            >
                {{ cell.label }}
            </button>
        </div>
    </div>
</template>
