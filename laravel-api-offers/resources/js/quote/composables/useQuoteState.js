import { reactive, ref, watch } from "vue";

export function useQuoteState() {

    const clientType = ref("individual");
    const ownerSameAsPolicyholder = ref(true);

    const formData = reactive({
        product: {
            motor: {
                startDate: new Date(Date.now() + 2 * 86400000)
                    .toISOString()
                    .split("T")[0],
                termTime: 12,
                installmentCount: 1,
                commissionPercentLimit: 10,
            },

            policyHolder: {
                firstName: "",
                lastName: "",
                businessName: "",
                taxId: "",
                mobileNumber: "",
                email: "",
                isForeignPerson: false,

                identification: {
                    idNumber: "",
                    idType: "",
                    issueAuthority: "",
                    issueDate: "",
                },

                drivingLicense: {
                    issueDate: "",
                },

                address: {
                    country: "RO",
                    county: "",
                    city: "",
                    cityCode: "",
                    street: "",
                    houseNumber: "",
                    floor: "",
                    building: "",
                    staircase: "",
                    apartment: "",
                    postcode: "",
                },
            },

            vehicle: {
                licensePlate: "",
                vin: "",
                brand: "",
                model: "",
                yearOfConstruction: "",
                vehicleType: "",
                usageType: "",
                totalWeight: "",
                seats: "",
                fuelType: "",
                engineDisplacement: "",
                enginePower: "",
                firstRegistration: "",
                currentMileage: "",

                identification: {
                    idNumber: "",
                },

                registrationType: "registered",

                owner: {
                    address: {
                        country: "RO",
                        county: "",
                        city: "",
                        cityCode: "",
                        street: "",
                        houseNumber: "",
                        postcode: "",
                    },
                },

                driver: [
                    {
                        firstName: "",
                        lastName: "",
                        taxId: "",
                        identification: { idNumber: "" }
                    }
                ],
            },

            additionalData: {
                product: {
                    vehicle: {
                        expirationDatePti: ""
                    }
                }
            },
        },
    });

    function addDriver() {
        formData.product.vehicle.driver.push({
            firstName: "",
            lastName: "",
            taxId: "",
            identification: { idNumber: "" }
        });
    }

    function removeDriver(i) {
        formData.product.vehicle.driver.splice(i, 1);
    }

    watch(ownerSameAsPolicyholder, (v) => {
        if (v) {
            formData.product.vehicle.owner =
                structuredClone(formData.product.policyHolder);
        }
    });

    watch(
        () => formData.product.policyHolder,
        (ph) => {
            if (ownerSameAsPolicyholder.value) {
                formData.product.vehicle.owner =
                    structuredClone(ph);
            }
        },
        { deep: true }
    );

    return {
        formData,
        clientType,
        ownerSameAsPolicyholder,
        addDriver,
        removeDriver
    };
}
