<script setup>
import { VueDatePicker as Datepicker } from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

const props = defineProps({
    clientType: Object,
    formData: Object,
    vehicleTypes: Object,
    addDriver: Function,
    removeDriver: Function,
    errors: Object,
    touched: Object,
    markTouched: Function,
    hasError: Function,
    validateStep: Function,
});

function touch(field = null) {
    if (field) {
        props.markTouched(`vehicle.${field}`);
    } else {
        props.markTouched("vehicle");
    }
    props.validateStep?.("vehicle");
}

function copyPolicyholderToDriver(i) {
    const ph = props.formData.product.policyHolder;
    const d = props.formData.product.vehicle.driver[i];
    if (!ph || !d) return;

    d.firstName = ph.firstName || d.firstName;
    d.lastName = ph.lastName || d.lastName;
    d.taxId = ph.taxId || d.taxId;
    d.identification = d.identification || { idNumber: "" };
    d.identification.idNumber = ph.identification?.idNumber || d.identification.idNumber;

    touch();
}
</script>

<template>
    <div class="step-container">
        <h5 class="sectionTitle">Vehicle details</h5>

        <div class="grid3">
            <label class="field">
                <span>License Plate *</span>
                <input
                    class="custom-input uppercase-input"
                    :class="{ 'is-error': hasError('vehicle.licensePlate') }"
                    v-model="formData.product.vehicle.licensePlate"
                    @input="touch('licensePlate')"
                />
            </label>

            <label class="field">
                <span>VIN *</span>
                <input
                    class="custom-input uppercase-input"
                    :class="{ 'is-error': hasError('vehicle.vin') }"
                    v-model="formData.product.vehicle.vin"
                    @input="touch('vin')"
                />
            </label>

            <label class="field">
                <span>Brand *</span>
                <input
                    class="custom-input"
                    :class="{ 'is-error': hasError('vehicle.brand') }"
                    v-model="formData.product.vehicle.brand"
                    @input="touch('brand')"
                />
            </label>

            <label class="field">
                <span>Model *</span>
                <input
                    class="custom-input"
                    :class="{ 'is-error': hasError('vehicle.model') }"
                    v-model="formData.product.vehicle.model"
                    @input="touch('model')"
                />
            </label>

            <label class="field">
                <span>Year *</span>
                <input
                    type="number"
                    class="custom-input"
                    :class="{ 'is-error': hasError('vehicle.year') }"
                    v-model.number="formData.product.vehicle.yearOfConstruction"
                    @input="touch('year')"
                    min="1900"
                    max="2100"
                />
            </label>

            <label class="field">
                <span>Vehicle Type *</span>
                <select
                    class="custom-input"
                    :class="{ 'is-error': hasError('vehicle.type') }"
                    v-model="formData.product.vehicle.vehicleType"
                    @change="touch('type')"
                >
                    <option value="">Select type</option>
                    <option
                        v-for="vt in vehicleTypes.value"
                        :key="vt.code || vt.id || vt"
                        :value="vt.code || vt.id || vt"
                    >
                        {{ vt.name || vt.label || vt }}
                    </option>
                </select>
            </label>

            <label class="field">
                <span>Usage Type *</span>
                <select
                    class="custom-input"
                    :class="{ 'is-error': hasError('vehicle.usage') }"
                    v-model="formData.product.vehicle.usageType"
                    @change="touch('usage')"
                >
                    <option value="">Select usage</option>
                    <option value="personal">Personal</option>
                    <option value="passengerTransportation">Passenger transportation</option>
                    <option value="taxi">Taxi</option>
                    <option value="carRental">Car rental</option>
                    <option value="cargoTransportation">Cargo</option>
                    <option value="distribution">Distribution</option>
                    <option value="courier">Courier</option>
                    <option value="drivingSchool">Driving school</option>
                </select>
            </label>

            <label class="field">
                <span>Total Weight (kg) *</span>
                <input
                    type="number"
                    class="custom-input"
                    :class="{ 'is-error': hasError('vehicle.weight') }"
                    v-model.number="formData.product.vehicle.totalWeight"
                    @input="touch('weight')"
                    min="1"
                />
            </label>

            <label class="field">
                <span>Seats *</span>
                <input
                    type="number"
                    class="custom-input"
                    :class="{ 'is-error': hasError('vehicle.seats') }"
                    v-model.number="formData.product.vehicle.seats"
                    @input="touch('seats')"
                    min="1"
                />
            </label>

            <label class="field">
                <span>Fuel Type *</span>
                <select
                    class="custom-input"
                    :class="{ 'is-error': hasError('vehicle.fuel') }"
                    v-model="formData.product.vehicle.fuelType"
                    @change="touch('fuel')"
                >
                    <option value="">Select fuel</option>
                    <option value="diesel">Diesel</option>
                    <option value="petrol">Petrol</option>
                    <option value="hybrid">Hybrid</option>
                    <option value="electric">Electric</option>
                    <option value="lpg">LPG</option>
                </select>
            </label>

            <label class="field">
                <span>Engine Displacement (cc) *</span>
                <input
                    type="number"
                    class="custom-input"
                    :class="{ 'is-error': hasError('vehicle.cc') }"
                    v-model.number="formData.product.vehicle.engineDisplacement"
                    @input="touch('cc')"
                    min="1"
                />
            </label>

            <label class="field">
                <span>Engine Power (kW) *</span>
                <input
                    type="number"
                    class="custom-input"
                    :class="{ 'is-error': hasError('vehicle.kw') }"
                    v-model.number="formData.product.vehicle.enginePower"
                    @input="touch('kw')"
                    min="1"
                />
            </label>

            <label class="field">
                <span>CIV Number *</span>
                <input
                    class="custom-input"
                    :class="{ 'is-error': hasError('vehicle.civ') }"
                    v-model="formData.product.vehicle.identification.idNumber"
                    @input="touch('civ')"
                />
            </label>

            <label class="field">
                <span>First Registration Date *</span>
                <div class="dp-wrapper" :class="{ 'dp-error': hasError('vehicle.firstReg') }">
                    <Datepicker
                        :model-value="formData.product.vehicle.firstRegistration"
                        :enable-time-picker="false"
                        :teleport="true"
                        auto-apply
                        :close-on-auto-apply="true"
                        :clearable="false"
                        hide-input-icon
                        format="yyyy-MM-dd"
                        model-type="yyyy-MM-dd"
                        @update:model-value="val => { formData.product.vehicle.firstRegistration = val; touch('firstReg'); }"
                    />
                </div>
            </label>

            <label class="field">
                <span>Current Mileage (km) *</span>
                <input
                    type="number"
                    class="custom-input"
                    :class="{ 'is-error': hasError('vehicle.mileage') }"
                    v-model.number="formData.product.vehicle.currentMileage"
                    @input="touch('mileage')"
                    min="0"
                />
            </label>

            <label class="field">
                <span>PTI Expiration Date *</span>
                <div class="dp-wrapper" :class="{ 'dp-error': hasError('vehicle.pti') }">
                    <Datepicker
                        :model-value="formData.product.additionalData.product.vehicle.expirationDatePti"
                        :enable-time-picker="false"
                        :teleport="true"
                        auto-apply
                        :close-on-auto-apply="true"
                        :clearable="false"
                        hide-input-icon
                        format="yyyy-MM-dd"
                        model-type="yyyy-MM-dd"
                        @update:model-value="val => { formData.product.additionalData.product.vehicle.expirationDatePti = val; touch('pti'); }"
                    />
                </div>
            </label>
        </div>

        <h5 class="sectionTitle" style="margin-top:18px;">Drivers *</h5>

        <div v-if="hasError('vehicle.drivers')" class="driverWarn">
            Please complete all required fields for at least one driver.
        </div>

        <div v-for="(d, i) in formData.product.vehicle.driver" :key="i" class="driverCard">
            <div class="driverTop">
                <b>Driver {{ i + 1 }}</b>
                <div class="driverTopActions">
                    <button
                        class="btn btn-copy"
                        type="button"
                        @click="copyPolicyholderToDriver(i)"
                        title="Copy details from customer"
                    >
                        ↙ Copy from customer
                    </button>
                    <button
                        v-if="formData.product.vehicle.driver.length > 1"
                        class="btn btn-danger"
                        type="button"
                        @click="removeDriver(i); touch()"
                    >
                        Remove
                    </button>
                </div>
            </div>

            <div class="grid2">
                <label class="field">
                    <span>First Name *</span>
                    <input class="custom-input"
                           :class="{ 'is-error': hasError('vehicle.drivers') && !d.firstName }"
                           v-model="d.firstName"
                           @input="touch()" />
                </label>
                <label class="field">
                    <span>Last Name *</span>
                    <input class="custom-input"
                           :class="{ 'is-error': hasError('vehicle.drivers') && !d.lastName }"
                           v-model="d.lastName"
                           @input="touch()" />
                </label>
                <label class="field">
                    <span>Tax ID (CNP) *</span>
                    <input class="custom-input"
                           :class="{ 'is-error': hasError('vehicle.drivers') && !d.taxId }"
                           v-model="d.taxId"
                           @input="touch()" />
                </label>
                <label class="field">
                    <span>ID Number *</span>
                    <input class="custom-input"
                           :class="{ 'is-error': hasError('vehicle.drivers') && !d.identification.idNumber }"
                           v-model="d.identification.idNumber"
                           @input="touch()" />
                </label>
            </div>
        </div>

        <button class="btn btn-brand" type="button" @click="addDriver(); touch()">+ Add Another Driver</button>
    </div>
