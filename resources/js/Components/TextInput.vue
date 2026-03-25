<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-[10px] border-gray-300 shadow-sm focus:border-[#1866ed] focus:ring-[#1866ed]"
        v-model="model"
        ref="input"
    />
</template>
