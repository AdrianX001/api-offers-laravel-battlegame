<script setup>
import { computed, ref, onMounted } from "vue";

import StepApplicant from "./steps/StepApplicant.vue";
import StepPolicyholder from "./steps/StepPolicyholder.vue";
import StepVehicle from "./steps/StepVehicle.vue";
import StepMotor from "./steps/StepMotor.vue";
import StepReview from "./steps/StepReview.vue";
import ToastNotifications from "../components/ToastNotifications.vue";

import { useQuoteState } from "./composables/useQuoteState";
import { useNomenclature } from "./composables/useNomenclature";
import { useValidation } from "./composables/useValidation";
import { useOffers } from "./composables/useOffers";
import { buildPayload } from "./utils/payloadBuilder";

const props = defineProps({ token: { type: String, required: true } });
const isDev = import.meta.env.DEV;

const toast = ref(null);

/* ---------------- STEPS ---------------- */

const steps = [
    { key: "applicant", title: "Customer type", component: StepApplicant },
    { key: "policyholder", title: "Customer details", component: StepPolicyholder },
    { key: "vehicle", title: "Vehicle details", component: StepVehicle },
    { key: "motor", title: "Coverage", component: StepMotor },
    { key: "review", title: "Results", component: StepReview }
];

const idx = ref(0);
const current = computed(() => steps[idx.value]);
const isLast = computed(() => idx.value === steps.length - 1);
const percent = computed(() =>
    Math.round(((idx.value + 1) / steps.length) * 100)
);

/* ---------------- STATE ---------------- */

const {
    formData,
    clientType,
    addDriver,
    removeDriver
} = useQuoteState();

const {
    counties,
    localities,
    countries,
    vehicleTypes,
    loadAll,
    loadLocalities,
    lookupVehicle
} = useNomenclature(props.token);

const {
    errors,
    touched,
    markTouched,
    hasError,
    validateStep,
    validateAll
} = useValidation(formData, clientType);

const {
    offers,
    providerStats,
    providerErrors,
    loading,
    error,
    submit,
    downloadOfferPdf,
    createPolicy,
    downloadPolicyPdf
} = useOffers(props.token, toast, formData);

/* ---------------- NAVIGATION ---------------- */

function next() {
    const stepKey = current.value.key;

    validateStep(stepKey);

    const hasErrors = Object.keys(errors).some(
        k => k.startsWith(stepKey + ".") && errors[k] === true
    );

    if (hasErrors) {
        markTouched(stepKey);
        return;
    }

    if (idx.value < steps.length - 1) {
        idx.value++;
    }
}

function back() {
    if (idx.value > 0) idx.value--;
}

/* ---------------- SUBMIT ---------------- */

async function submitQuote() {
    validateAll();

    // Only block on errors that are actually true (not deleted keys)
    const hasErrors = Object.keys(errors).some(k => errors[k] === true);

    if (hasErrors) {
        markTouched("applicant");
        markTouched("policyholder");
        markTouched("vehicle");
        markTouched("motor");
        return;
    }

    const payload = buildPayload(formData, clientType.value);
    await submit(payload);
}

function resetQuote() {
    idx.value = 0;
    offers.value = [];
    providerErrors.value = {};
    providerStats.value = null;
    error.value = null;
}

onMounted(loadAll);

/* ---------------- TEST DATA ---------------- */

async function fillTestData() {
    clientType.value = "individual";

    // Policyholder
    const ph = formData.product.policyHolder;
    ph.firstName = "Ion";
    ph.lastName = "Popescu";
    ph.taxId = "1900101410011";
    ph.mobileNumber = "0744123456";
    ph.email = "test@test.ro";
    ph.identification.idNumber = "RX123456";
    ph.identification.idType = "CI";
    ph.drivingLicense.issueDate = "2010-01-01";
    ph.isForeignPerson = false;
    ph.address.country = "RO";
    ph.address.county = "B";
    ph.address.city = "BUCURESTI SECTORUL 1";
    ph.address.cityCode = 179141;
    ph.address.street = "Bulevardul Unirii";
    ph.address.houseNumber = "10";
    ph.address.floor = "1";
    ph.address.building = "A";
    ph.address.staircase = "1";
    ph.address.apartment = "12";
    ph.address.postcode = "010101";

    // Load localities for Bucuresti
    await loadLocalities("B");

    // Motor — always today + 2 days
    const startDate = new Date(Date.now() + 2 * 86400000).toISOString().split("T")[0];
    formData.product.motor.startDate = startDate;
    formData.product.motor.termTime = 12;
    formData.product.motor.installmentCount = 1;
    formData.product.motor.commissionPercentLimit = 10;

    // Vehicle
    const v = formData.product.vehicle;
    v.licensePlate = "B101XXX";
    v.vin = "WVWZZZ1JZXW000001";
    v.brand = "TRIUMPH";
    v.model = "DP04";
    v.yearOfConstruction = 2023;
    v.vehicleType = "L3e";
    v.usageType = "personal";
    v.totalWeight = 426;
    v.seats = 2;
    v.fuelType = "petrol";
    v.engineDisplacement = 900;
    v.enginePower = 48;
    v.identification.idNumber = "B101XXX";
    v.firstRegistration = "2023-12-07";
    v.currentMileage = 12000;

    // PTI
    formData.product.additionalData.product.vehicle.expirationDatePti = "2026-12-31";

    // Driver (same as policyholder)
    v.driver[0].firstName = "Ion";
    v.driver[0].lastName = "Popescu";
    v.driver[0].taxId = "1900101410011";
    v.driver[0].identification.idNumber = "RX123456";
}

