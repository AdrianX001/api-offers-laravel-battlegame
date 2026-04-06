<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuoteController;

Route::post('/auth', [QuoteController::class, 'authenticate']);
Route::delete('/auth', [QuoteController::class, 'deauthenticate']);
Route::patch('/auth', [QuoteController::class, 'renewToken']);
Route::get('/auto-auth', [QuoteController::class, 'autoAuthenticate']);

Route::post('/offer', [QuoteController::class, 'getOffer']);

// Download offer PDF - matches external API: GET /offer/{id}
// Frontend calls: /api/offer/{offerId}/pdf (we support both patterns)
Route::get('/offer/{offerId}', [QuoteController::class, 'downloadOfferPdf']);
Route::get('/offer/{offerId}/pdf', [QuoteController::class, 'downloadOfferPdf']);

// Create policy from offer - matches external API: POST /policy
// Frontend calls: /api/offer/{offerId}/policy OR /api/policy
Route::post('/offer/{offerId}/policy', [QuoteController::class, 'createPolicy']);
Route::post('/policy', [QuoteController::class, 'createPolicy']);

// Check if policy already exists for an offer
Route::get('/offer/{offerId}/check-policy', [QuoteController::class, 'checkPolicyExists']);

// Download policy PDF - matches external API: GET /policy/{id}
// Also supports: GET /policy (general policy PDF file endpoint)
Route::get('/policy/{policyId}', [QuoteController::class, 'downloadPolicyPdf']);
Route::get('/policy/{policyId}/pdf', [QuoteController::class, 'downloadPolicyPdf']);
Route::get('/policy', [QuoteController::class, 'getPolicyPdfFile']);

Route::post('/quote/save', [QuoteController::class, 'store']);
Route::get('/quote', [QuoteController::class, 'index']);
Route::get('/quote/{id}', [QuoteController::class, 'show']);

// Nomenclature endpoints
Route::get('/nomenclature/counties', [QuoteController::class, 'getCounties']);
Route::get('/nomenclature/countries', [QuoteController::class, 'getCountries']);
Route::get('/nomenclature/localities/{countyCode}', [QuoteController::class, 'getLocalities']);
Route::get('/vehicle', [QuoteController::class, 'getVehicleInfo']);
Route::get('/company/{taxId}', [QuoteController::class, 'getCompanyInfo']);
Route::get('/product', [QuoteController::class, 'getProducts']);
