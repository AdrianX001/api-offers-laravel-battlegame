<script setup>
import { ref } from "vue";

const toasts = ref([]);
let nextId = 0;

function show(message, type = "info", duration = 4000) {
    const id = ++nextId;
    toasts.value.push({ id, message, type });
    setTimeout(() => remove(id), duration);
}

function remove(id) {
    toasts.value = toasts.value.filter(t => t.id !== id);
}

// Expose show method so parent can call it
defineExpose({ show });
</script>

<template>
    <Teleport to="body">
        <div class="toastStack">
            <TransitionGroup name="toast">
                <div
                    v-for="t in toasts"
                    :key="t.id"
                    class="toast"
                    :class="`toast--${t.type}`"
                    @click="remove(t.id)"
                >
                    <span class="toastIcon">
                        <template v-if="t.type === 'success'">✓</template>
                        <template v-else-if="t.type === 'error'">✕</template>
                        <template v-else-if="t.type === 'warning'">⚠</template>
                        <template v-else>ℹ</template>
                    </span>
                    <span class="toastMsg">{{ t.message }}</span>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<style scoped>
.toastStack {
    position: fixed;
    bottom: 24px;
    right: 20px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 8px;
    pointer-events: none;
}

.toast {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    border-radius: 14px;
    border: 1px solid var(--border);
    background: color-mix(in srgb, var(--card) 90%, transparent);
    backdrop-filter: blur(12px);
    box-shadow: 0 8px 30px rgba(0,0,0,.18);
    font-size: 14px;
    max-width: 360px;
    pointer-events: all;
    cursor: pointer;
    transition: opacity .2s;
}
.toast:hover { opacity: .85; }

.toast--success { border-color: color-mix(in srgb, var(--accent) 40%, var(--border)); }
.toast--error   { border-color: color-mix(in srgb, var(--danger) 40%, var(--border)); }
.toast--warning { border-color: color-mix(in srgb, #f59e0b 40%, var(--border)); }

.toastIcon {
    font-size: 16px;
    font-weight: 900;
    flex-shrink: 0;
}
.toast--success .toastIcon { color: var(--accent); }
.toast--error   .toastIcon { color: var(--danger); }
.toast--warning .toastIcon { color: #f59e0b; }

.toastMsg { line-height: 1.4; }

/* transition */
.toast-enter-active { transition: all .25s ease; }
.toast-leave-active { transition: all .2s ease; }
.toast-enter-from   { opacity: 0; transform: translateY(12px); }
.toast-leave-to     { opacity: 0; transform: translateX(20px); }
</style>
