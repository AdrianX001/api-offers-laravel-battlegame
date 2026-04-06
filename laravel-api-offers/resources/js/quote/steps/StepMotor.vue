<script setup>
import { VueDatePicker as Datepicker } from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

const props = defineProps({
    formData: Object,
    errors: Object,
    touched: Object,
    markTouched: Function,
    hasError: Function,
    validateStep: Function,
});

function touch(field) {
    props.markTouched(`motor.${field}`);
    props.validateStep?.("motor");
}
</script>

<template>
    <div class="step-container">
        <h5 class="sectionTitle">Coverage</h5>

        <div class="grid">
            <label class="field">
                <span>Start date *</span>
                <div class="dp-wrapper" :class="{ 'dp-error': hasError('motor.startDate') }">
                    <Datepicker
                        :model-value="formData.product.motor.startDate"
                        :enable-time-picker="false"
                        :teleport="true"
                        :auto-position="true"
                        position="bottom"
                        auto-apply
                        :close-on-auto-apply="true"
                        :clearable="false"
                        hide-input-icon
                        format="yyyy-MM-dd"
                        model-type="yyyy-MM-dd"
                        @update:model-value="val => { formData.product.motor.startDate = val; touch('startDate'); }"
                    />
                </div>
            </label>

            <label class="field">
                <span>Term (months) *</span>
                <input
                    type="number"
                    class="custom-input"
                    :class="{ 'is-error': hasError('motor.termTime') }"
                    v-model.number="formData.product.motor.termTime"
                    @input="touch('termTime')"
                    min="1"
                    max="12"
                />
            </label>

            <label class="field">
                <span>Installments *</span>
                <select
                    class="custom-input"
                    :class="{ 'is-error': hasError('motor.installmentCount') }"
                    v-model.number="formData.product.motor.installmentCount"
                    @change="touch('installmentCount')"
                >
                    <option :value="1">1 — full payment</option>
                    <option :value="2">2 — semi-annual</option>
                    <option :value="4">4 — quarterly</option>
                </select>
            </label>

            <label class="field">
                <span>Commission limit (%) *</span>
                <select
                    class="custom-input"
                    :class="{ 'is-error': hasError('motor.commissionPercentLimit') }"
                    v-model.number="formData.product.motor.commissionPercentLimit"
                    @change="touch('commissionPercentLimit')"
                >
                    <option :value="0">0%</option>
                    <option :value="5">5%</option>
                    <option :value="10">10%</option>
                    <option :value="15">15%</option>
                    <option :value="20">20%</option>
                    <option :value="25">25%</option>
                </select>
            </label>
        </div>
    </div>
</template>

<style scoped>
.sectionTitle {
    font-size: 11px; font-weight: 700; letter-spacing: .1em;
    text-transform: uppercase; color: var(--muted);
    font-family: 'JetBrains Mono', monospace; margin-bottom: 16px;
}
.grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 14px; }
@media (max-width: 1100px) { .grid { grid-template-columns: 1fr 1fr; } }
@media (max-width: 720px)  { .grid { grid-template-columns: 1fr; } }
.field { display: flex; flex-direction: column; gap: 5px; }
.field span { font-size: 11px; font-weight: 500; color: var(--muted); letter-spacing: .04em; text-transform: uppercase; }
:deep(.dp__clear_icon), :deep(.dp__action_clear),
:deep(.dp__btn.dp__action_clear), :deep(.dp__input_icon), :deep(.dp__icon) {
    display: none !important;
}
:deep(.dp__input_wrap) { border-radius: 10px !important; }
.dp-wrapper :deep(.dp__input) { padding-left: 14px !important; }
.dp-error :deep(.dp__input) {
    border: 1px solid var(--danger) !important;
    box-shadow: 0 0 0 3px rgba(248,113,113,.14) !important;
}
</style>
