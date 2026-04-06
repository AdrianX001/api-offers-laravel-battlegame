<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetOfferRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // --- Provider ---
            //'provider.organization.businessName' => 'required|string',
            //'provider.authentication.account'    => 'required|string',
            //'provider.authentication.password'   => 'required|string',

            // --- Motor ---
            'product.motor.startDate'              => 'required|date_format:Y-m-d|after:today',
            'product.motor.termTime'               => 'required|integer|min:1|max:12',
            'product.motor.installmentCount'       => 'required|integer|min:1|max:4',
            'product.motor.commissionPercentLimit' => 'required|numeric|min:0',
            'product.motor.generatePaymentLink'    => 'nullable|boolean',
            'product.motor.webhookLink'            => 'nullable|url',
            'product.motor.renewPolicy'            => 'nullable|array',
            'product.motor.renewPolicy.series'     => 'required_with:product.motor.renewPolicy|string',
            'product.motor.renewPolicy.number'     => 'required_with:product.motor.renewPolicy|numeric',

            // --- Policy Holder (lowercase 'h' to match API spec) ---
            'product.policyholder.businessName'          => 'nullable|string',
            'product.policyholder.companyRegistryNumber' => 'nullable|string',
            'product.policyholder.caenCode'              => 'nullable|integer',

            // Name required if not a business
            'product.policyholder.lastName'  => 'required_without:product.policyholder.businessName|string',
            'product.policyholder.firstName' => 'required_without:product.policyholder.businessName|string',

            'product.policyholder.taxId'           => 'required|string',
            'product.policyholder.isForeignPerson' => 'boolean',

            // Conditional requirements for Foreign Persons
            'product.policyholder.nationality' => 'required_if:product.policyholder.isForeignPerson,true|nullable|string|size:2',
            'product.policyholder.citizenship' => 'required_if:product.policyholder.isForeignPerson,true|nullable|string|size:2',
            'product.policyholder.gender'      => ['required_if:product.policyholder.isForeignPerson,true', 'nullable', Rule::in(['m', 'f'])],
            'product.policyholder.birthdate'   => 'required_if:product.policyholder.isForeignPerson,true|nullable|date_format:Y-m-d',

            'product.policyholder.email'        => 'nullable|email',
            'product.policyholder.mobileNumber' => 'nullable',

            // Identification
            'product.policyholder.identification.idType'         => 'nullable|string',
            'product.policyholder.identification.idNumber'       => 'required_without:product.policyholder.businessName|string',
            'product.policyholder.identification.issueAuthority' => 'nullable|string',
            'product.policyholder.identification.issueDate'      => 'nullable|date_format:Y-m-d',

            // Driving License
            'product.policyholder.drivingLicense'           => 'nullable|array',
            'product.policyholder.drivingLicense.issueDate' => 'nullable|date_format:Y-m-d',

            // Address
            'product.policyholder.address.country'    => 'nullable|string|size:2',
            'product.policyholder.address.county'     => 'required|string',
            'product.policyholder.address.city'       => 'required|string',
            'product.policyholder.address.cityCode'   => 'required|numeric',
            'product.policyholder.address.street'     => 'required|string',
            'product.policyholder.address.houseNumber'=> 'nullable|string',
            'product.policyholder.address.building'   => 'nullable|string',
            'product.policyholder.address.staircase'  => 'nullable|string',
            'product.policyholder.address.apartment'  => 'nullable|string',
            'product.policyholder.address.floor'      => 'nullable|string',
            'product.policyholder.address.postcode'   => 'nullable|string',

            'product.policyholder.hasDisability'      => 'nullable|boolean',
            'product.policyholder.isRetired'          => 'nullable|boolean',

            // --- Vehicle Owner (Similar conditionals) ---
            'product.vehicle.owner.businessName'          => 'nullable|string',
            'product.vehicle.owner.companyRegistryNumber' => 'nullable|string',
            'product.vehicle.owner.caenCode'              => 'nullable|integer',
            'product.vehicle.owner.lastName'     => 'required_without:product.vehicle.owner.businessName|string',
            'product.vehicle.owner.firstName'    => 'required_without:product.vehicle.owner.businessName|string',
            'product.vehicle.owner.taxId'        => 'required|string',
            'product.vehicle.owner.isForeignPerson' => 'boolean',

            'product.vehicle.owner.nationality' => 'required_if:product.vehicle.owner.isForeignPerson,true|nullable|string|size:2',
            'product.vehicle.owner.citizenship' => 'required_if:product.vehicle.owner.isForeignPerson,true|nullable|string|size:2',
            'product.vehicle.owner.gender'      => ['required_if:product.vehicle.owner.isForeignPerson,true', 'nullable', Rule::in(['m', 'f'])],
            'product.vehicle.owner.birthdate'   => 'required_if:product.vehicle.owner.isForeignPerson,true|nullable|date_format:Y-m-d',

            'product.vehicle.owner.email'        => 'nullable|email',
            'product.vehicle.owner.mobileNumber' => 'nullable',

            // Owner Identification
            'product.vehicle.owner.identification'               => 'nullable|array',
            'product.vehicle.owner.identification.idType'        => 'nullable|string',
            'product.vehicle.owner.identification.idNumber'      => 'required_without:product.vehicle.owner.businessName|string',
            'product.vehicle.owner.identification.issueAuthority'=> 'nullable|string',
            'product.vehicle.owner.identification.issueDate'     => 'nullable|date_format:Y-m-d',

            // Owner Driving License
            'product.vehicle.owner.drivingLicense'           => 'nullable|array',
            'product.vehicle.owner.drivingLicense.issueDate' => 'nullable|date_format:Y-m-d',

            // Owner Address
            'product.vehicle.owner.address.country'   => 'nullable|string|size:2',
            'product.vehicle.owner.address.county'    => 'required|string',
            'product.vehicle.owner.address.city'      => 'required|string',
            'product.vehicle.owner.address.cityCode'  => 'required|numeric',
            'product.vehicle.owner.address.street'    => 'required|string',
            'product.vehicle.owner.address.houseNumber'=> 'nullable|string',
            'product.vehicle.owner.address.building'  => 'nullable|string',
            'product.vehicle.owner.address.staircase' => 'nullable|string',
            'product.vehicle.owner.address.apartment' => 'nullable|string',
            'product.vehicle.owner.address.floor'     => 'nullable|string',
            'product.vehicle.owner.address.postcode'  => 'nullable|string',

            'product.vehicle.owner.hasDisability' => 'nullable|boolean',
            'product.vehicle.owner.isRetired'     => 'nullable|boolean',

            // --- Drivers ---
            'product.vehicle.driver' => 'nullable|array',
            'product.vehicle.driver.*.lastName' => 'required|string',
            'product.vehicle.driver.*.firstName' => 'required|string',
            'product.vehicle.driver.*.taxId' => 'required|string',
            'product.vehicle.driver.*.identification.idNumber' => 'required|string',
            'product.vehicle.driver.*.mobileNumber' => 'nullable|string',

            // --- Vehicle ---
            'product.vehicle.licensePlate'       => 'nullable|string',
            'product.vehicle.registrationType'   => 'required|string',
            'product.vehicle.vin'                => 'required|string',
            'product.vehicle.vehicleType'        => 'required|string',
            'product.vehicle.brand'              => 'required|string',
            'product.vehicle.model'              => 'required|string',
            'product.vehicle.yearOfConstruction' => 'required|integer|min:1900',
            'product.vehicle.engineDisplacement' => 'nullable|numeric',
            'product.vehicle.enginePower'        => 'nullable|numeric',
            'product.vehicle.totalWeight'        => 'required|numeric',
            'product.vehicle.seats'              => 'nullable|integer',
            'product.vehicle.fuelType'           => ['nullable', Rule::in(['diesel', 'petrol', 'electric', 'hybrid', 'lpg'])],
            'product.vehicle.firstRegistration'  => 'nullable|date_format:Y-m-d',
            'product.vehicle.usageType'          => 'required|string',
            'product.vehicle.identification.idNumber' => 'required|string',
            'product.vehicle.currentMileage'          => 'nullable|numeric',
            'product.vehicle.hasMobilityModifications'=> 'nullable|boolean',
            'product.vehicle.isLeased'                => 'nullable|boolean',
            'product.vehicle.isNew'                   => 'nullable|boolean',

            // --- Additional Data (optional) ---
            'product.additionalData' => 'nullable|array',
        ];
    }
}
