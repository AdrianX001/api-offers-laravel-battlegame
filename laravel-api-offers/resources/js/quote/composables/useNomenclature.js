import { ref } from "vue";

function headers(token) {
    return {
        Authorization: `Bearer ${token}`,
        Accept: "application/json"
    };
}

export function useNomenclature(token) {

    const counties     = ref([]);
    const localities   = ref([]);
    const countries    = ref([]);
    const vehicleTypes = ref([
        { code: "M1",   name: "Passenger car (M1 — ≤8+1 seats)" },
        { code: "M2",   name: "Minibus (M2 — >8 seats, ≤5t)" },
        { code: "M3",   name: "Bus (M3 — >8 seats, >5t)" },
        { code: "N1",   name: "Light goods vehicle (N1 — ≤3.5t)" },
        { code: "N2",   name: "Medium goods vehicle (N2 — 3.5-12t)" },
        { code: "N3",   name: "Heavy goods vehicle (N3 — >12t)" },
        { code: "L1e",  name: "Moped (L1e — 2-wheel, ≤50cc)" },
        { code: "L2e",  name: "Moped (L2e — 3-wheel, ≤50cc)" },
        { code: "L3e",  name: "Motorcycle (L3e — 2-wheel, >50cc)" },
        { code: "L4e",  name: "Motorcycle with sidecar (L4e)" },
        { code: "L5e",  name: "Tricycle (L5e — >50cc)" },
        { code: "L6e",  name: "Light quadricycle (L6e)" },
        { code: "L7e",  name: "Heavy quadricycle (L7e)" },
        { code: "O1",   name: "Trailer (O1 — ≤0.75t)" },
        { code: "O2",   name: "Trailer (O2 — 0.75-3.5t)" },
        { code: "O3",   name: "Semi-trailer (O3 — 3.5-10t)" },
        { code: "O4",   name: "Semi-trailer (O4 — >10t)" },
        { code: "T",    name: "Tractor / Agricultural vehicle" },
    ]);

    async function loadAll() {
        const [countiesRes, countriesRes] = await Promise.all([
            fetch("/api/nomenclature/counties", { headers: headers(token) }),
            fetch("/api/nomenclature/countries", { headers: headers(token) }),
        ]);

        const countiesData  = await countiesRes.json().catch(() => ({}));
        const countriesData = await countriesRes.json().catch(() => ({}));

        counties.value  = countiesData.data  || [];
        countries.value = countriesData.data || [];
    }

    async function lookupVehicle(query) {
        // query: { vin: '...' } or { licensePlate: '...' }
        const params = new URLSearchParams(query).toString();
        const res = await fetch(`/api/vehicle?${params}`, { headers: headers(token) });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data?.error || "Vehicle not found");
        return data.data || data;
    }

    async function loadLocalities(county) {
        if (!county) {
            localities.value = [];
            return;
        }

        const r = await fetch(
            `/api/nomenclature/localities/${county}`,
            { headers: headers(token) }
        );

        const data = await r.json().catch(() => ({}));
        localities.value = data.data || [];
    }

    return {
        counties,
        localities,
        countries,
        vehicleTypes,
        loadAll,
        loadLocalities,
        lookupVehicle
    };
}
