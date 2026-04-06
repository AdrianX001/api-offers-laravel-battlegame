<script setup>
const props = defineProps({
    clientType: Object,
    markTouched: Function,
});
</script>

<template>
    <div class="wrap">
        <div class="label">Choose customer type *</div>

        <div class="typeRow">
            <button
                class="typeBtn"
                :class="{ active: clientType.value === 'individual' }"
                @click="clientType.value = 'individual'; markTouched('applicant.clientType')"
            >
                <span class="typeIcon">👤</span>
                <span class="typeName">Individual</span>
                <span class="typeCode">PF</span>
            </button>

            <button
                class="typeBtn"
                :class="{ active: clientType.value === 'company' }"
                @click="clientType.value = 'company'; markTouched('applicant.clientType')"
            >
                <span class="typeIcon">🏢</span>
                <span class="typeName">Company</span>
                <span class="typeCode">PJ</span>
            </button>
        </div>

        <p class="hint">Required fields will be highlighted as you fill in the form.</p>
    </div>
</template>

<style scoped>
.wrap {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.label {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--muted);
}

.typeRow {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.typeBtn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 24px;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,.10);
    background: rgba(255,255,255,.06);
    color: var(--fg);
    font-family: 'Inter', sans-serif;
    cursor: pointer;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    transition: all .15s;
    user-select: none;
    min-width: 160px;
}
.typeBtn:hover {
    background: rgba(255,255,255,.10);
    border-color: rgba(255,255,255,.18);
    transform: translateY(-1px);
}
.typeBtn.active {
    border-color: rgba(34,197,94,.60);
    background: rgba(34,197,94,.14);
    box-shadow: inset 0 1px 0 rgba(34,197,94,.25), 0 4px 16px rgba(34,197,94,.15);
}

.typeIcon { font-size: 18px; line-height: 1; }

.typeName {
    font-size: 15px;
    font-weight: 600;
    flex: 1;
}

.typeCode {
    font-size: 11px;
    font-weight: 600;
    color: var(--muted);
    padding: 2px 7px;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,.12);
    background: rgba(255,255,255,.06);
}
.typeBtn.active .typeCode {
    border-color: rgba(34,197,94,.35);
    background: rgba(34,197,94,.15);
    color: var(--accent);
}

.hint {
    font-size: 12px;
    color: var(--muted);
    margin: 0;
}
</style>
