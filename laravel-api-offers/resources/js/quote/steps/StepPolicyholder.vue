<script setup>
import { computed } from "vue";
import { VueDatePicker as Datepicker } from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

const props = defineProps({
    clientType: Object,
    formData: Object,
    counties: Object,
    localities: Object,
    countries: Object,
    selectedPolicyHolderLocality: Object,
    onPolicyHolderCountyChange: Function,
    onPolicyHolderLocalityChange: Function,

    errors: Object,
    touched: Object,
    markTouched: Function,
    hasError: Function,
    validateStep: Function,
});

// Safe unwrap — these are refs passed as props
const countryList  = computed(() => props.countries?.value  ?? props.countries  ?? []);
const countyList   = computed(() => props.counties?.value   ?? props.counties   ?? []);
const localityList = computed(() => props.localities?.value ?? props.localities ?? []);

function touch(fieldName) {
    props.markTouched(`policyholder.${fieldName}`);
    props.validateStep?.("policyholder");
}

function digitsOnly(val, maxLen = null) {
    let s = String(val ?? "").replace(/\D/g, "");
    if (maxLen) s = s.slice(0, maxLen);
    return s;
}

// Block non-digit key presses on numeric-only inputs
function onlyDigitsKeydown(e) {
    const allowed = ["Backspace", "Delete", "ArrowLeft", "ArrowRight", "Tab", "Home", "End"];
    if (allowed.includes(e.key)) return;
    if (!/^\d$/.test(e.key)) e.preventDefault();
}

function onLocalityIdChange(id) {
    const loc =
        props.localities?.value?.find((l) => String(l.siruta || l.id) === String(id)) || null;

    props.onPolicyHolderLocalityChange?.(loc);
    touch("city");
}
</script>

