<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import QuoteWizard from "../quote/QuoteWizard.vue";

const token     = ref(null);
const expiresAt = ref(null);
const loading   = ref(true);
const error     = ref(null);

let refreshTimer = null;

async function autoAuthenticate() {
    loading.value = true;
    error.value   = null;

    try {
        const response = await fetch("/api/auto-auth", {
            headers: { "X-Requested-With": "XMLHttpRequest" }
        });

        const data = await response.json();

        if (!response.ok) {
            error.value = data.error || "Auto-authentication failed. Please check API configuration.";
            return;
        }

        token.value     = data.token;
        expiresAt.value = data.expires_at;

        scheduleRefresh(data.expires_at);

    } catch (err) {
        error.value = err.message || "Network error. Please try again.";
    } finally {
        loading.value = false;
    }
}

async function doRefresh() {
    try {
        const response = await fetch("/api/auto-auth", {
            headers: { "X-Requested-With": "XMLHttpRequest" }
        });
        const data = await response.json();
        if (response.ok) {
            token.value     = data.token;
            expiresAt.value = data.expires_at;
            scheduleRefresh(data.expires_at);
        }
    } catch {
        // silent — next API call will surface the error naturally
    }
}

function scheduleRefresh(expiresAtStr) {
    if (refreshTimer) clearTimeout(refreshTimer);
    if (!expiresAtStr) return;

    const refreshIn = new Date(expiresAtStr).getTime() - Date.now() - 60_000; // 1 min before expiry
    if (refreshIn > 0) refreshTimer = setTimeout(doRefresh, refreshIn);
}

function resetAuth() {
    token.value     = null;
    expiresAt.value = null;
    error.value     = null;
    if (refreshTimer) clearTimeout(refreshTimer);
    autoAuthenticate();
}

onMounted(autoAuthenticate);
onUnmounted(() => { if (refreshTimer) clearTimeout(refreshTimer); });
</script>


<template>
    <div>
        <div v-if="loading" class="authState">
            <div class="authCard">
                <div class="spinner-border spinner-border-lg" style="color: var(--accent);"></div>
                <div class="authTitle">Connecting</div>
                <div class="authSub">Authenticating with RCA API…</div>
            </div>
        </div>

        <div v-else-if="error" class="authState">
            <div class="authCard error">
                <div class="authErrIcon">!</div>
                <div class="authTitle">Connection failed</div>
                <div class="authSub">{{ error }}</div>
                <button class="btn btn-brand" style="margin-top: 16px;" @click="resetAuth">↺ Retry</button>
            </div>
        </div>

        <div v-else>
            <QuoteWizard :token="token" />
        </div>
    </div>
</template>

<style scoped>
.authState {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 55vh;
}
.authCard {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 36px 44px;
    border-radius: 16px;
    border: 1px solid var(--border2);
    background: var(--card);
    box-shadow: var(--shadow-lg);
    text-align: center;
    min-width: 260px;
}
.authCard.error {
    border-color: color-mix(in srgb, var(--danger) 30%, var(--border));
}
.authErrIcon {
    width: 40px; height: 40px;
    border-radius: 50%;
    border: 2px solid var(--danger);
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; font-weight: 700;
    color: var(--danger);
    margin-bottom: 4px;
}
.authTitle { font-size: 16px; font-weight: 600; margin-top: 8px; }
.authSub { font-size: 12px; color: var(--muted); max-width: 240px; line-height: 1.6; }
</style>
