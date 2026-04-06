<script setup>
import { computed, ref } from "vue";

const props = defineProps({
    formData: Object,
    offers: Object,
    providerStats: Object,
    providerErrors: Object,
    loading: Object,
    error: Object,
    downloadOfferPdf: Function,
    createPolicy: Function,
    downloadPolicyPdf: Function,
    resetQuote: Function,
});

const summary = computed(() => {
    const p = props.formData.product;
    return {
        startDate: p.motor.startDate,
        term: p.motor.termTime,
        inst: p.motor.installmentCount,
        plate: p.vehicle.licensePlate,
        vin: p.vehicle.vin,
        vehicle: `${p.vehicle.brand} ${p.vehicle.model}`.trim(),
        year: p.vehicle.yearOfConstruction,
        name: p.policyHolder.businessName
            ? p.policyHolder.businessName
            : `${p.policyHolder.firstName} ${p.policyHolder.lastName}`.trim(),
        taxId: p.policyHolder.taxId,
        county: p.policyHolder.address.county,
        city: p.policyHolder.address.city,
        street: p.policyHolder.address.street,
    };
});

function directCompAmount(offer) {
    if (!offer?.directCompensation) return null;
    if (typeof offer.directCompensation === "object") {
        return offer.directCompensation.premiumAmount ?? null;
    }
    return null;
}


</script>

<template>
    <div class="reviewWrap">
        <div class="summaryCard">
            <div class="sumTitle">Final details</div>
            <div class="sumGrid">
                <div class="sumRow"><span>Policy start</span><b>{{ summary.startDate }}</b></div>
                <div class="sumRow"><span>Term</span><b>{{ summary.term }} months</b></div>
                <div class="sumRow"><span>Installments</span><b>{{ summary.inst }}</b></div>
                <div class="sumRow"><span>Plate</span><b>{{ summary.plate }}</b></div>
                <div class="sumRow"><span>VIN</span><b>{{ summary.vin }}</b></div>
                <div class="sumRow"><span>Vehicle</span><b>{{ summary.vehicle }} ({{ summary.year || "—" }})</b></div>
                <div class="sumRow"><span>Client</span><b>{{ summary.name }}</b></div>
                <div class="sumRow"><span>Tax ID</span><b>{{ summary.taxId }}</b></div>
                <div class="sumRow"><span>Address</span><b>{{ summary.county }}, {{ summary.city }}, {{ summary.street }}</b></div>
            </div>
        </div>

        <div v-if="providerStats?.value" class="meta">
            Received offers from <b>{{ providerStats.value.successful }}</b>/<b>{{ providerStats.value.total }}</b> providers
        </div>

        <!-- Loading skeletons -->
        <div v-if="loading?.value" class="skeletonWrap">
            <div class="loadingMeta">
                <span class="spinner-border spinner-border-sm me-2"></span>
                Fetching offers from all providers…
            </div>
            <div class="offersGrid">
                <div v-for="i in 6" :key="i" class="offerCard skeleton">
                    <div class="offerBadge skLine skLine--short"></div>
                    <div class="offerBody">
                        <div class="skLine skLine--medium"></div>
                        <div class="skLine skLine--price"></div>
                        <div class="skLine skLine--long"></div>
                        <div class="skLine skLine--long"></div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="error?.value" class="errorBox">{{ error.value }}</div>

        <div v-if="offers?.value && offers.value.length" class="results">
            <h4 class="resultsTitle">Available offers</h4>

            <div class="offersGrid">
                <div v-for="offer in offers.value" :key="offer.offerId || offer.id || offer.providerName" class="offerCard">
                    <div class="offerBadge">{{ offer.providerName }}</div>

                    <div class="offerBody">
                        <div class="offerProvider">{{ offer.providerBusinessName || offer.providerName }}</div>

                        <div class="offerPrice">
                            <span class="currency">{{ offer.currency || "RON" }}</span>
                            <span class="amount">{{ offer.premiumAmount ?? "N/A" }}</span>
                            <span class="period">/year</span>
                        </div>

                        <div class="offerDetails">
                            <div class="row"><span>Period</span><b>{{ offer.startDate }} → {{ offer.endDate }}</b></div>
                            <div class="row"><span>Bonus-Malus</span><b>{{ offer.bonusMalusClass || "N/A" }}</b></div>

                            <div class="row">
                                <span>Commission</span>
                                <b>
                                    {{ offer.commissionPercent ?? "—" }}%
                                    <template v-if="offer.commissionValue !== undefined && offer.commissionValue !== null">
                                        ({{ offer.commissionValue }} {{ offer.currency || "RON" }})
                                    </template>
                                </b>
                            </div>

                            <div class="row" v-if="directCompAmount(offer) !== null">
                                <span>Direct Compensation</span>
                                <b>{{ directCompAmount(offer) }} {{ offer.currency || "RON" }}</b>
                            </div>
                        </div>

                        <div class="actions">
                            <button class="btn w-100" :disabled="offer.downloadingOffer" @click="downloadOfferPdf?.(offer)">
                                <span v-if="offer.downloadingOffer" class="spinner-border spinner-border-sm me-2"></span>
                                Download Offer
                            </button>

                            <button
                                class="btn btn-brand w-100"
                                :disabled="offer.creatingPolicy || offer.policyAlreadyExists"
                                @click="createPolicy?.(offer)"
                            >
                                <span v-if="offer.creatingPolicy" class="spinner-border spinner-border-sm me-2"></span>
                                {{ offer.policyAlreadyExists ? "Policy Already Created" : "Create Policy" }}
                            </button>
                        </div>

                        <div v-if="offer.policyAlreadyExists && offer.existingPolicyInfo" class="policyBox">
                            <div class="policyTitle">Policy Created!</div>
                            <div class="policyRow" v-if="offer.policyId">
                                <span>Policy ID:</span>
                                <b>{{ offer.policyId }}</b>
                            </div>
                            <div class="policyRow" v-if="offer.existingPolicyInfo.policyNumber || offer.existingPolicyInfo.policy_number || offer.existingPolicyInfo.number">
                                <span>Policy Number:</span>
                                <b>{{ offer.existingPolicyInfo.policyNumber ?? offer.existingPolicyInfo.policy_number ?? offer.existingPolicyInfo.number }}</b>
                            </div>
                            <div class="policyRow" v-if="offer.existingPolicyInfo.series">
                                <span>Series:</span>
                                <b>{{ offer.existingPolicyInfo.series }}</b>
                            </div>

                            <button class="btn btn-brand w-100" :disabled="offer.downloadingPolicy" @click="downloadPolicyPdf?.(offer)">
                                <span v-if="offer.downloadingPolicy" class="spinner-border spinner-border-sm me-2"></span>
                                Download Policy PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<style scoped>
