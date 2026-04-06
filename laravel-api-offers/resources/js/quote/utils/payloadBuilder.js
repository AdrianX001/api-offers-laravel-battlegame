function num(v) {
    return v !== null && v !== undefined && v !== "" ? Number(v) : undefined;
}

function buildAddress(a) {
    return {
        country: a.country || "RO",
        county: a.county,
        city: a.city,
        cityCode: num(a.cityCode),
        street: a.street,
        ...(a.houseNumber  && { houseNumber: a.houseNumber }),
        ...(a.postcode     && { postcode: a.postcode }),
        ...(a.floor        && { floor: a.floor }),
        ...(a.building     && { building: a.building }),
        ...(a.staircase    && { staircase: a.staircase }),
        ...(a.apartment    && { apartment: a.apartment }),
    };
}

export function buildPayload(formData, clientType) {

    const ph = formData.product.policyHolder;
    const owner = ph; // owner is always the policyholder

    const base = (d) => ({
        taxId: d.taxId,
        email: d.email,
        mobileNumber: d.mobileNumber,
        address: buildAddress(d.address),
        ...(d.identification?.idNumber && {
            identification: {
                idNumber: d.identification.idNumber,
                idType:   d.identification.idType || "CI",
                ...(d.identification.issueAuthority && { issueAuthority: d.identification.issueAuthority }),
                ...(d.identification.issueDate      && { issueDate: d.identification.issueDate }),
            }
        }),
        ...(d.drivingLicense?.issueDate && {
            drivingLicense: { issueDate: d.drivingLicense.issueDate }
        }),
    });

    const policyholder =
        clientType === "company"
            ? { ...base(ph), businessName: ph.businessName }
            : { ...base(ph), firstName: ph.firstName, lastName: ph.lastName };

    const vehicle = {
        ...formData.product.vehicle,
        yearOfConstruction: num(formData.product.vehicle.yearOfConstruction),
        totalWeight: num(formData.product.vehicle.totalWeight),
        owner:
            clientType === "company"
                ? { ...base(owner), businessName: owner.businessName }
                : { ...base(owner), firstName: owner.firstName, lastName: owner.lastName }
    };

    return {
        product: {
            motor: formData.product.motor,
            policyholder,
            vehicle,
            additionalData: formData.product.additionalData
        }
    };
}