</template>

<style scoped>
.sectionTitle {
    font-size: 11px; font-weight: 600; letter-spacing: .08em;
    text-transform: uppercase; color: var(--muted); margin-bottom: 14px;
}
.subTitle {
    grid-column: 1 / -1;
    display: flex; align-items: center; gap: 8px;
    font-size: 11px; font-weight: 600; letter-spacing: .08em;
    text-transform: uppercase; color: var(--muted);
    margin: 14px 0 4px;
    padding-top: 12px;
    border-top: 1px solid rgba(255,255,255,.06);
}
.subTitle::before {
    content: ''; width: 3px; height: 12px;
    border-radius: 999px; background: var(--accent); flex-shrink: 0;
}
.grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.grid3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
@media (max-width: 1000px) {
    .grid3 { grid-template-columns: 1fr 1fr; }
    .grid2 { grid-template-columns: 1fr; }
}
.field { display: flex; flex-direction: column; gap: 5px; }
.field span {
    font-size: 11px; font-weight: 500; color: var(--muted);
    letter-spacing: .04em; text-transform: uppercase;
}

.driverWarn {
    padding: 9px 14px;
    border-radius: 10px;
    border: 1px solid rgba(248,113,113,.35);
    background: rgba(248,113,113,.08);
    margin-bottom: 12px;
    font-size: 13px;
}