.reviewWrap { display: grid; gap: 16px; max-width: 980px; margin: 0 auto; }

/* Summary card */
.summaryCard {
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,.10);
    background: rgba(255,255,255,.05);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    box-shadow: 0 4px 24px rgba(0,0,0,.35);
    padding: 16px 20px;
}
.sumTitle {
    font-size: 11px; font-weight: 700; letter-spacing: .1em;
    text-transform: uppercase; color: var(--muted);
    font-family: 'JetBrains Mono', monospace; margin-bottom: 12px;
    display: flex; align-items: center; gap: 8px;
}
.sumTitle::before {
    content: ''; display: block;
    width: 3px; height: 12px;
    border-radius: 999px; background: var(--accent);
}
.sumGrid { display: grid; gap: 8px; }
.sumRow { display: flex; justify-content: space-between; gap: 10px; font-size: 13px; font-family: 'JetBrains Mono', monospace; }
.sumRow span { color: var(--muted); }

.meta { font-size: 11px; color: var(--muted); font-family: 'JetBrains Mono', monospace; }

.errorBox {
    padding: 12px 18px;
    border-radius: 16px;
    border: 1px solid color-mix(in srgb, var(--danger) 40%, var(--border));
    background: color-mix(in srgb, var(--dangerGlow) 50%, var(--card2));
    font-family: 'JetBrains Mono', monospace;
    font-size: 13px;
}