<template>
    <div class="step-container">
        <h5 class="sectionTitle">Customer details</h5>

        <div v-if="clientType.value === 'company'" class="grid2">
            <label class="field">
                <span>Company name *</span>
                <input
                    class="custom-input"
                    :class="{ 'is-error': hasError('policyholder.businessName') }"
                    v-model="formData.product.policyHolder.businessName"
                    @input="touch('businessName')"
                />
            </label>

            <label class="field">
                <span>Tax ID (CUI) *</span>
                <input
                    class="custom-input"
                    :class="{ 'is-error': hasError('policyholder.taxId') }"
                    v-model="formData.product.policyHolder.taxId"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    @keydown="onlyDigitsKeydown"
                    @input="
                        formData.product.policyHolder.taxId = digitsOnly(formData.product.policyHolder.taxId);
                        touch('taxId');
                    "
                />
            </label>

            <label class="field">
                <span>Registry no. (J) *</span>
                <input
                    class="custom-input"
                    :class="{ 'is-error': hasError('policyholder.companyRegistryNumber') }"
                    v-model="formData.product.policyHolder.companyRegistryNumber"
                    @input="touch('companyRegistryNumber')"
                />
            </label>

            <label class="field">
                <span>CAEN *</span>
                <input
                    type="number"
                    class="custom-input"
                    :class="{ 'is-error': hasError('policyholder.caenCode') }"
                    v-model.number="formData.product.policyHolder.caenCode"
                    @input="touch('caenCode')"
                />
            </label>
        </div>

        <div v-else class="grid2">
            <label class="field">
                <span>First name *</span>
                <input
                    class="custom-input"
                    :class="{ 'is-error': hasError('policyholder.firstName') }"
                    v-model="formData.product.policyHolder.firstName"
                    @input="touch('firstName')"
                />
            </label>

            <label class="field">
                <span>Last name *</span>
                <input
                    class="custom-input"
                    :class="{ 'is-error': hasError('policyholder.lastName') }"
                    v-model="formData.product.policyHolder.lastName"
                    @input="touch('lastName')"
                />
            </label>

            <label class="field">
                <span>Tax ID (CNP) *</span>
                <input
                    class="custom-input"
                    :class="{ 'is-error': hasError('policyholder.taxId') }"
                    v-model="formData.product.policyHolder.taxId"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="13"
                    @keydown="onlyDigitsKeydown"
                    @input="
                        formData.product.policyHolder.taxId = digitsOnly(formData.product.policyHolder.taxId, 13);
                        touch('taxId');
                    "
                />
            </label>

            <label class="field">
                <span>ID number *</span>
                <input
                    class="custom-input"
                    :class="{ 'is-error': hasError('policyholder.idNumber') }"
                    v-model="formData.product.policyHolder.identification.idNumber"
                    @input="touch('idNumber')"
                />
            </label>

            <label class="field">
                <span>Driving license issue date *</span>
                <div class="dp-wrapper" :class="{ 'dp-error': hasError('policyholder.drivingLicenseIssueDate') }">
                    <Datepicker
                        :model-value="formData.product.policyHolder.drivingLicense.issueDate"
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
                        @update:model-value="val => { formData.product.policyHolder.drivingLicense.issueDate = val; touch('drivingLicenseIssueDate'); }"
                    />
                </div>
            </label>

            <div class="foreign-alignment-container">
                <label class="foreign-check-label">
                    <input
                        type="checkbox"
                        v-model="formData.product.policyHolder.isForeignPerson"
                        @change="touch('isForeignPerson')"
                    />
                    <span class="foreign-text">Foreign person</span>
                </label>
            </div>

            <template v-if="formData.product.policyHolder.isForeignPerson">
                <label class="field">
                    <span>Nationality *</span>
                    <select
                        class="custom-input"
                        :class="{ 'is-error': hasError('policyholder.nationality') }"
                        v-model="formData.product.policyHolder.nationality"
                        @change="touch('nationality')"
                    >
                        <option value="">Select nationality</option>
                        <option v-for="c in countryList" :key="c.code" :value="c.code">
                            {{ c.name }} ({{ c.code }})
                        </option>
                    </select>
                </label>

                <label class="field">
                    <span>Citizenship *</span>
                    <select
                        class="custom-input"
                        :class="{ 'is-error': hasError('policyholder.citizenship') }"
                        v-model="formData.product.policyHolder.citizenship"
                        @change="touch('citizenship')"
                    >
                        <option value="">Select citizenship</option>
                        <option v-for="c in countryList" :key="c.code" :value="c.code">
                            {{ c.name }} ({{ c.code }})
                        </option>
                    </select>
                </label>

                <label class="field">
                    <span>ID type *</span>
                    <select
                        class="custom-input"
                        :class="{ 'is-error': hasError('policyholder.idType') }"
                        v-model="formData.product.policyHolder.identification.idType"
                        @change="touch('idType')"
                    >
                        <option value="CI">CI</option>
                        <option value="PASSPORT">Passport</option>
                        <option value="ID">National ID</option>
                    </select>
                </label>

                <label class="field">
                    <span>Issue authority *</span>
                    <input
                        class="custom-input"
                        :class="{ 'is-error': hasError('policyholder.issueAuthority') }"
                        v-model="formData.product.policyHolder.identification.issueAuthority"
                        @input="touch('issueAuthority')"
                    />
                </label>

                <label class="field">
                    <span>Issue date *</span>
                    <div class="dp-wrapper" :class="{ 'dp-error': hasError('policyholder.issueDate') }">
                        <Datepicker
                            :model-value="formData.product.policyHolder.identification.issueDate"
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
                            @update:model-value="val => { formData.product.policyHolder.identification.issueDate = val; touch('issueDate'); }"
                        />
                    </div>
                </label>
            </template>
        </div>

        <h6 class="subTitle">Contact & address</h6>

        <div class="grid2">
            <label class="field">
                <span>Mobile number *</span>
                <input
                    class="custom-input"
                    :class="{ 'is-error': hasError('policyholder.mobileNumber') }"
                    v-model="formData.product.policyHolder.mobileNumber"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    @keydown="onlyDigitsKeydown"
                    @input="
                        formData.product.policyHolder.mobileNumber = digitsOnly(formData.product.policyHolder.mobileNumber);
                        touch('mobileNumber');
                    "
                />
            </label>

            <label class="field">
                <span>Email *</span>
                <input
                    type="email"
                    class="custom-input"
                    :class="{ 'is-error': hasError('policyholder.email') }"
                    v-model="formData.product.policyHolder.email"
                    @input="touch('email')"
                />
            </label>

            <label class="field">
                <span>County *</span>
                <select
                    class="custom-input"
                    :class="{ 'is-error': hasError('policyholder.county') }"
                    :value="formData.product.policyHolder.address.county"
                    @change="
                        onPolicyHolderCountyChange?.($event.target.value);
                        touch('county');
                        touch('city');
                        touch('cityCode');
                    "
                >
                    <option value="">Select county</option>
                    <option v-for="c in countyList" :key="c.code" :value="c.code">
                        {{ c.name }} ({{ c.code }})
                    </option>
                </select>
            </label>

            <label class="field">
                <span>City *</span>
                <select
                    class="custom-input"
                    :class="{ 'is-error': hasError('policyholder.city') }"
                    :value="formData.product.policyHolder.address.cityCode || ''"
                    @change="onLocalityIdChange($event.target.value)"
                >
                    <option value="">Select city</option>
                    <option v-for="l in localityList" :key="l.siruta || l.id" :value="String(l.siruta || l.id)">
                        {{ l.name }}
                    </option>
                </select>
            </label>

            <label class="field">
                <span>Postcode *</span>
                <input
                    class="custom-input"
                    :class="{ 'is-error': hasError('policyholder.postcode') }"
                    v-model="formData.product.policyHolder.address.postcode"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="6"
                    @keydown="onlyDigitsKeydown"
                    @input="
                        formData.product.policyHolder.address.postcode = digitsOnly(formData.product.policyHolder.address.postcode, 6);
                        touch('postcode');
                    "
                />
            </label>

            <label class="field">
                <span>Street *</span>
                <input
                    class="custom-input"
                    :class="{ 'is-error': hasError('policyholder.street') }"
                    v-model="formData.product.policyHolder.address.street"
                    @input="touch('street')"
                />
            </label>

            <label class="field">
                <span>House number *</span>
                <input
                    class="custom-input"
                    :class="{ 'is-error': hasError('policyholder.houseNumber') }"
                    v-model="formData.product.policyHolder.address.houseNumber"
                    @input="touch('houseNumber')"
                />
            </label>

            <label class="field">
                <span>Floor</span>
                <input
                    class="custom-input"
                    v-model="formData.product.policyHolder.address.floor"
                    @input="touch('floor')"
                />
            </label>

            <label class="field">
                <span>Building</span>
                <input
                    class="custom-input"
                    v-model="formData.product.policyHolder.address.building"
                    @input="touch('building')"
                />
            </label>

            <label class="field">
                <span>Staircase</span>
                <input
                    class="custom-input"
                    v-model="formData.product.policyHolder.address.staircase"
                    @input="touch('staircase')"
                />
            </label>

            <label class="field">
                <span>Apartment</span>
                <input
                    class="custom-input"
                    v-model="formData.product.policyHolder.address.apartment"
                    @input="touch('apartment')"
                />
            </label>

            <!-- Siruta hidden — auto-filled when city is selected -->
            <input type="hidden" v-model.number="formData.product.policyHolder.address.cityCode" />
        </div>
    </div>