/* Glassy driver card */
.driverCard {
    margin: 8px 0 12px;
    padding: 14px 16px;
    border: 1px solid rgba(255,255,255,.10);
    border-radius: 12px;
    background: rgba(255,255,255,.04);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

.driverTop {
    display: flex; justify-content: space-between;
    align-items: center; margin-bottom: 12px;
}
.driverTop strong { font-size: 13px; font-weight: 600; }

.driverTopActions { display: flex; gap: 8px; align-items: center; }

/* Copy from customer — teal/green glassy */
.btn-copy {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 12px; padding: 6px 14px;
    border-radius: 8px;
    border: 1px solid rgba(34,197,94,.40);
    background: rgba(34,197,94,.10);
    color: #4ade80;
    font-weight: 500;
    cursor: pointer;
    backdrop-filter: blur(8px);
    transition: all .14s;
}
.btn-copy:hover {
    background: rgba(34,197,94,.18);
    border-color: rgba(34,197,94,.60);
    box-shadow: 0 0 12px rgba(34,197,94,.15);
}

:deep(.dp__clear_icon), :deep(.dp__action_clear),
:deep(.dp__btn.dp__action_clear), :deep(.dp__input_icon), :deep(.dp__icon) {
    display: none !important;
}
:deep(.dp__input_wrap) { border-radius: 10px !important; }
.dp-wrapper :deep(.dp__input) { padding-left: 14px !important; }
.dp-error :deep(.dp__input) {
    border-color: rgba(248,113,113,.60) !important;
    box-shadow: 0 0 0 3px rgba(248,113,113,.14) !important;
}
</style>
