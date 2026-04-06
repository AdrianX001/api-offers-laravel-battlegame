import { reactive } from "vue";

export function useValidation(formData, clientType) {

    const errors = reactive({});
    const touched = reactive({});

    function markTouched(path) {
        touched[path] = true;
    }

    function hasError(path) {
        const stepKey = path.split(".")[0];
        return !!(errors[path] && (touched[path] || touched[stepKey]));
    }

    function clearStep(stepKey) {
        Object.keys(errors).forEach(k => {
            if (k.startsWith(stepKey + ".")) {
                delete errors[k];
            }
        });
    }

    function req(v) {
        return v !== null && v !== undefined && String(v).trim() !== "";
    }

    function reqNum(v) {
        return v !== null &&
            v !== undefined &&
            String(v).trim() !== "" &&
            !Number.isNaN(Number(v));
    }

    function validateStep(stepKey) {

        clearStep(stepKey);

        const m = formData.product.motor;
        const ph = formData.product.policyHolder;
        const v = formData.product.vehicle;

        if (stepKey === "applicant") {
            if (!clientType.value) {
                errors["applicant.clientType"] = true;
            }
        }

        if (stepKey === "policyholder") {

            if (!req(ph.taxId))
                errors["policyholder.taxId"] = true;

            if (!req(ph.mobileNumber))
                errors["policyholder.mobileNumber"] = true;

            if (!req(ph.address.county))
                errors["policyholder.county"] = true;

            if (!req(ph.address.city))
                errors["policyholder.city"] = true;

            if (!reqNum(ph.address.cityCode))
                errors["policyholder.cityCode"] = true;

            if (!req(ph.address.street))
                errors["policyholder.street"] = true;

            if (!req(ph.address.houseNumber))
                errors["policyholder.houseNumber"] = true;

            if (!req(ph.address.postcode))
                errors["policyholder.postcode"] = true;

            if (clientType.value === "company") {
                if (!req(ph.businessName))
                    errors["policyholder.businessName"] = true;

                if (!req(ph.companyRegistryNumber))
                    errors["policyholder.companyRegistryNumber"] = true;

                if (!reqNum(ph.caenCode))
                    errors["policyholder.caenCode"] = true;

            } else {

                if (!req(ph.firstName))
                    errors["policyholder.firstName"] = true;

                if (!req(ph.lastName))
                    errors["policyholder.lastName"] = true;

                if (!req(ph.identification?.idNumber))
                    errors["policyholder.idNumber"] = true;

                if (!req(ph.drivingLicense?.issueDate))
                    errors["policyholder.drivingLicenseIssueDate"] = true;

                if (ph.isForeignPerson) {

                    if (!req(ph.nationality))
                        errors["policyholder.nationality"] = true;

                    if (!req(ph.citizenship))
                        errors["policyholder.citizenship"] = true;

                    if (!req(ph.identification?.idType))
                        errors["policyholder.idType"] = true;

                    if (!req(ph.identification?.issueAuthority))
                        errors["policyholder.issueAuthority"] = true;

                    if (!req(ph.identification?.issueDate))
                        errors["policyholder.issueDate"] = true;
                }
            }
        }

        if (stepKey === "vehicle") {

            if (!req(v.licensePlate))
                errors["vehicle.licensePlate"] = true;

            if (!req(v.vin))
                errors["vehicle.vin"] = true;

            if (!req(v.brand))
                errors["vehicle.brand"] = true;

            if (!req(v.model))
                errors["vehicle.model"] = true;

            if (!reqNum(v.yearOfConstruction))
                errors["vehicle.year"] = true;

            if (!req(v.vehicleType))
                errors["vehicle.type"] = true;

            if (!req(v.usageType))
                errors["vehicle.usage"] = true;

            if (!reqNum(v.totalWeight))
                errors["vehicle.weight"] = true;

            if (!reqNum(v.seats))
                errors["vehicle.seats"] = true;

            if (!req(v.fuelType))
                errors["vehicle.fuel"] = true;

            if (!reqNum(v.engineDisplacement))
                errors["vehicle.cc"] = true;

            if (!reqNum(v.enginePower))
                errors["vehicle.kw"] = true;

            if (!req(v.identification?.idNumber))
                errors["vehicle.civ"] = true;

            if (!req(v.firstRegistration))
                errors["vehicle.firstReg"] = true;

            if (!reqNum(v.currentMileage))
                errors["vehicle.mileage"] = true;

            const pti =
                formData.product.additionalData?.product?.vehicle?.expirationDatePti;

            if (!req(pti))
                errors["vehicle.pti"] = true;

            const driversOk =
                Array.isArray(v.driver) &&
                v.driver.length > 0 &&
                v.driver.every(d =>
                    req(d.firstName) &&
                    req(d.lastName) &&
                    req(d.taxId) &&
                    req(d.identification?.idNumber)
                );

            if (!driversOk)
                errors["vehicle.drivers"] = true;
        }

        if (stepKey === "motor") {

            if (!req(m.startDate))
                errors["motor.startDate"] = true;

            if (!reqNum(m.termTime))
                errors["motor.termTime"] = true;

            if (!reqNum(m.installmentCount))
                errors["motor.installmentCount"] = true;

            if (!reqNum(m.commissionPercentLimit))
                errors["motor.commissionPercentLimit"] = true;
        }
    }

    function validateAll() {
        validateStep("applicant");
        validateStep("policyholder");
        validateStep("vehicle");
        validateStep("motor");
    }

    return {
        errors,
        touched,
        markTouched,
        hasError,
        validateStep,
        validateAll
    };
}
