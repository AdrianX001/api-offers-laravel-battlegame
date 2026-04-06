import { ref } from "vue";

function headers(token, accept = "application/json") {
    return {
        Authorization: `Bearer ${token}`,
        Accept: accept,
        ...(accept === "application/json" && {
            "Content-Type": "application/json"
        })
    };
}

export function useOffers(token, toast, formData) {

    const offers         = ref([]);
    const providerStats  = ref(null);
    const providerErrors = ref({});
    const loading        = ref(false);
    const error          = ref(null);

    function notify(message, type = "error") {
        if (toast?.value?.show) toast.value.show(message, type);
        else console.error(message);
    }

    async function submit(payload) {
        loading.value        = true;
        error.value          = null;
        offers.value         = [];
        providerErrors.value = {};
        providerStats.value  = null;

        try {
            const res = await fetch("/api/offer", {
                method: "POST",
                headers: headers(token),
                body: JSON.stringify(payload)
            });

            let data = {};
            try { data = await res.json(); } catch {}

            if (!res.ok) {
                error.value =
                    data?.error ||
                    data?.message ||
                    (data?.errors
                        ? Object.values(data.errors).flat().join(", ")
                        : "Failed to retrieve quotes");
                notify(error.value, "error");
                return;
            }

            offers.value = (data.data?.offers || []).map(o => ({
                ...o,
                downloadingOffer:    false,
                creatingPolicy:      false,
                downloadingPolicy:   false,
                policyAlreadyExists: false,
                existingPolicyInfo:  null
            }));

            if (data.errors) {
                providerErrors.value = data.errors;
            }

            providerStats.value = {
                total:      data.data?.totalProviders      || 0,
                successful: data.data?.successfulProviders || 0
            };

            if (offers.value.length === 0) {
                notify("No offers received from any provider.", "warning");
            } else {
                notify(`Received ${offers.value.length} offer(s) from ${providerStats.value.successful} provider(s)`, "success");
            }

        } catch (e) {
            error.value = e?.message || "An error occurred";
            notify(error.value, "error");
        } finally {
            loading.value = false;
        }
    }

    async function downloadOfferPdf(offer) {
        if (!offer?.offerId) return;
        offer.downloadingOffer = true;

        try {
            const res = await fetch(
                `/api/offer/${offer.offerId}/pdf`,
                { headers: headers(token, "application/pdf") }
            );

            if (!res.ok) throw new Error("Failed to download offer PDF");

            const blob = await res.blob();
            const url  = window.URL.createObjectURL(blob);
            const link = document.createElement("a");
            link.href     = url;
            link.download = `offer_${offer.offerId}.pdf`;
            link.click();
            window.URL.revokeObjectURL(url);
            notify("Offer PDF downloaded!", "success");
        } catch (e) {
            notify(e.message, "error");
        } finally {
            offer.downloadingOffer = false;
        }
    }

    async function createPolicy(offer) {
        if (!offer?.offerId || offer.policyAlreadyExists) return;
        offer.creatingPolicy = true;

        try {
            const ph = formData.product.policyHolder;
            const v  = formData.product.vehicle;
            const m  = formData.product.motor;

            const res = await fetch(
                `/api/offer/${offer.offerId}/policy`,
                {
                    method: "POST",
                    headers: headers(token),
                    body: JSON.stringify({
                        premiumAmount:    offer.premiumAmount ?? 0,
                        currency:         offer.currency      ?? "RON",
                        // Extra fields for DB logging
                        providerName:     offer.providerBusinessName ?? offer.providerName ?? "",
                        providerCode:     offer.providerCode          ?? "",
                        licensePlate:     v.licensePlate              ?? "",
                        firstName:        ph.firstName                ?? "",
                        lastName:         ph.lastName                 ?? "",
                        businessName:     ph.businessName             ?? "",
                        startDate:        m.startDate                 ?? "",
                        installmentCount: m.installmentCount          ?? 1,
                    })
                }
            );

            let data = {};
            try { data = await res.json(); } catch {}

            if (!res.ok) {
                if (data?.data) {
                    offer.existingPolicyInfo  = data.data;
                    offer.policyAlreadyExists = true;
                    return;
                }
                throw new Error(data?.message || data?.error || "Failed to create policy");
            }

            const policyData = data.data || data;
            offer.existingPolicyInfo  = policyData;
            offer.policyId =
                policyData?.policyId                ??
                policyData?.id                      ??
                policyData?.policies?.[0]?.policyId ??
                policyData?.policies?.[0]?.id       ??
                null;
            offer.policyAlreadyExists = true;
            notify("Policy created successfully!", "success");
        } catch (e) {
            notify(e.message, "error");
        } finally {
            offer.creatingPolicy = false;
        }
    }

    async function downloadPolicyPdf(offer) {
        const policyId =
            offer?.policyId                     ??
            offer?.existingPolicyInfo?.policyId ??
            offer?.existingPolicyInfo?.id       ??
            offer?.existingPolicyInfo?.policy_id;

        if (!policyId) {
            notify("Policy ID not found. Please try creating the policy again.", "warning");
            return;
        }

        offer.downloadingPolicy = true;

        try {
            const res = await fetch(
                `/api/policy/${policyId}`,
                { headers: headers(token, "application/pdf") }
            );

            if (!res.ok) throw new Error("Failed to download policy PDF");

            const contentType = res.headers.get("content-type") || "";
            if (contentType.includes("application/json")) {
                const data = await res.json();
                if (data?.pdfUrl) {
                    window.open(data.pdfUrl, "_blank");
                    notify("Policy PDF opened in new tab!", "success");
                    return;
                }
                throw new Error("Unexpected response format");
            }

            const blob = await res.blob();
            const url  = window.URL.createObjectURL(blob);
            const link = document.createElement("a");
            link.href     = url;
            link.download = `policy_${policyId}.pdf`;
            link.click();
            window.URL.revokeObjectURL(url);
            notify("Policy PDF downloaded!", "success");
        } catch (e) {
            notify(e.message, "error");
        } finally {
            offer.downloadingPolicy = false;
        }
    }

    return {
        offers,
        providerStats,
        providerErrors,
        loading,
        error,
        submit,
        downloadOfferPdf,
        createPolicy,
        downloadPolicyPdf
    };
}