function onPolicyHolderCountyChange(code) {
    formData.product.policyHolder.address.county = code;
    loadLocalities(code);
}

function onPolicyHolderLocalityChange(locality) {
    if (!locality) return;

    formData.product.policyHolder.address.city = locality.name;
    formData.product.policyHolder.address.cityCode =
        locality.siruta || locality.id;
}

/* ---------------- CTX FOR STEPS ---------------- */

// Plain object — refs stay as refs, no unwrapping
const ctx = {
    formData,
    clientType,
    counties,
    localities,
    countries,
    vehicleTypes,

    onPolicyHolderCountyChange,
    onPolicyHolderLocalityChange,

    addDriver,
    removeDriver,
    errors,
    touched,
    markTouched,
    hasError,
    validateStep,
    offers,
    providerStats,
    providerErrors,
    loading,
    error,
    downloadOfferPdf,
    createPolicy,
    downloadPolicyPdf,
    resetQuote,
};
</script>

<template>
    <div class="wizShell">
        <ToastNotifications ref="toast" />
        <div class="wizCard">

            <!-- TOP -->
            <div class="wizTop">
                <div class="titleBlock">
                    <div class="h1">{{ current.title }}</div>
                    <div class="sub">Required fields are marked with *</div>
                </div>

                <div class="progressWrap">
                    <div class="progressMeta">
                        <span>Step {{ idx + 1 }} / {{ steps.length }}</span>
                        <span>{{ percent }}%</span>
                    </div>
                    <div class="progressBar">
                        <div class="progressFill"
                             :style="{ width: percent + '%' }"></div>
                    </div>
                    <button v-if="isDev" class="btn-test" @click="fillTestData">🧪 Fill test data</button>
                </div>
            </div>

            <!-- BODY -->
            <div class="wizBody">
                <component :is="current.component" v-bind="ctx" />
            </div>

            <!-- FOOTER -->
            <div class="wizFooter">
                <button class="btn" v-if="idx > 0" @click="back">Back</button>
                <div v-else></div>

                <div class="dots">
                    <span v-for="(s, i) in steps"
                          :key="s.key"
                          class="dot"
                          :class="{ active: i === idx }">
                    </span>
                </div>

                <div style="display:flex; gap:8px;">
                    <button v-if="isLast && (offers?.length || error)"
                            class="btn"
                            @click="resetQuote">
                        ↺ New Quote
                    </button>

                    <button v-if="!isLast"
                            class="btn btn-brand"
                            @click="next">
                        Next
                    </button>

                    <button v-else
                            class="btn btn-danger"
                            :disabled="loading"
                            @click="submitQuote">
                        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                        GET OFFERS
                    </button>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
.wizShell { width: 100%; }

.wizCard {
    background: rgba(15,18,28,.72);
    border: 1px solid rgba(255,255,255,.10);
    border-radius: 16px;
    box-shadow: 0 8px 40px rgba(0,0,0,.55), inset 0 1px 0 rgba(255,255,255,.06);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    overflow: hidden;
}

.wizTop {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 20px;
    border-bottom: 1px solid rgba(255,255,255,.07);
    gap: 16px;
    background: rgba(255,255,255,.03);
}

.h1 { font-size: 15px; font-weight: 600; line-height: 1.3; }
.sub { font-size: 12px; color: var(--muted); margin-top: 2px; }

.progressWrap { flex-shrink: 0; width: min(260px, 100%); }

.progressMeta {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: var(--muted);
    margin-bottom: 6px;
}

.progressBar {
    height: 3px;
    background: rgba(255,255,255,.08);
    border-radius: 999px;
    overflow: hidden;
}
.progressFill {
    height: 100%;
    background: var(--accent);
    border-radius: 999px;
    transition: width .35s ease;
    box-shadow: 0 0 8px rgba(34,197,94,.45);
}

.wizBody { padding: 20px; min-height: 200px; }

.wizFooter {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 20px;
    border-top: 1px solid rgba(255,255,255,.07);
    background: rgba(255,255,255,.02);
    gap: 12px;
}

.dots { display: flex; gap: 5px; align-items: center; }
.dot {
    width: 6px; height: 6px;
    border-radius: 999px;
    background: rgba(255,255,255,.18);
    transition: all .2s ease;
}
.dot.active {
    width: 18px;
    background: var(--accent);
    box-shadow: 0 0 6px rgba(34,197,94,.5);
}

.btn-test {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 12px;
    border-radius: 999px;
    border: 1px dashed rgba(255,255,255,.15);
    background: transparent;
    color: var(--muted);
    font-size: 11px;
    cursor: pointer;
    transition: all .12s;
}
.btn-test:hover { border-color: var(--accent); color: var(--accent); }
</style>