.resultsTitle {
    font-size: 11px; font-weight: 700; letter-spacing: .1em;
    text-transform: uppercase; color: var(--muted);
    font-family: 'JetBrains Mono', monospace; margin: 0 0 14px;
    display: flex; align-items: center; gap: 8px;
}
.resultsTitle::before {
    content: ''; display: block;
    width: 3px; height: 12px;
    border-radius: 999px; background: var(--accent);
}

/* Offer grid */
.offersGrid { display: grid; grid-template-columns: 1fr; gap: 14px; }
@media (min-width: 900px) { .offersGrid { grid-template-columns: 1fr 1fr; } }

/* Offer card — solid, sharp */
.offerCard {
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,.10);
    background: rgba(255,255,255,.05);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    overflow: hidden;
    box-shadow: 0 4px 24px rgba(0,0,0,.35);
    transition: transform .15s, box-shadow .15s;
}
.offerCard:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg), 0 0 0 1px var(--border3);
}

.offerBadge {
    padding: 10px 16px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    font-family: 'JetBrains Mono', monospace;
    color: var(--accent);
    background: color-mix(in srgb, var(--accentDim) 80%, var(--card));
    border-bottom: 1px solid var(--border);
}

.offerBody { padding: 16px 18px; }

.offerProvider {
    font-size: 15px;
    font-weight: 700;
    margin-bottom: 10px;
    letter-spacing: -.01em;
}

.offerPrice { display: flex; align-items: baseline; gap: 5px; margin-bottom: 12px; }
.currency { color: var(--muted); font-size: 12px; font-family: 'JetBrains Mono', monospace; }
.amount {
    font-size: 30px;
    font-weight: 800;
    font-family: 'JetBrains Mono', monospace;
    letter-spacing: -.02em;
    color: var(--fg);
}
.period { color: var(--muted); font-size: 11px; font-family: 'JetBrains Mono', monospace; }

.offerDetails {
    border-top: 1px solid var(--border);
    padding-top: 12px;
    display: grid; gap: 7px;
    font-size: 12px;
    font-family: 'JetBrains Mono', monospace;
}
.offerDetails .row { display: flex; justify-content: space-between; gap: 10px; }
.offerDetails span { color: var(--muted); }

.actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-top: 14px;
}
@media (max-width: 520px) { .actions { grid-template-columns: 1fr; } }

/* Policy success box */
.policyBox {
    margin-top: 14px;
    padding: 14px 18px;
    border-radius: 16px;
    border: 1px solid color-mix(in srgb, var(--success) 30%, var(--border));
    background: color-mix(in srgb, var(--successGlow) 55%, var(--card));
    box-shadow: 0 0 20px var(--successGlow);
    display: grid; gap: 7px;
}
.policyTitle {
    font-size: 11px; font-weight: 700; letter-spacing: .1em;
    text-transform: uppercase; color: var(--success);
    font-family: 'JetBrains Mono', monospace;
}
.policyRow { display: flex; justify-content: space-between; gap: 10px; font-size: 12px; font-family: 'JetBrains Mono', monospace; }
.policyRow span { color: var(--muted); }

/* Skeleton */
.skeletonWrap { display: grid; gap: 14px; }
.loadingMeta { display: flex; align-items: center; gap: 10px; font-size: 12px; color: var(--muted); font-family: 'JetBrains Mono', monospace; }

@keyframes shimmer {
    0%   { background-position: -500px 0; }
    100% { background-position: 500px 0; }
}
.skeleton .offerBadge,
.skLine {
    background: linear-gradient(90deg,
    var(--card3) 25%,
    color-mix(in srgb, var(--card3) 60%, var(--border)) 50%,
    var(--card3) 75%);
    background-size: 500px 100%;
    animation: shimmer 1.5s infinite;
    border-radius: 999px;
    height: 12px;
    margin-bottom: 10px;
}
.skLine--short  { width: 38%; }
.skLine--medium { width: 58%; }
.skLine--long   { width: 88%; }
.skLine--price  { height: 26px; width: 45%; margin: 10px 0; border-radius: 999px; }
</style>