</template>

<style scoped>
.step-container { display: flex; flex-direction: column; gap: 0; }

.sectionTitle {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--muted);
    margin: 0 0 14px;
}

.subTitle {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--muted);
    margin: 20px 0 14px;
    padding-top: 16px;
    border-top: 1px solid rgba(255,255,255,.06);
}
.subTitle::before {
    content: '';
    width: 3px; height: 12px;
    border-radius: 999px;
    background: var(--accent);
    flex-shrink: 0;
}

.grid2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
@media (max-width: 700px) { .grid2 { grid-template-columns: 1fr; } }

.field {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.field span {
    font-size: 11px;
    font-weight: 500;
    color: var(--muted);
    letter-spacing: .04em;
    text-transform: uppercase;
}

/* Foreign person checkbox — same height as an input */
.foreign-alignment-container {
    display: flex;
    align-items: flex-end;
}
.foreign-check-label {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 14px;
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,.10);
    background: rgba(255,255,255,.06);
    backdrop-filter: blur(8px);
    cursor: pointer;
    user-select: none;
    width: 100%;
    transition: background .12s, border-color .12s;
}
.foreign-check-label:hover {
    background: rgba(255,255,255,.09);
    border-color: rgba(255,255,255,.16);
}
.foreign-text {
    font-size: 14px !important;
    font-weight: 500;
    color: var(--fg) !important;
    margin: 0 !important;
}
input[type="checkbox"] {
    width: 16px; height: 16px;
    margin: 0;
    accent-color: var(--accent);
    cursor: pointer;
    flex-shrink: 0;
}

:deep(.dp__input_icon) { display: none !important; }
:deep(.dp__input_wrap) { border-radius: 10px !important; }
.dp-wrapper :deep(.dp__input) {
    padding-left: 14px !important;
}
.dp-error :deep(.dp__input) {
    border-color: rgba(248,113,113,.60) !important;
    box-shadow: 0 0 0 3px rgba(248,113,113,.14) !important;
}
</style>
