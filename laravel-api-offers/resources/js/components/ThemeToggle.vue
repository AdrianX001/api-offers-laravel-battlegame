<script setup>
import { ref, onMounted } from "vue";

const isLight = ref(false);

onMounted(() => {
    isLight.value = document.documentElement.classList.contains("theme-light");
});

function toggle() {
    isLight.value = !isLight.value;
    document.documentElement.classList.toggle("theme-light", isLight.value);
}
</script>

<template>
    <button class="toggle" @click="toggle" :title="isLight ? 'Switch to dark mode' : 'Switch to light mode'">
        <span class="track">
            <span class="thumb" :class="{ on: isLight }"></span>
        </span>
        <span class="label">{{ isLight ? 'Light' : 'Dark' }}</span>
    </button>
</template>

<style scoped>
.toggle {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 5px 12px 5px 7px;
    border-radius: 999px;
    border: 1px solid var(--border2);
    background: var(--card2);
    color: var(--muted);
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all .12s;
    user-select: none;
}
.toggle:hover { color: var(--fg); border-color: var(--border3); }

.track {
    width: 26px; height: 14px;
    border-radius: 999px;
    background: var(--bg3);
    border: 1px solid var(--border2);
    position: relative;
    flex-shrink: 0;
    transition: background .2s;
}

.thumb {
    position: absolute;
    top: 2px; left: 2px;
    width: 8px; height: 8px;
    border-radius: 50%;
    background: var(--muted);
    transition: transform .18s ease, background .18s;
}
.thumb.on {
    transform: translateX(12px);
    background: var(--accent);
}
</style>
