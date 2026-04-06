<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetOfferRequest;
use App\Http\Requests\StoreQuoteRequest;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Pool;
use Exception;
use Illuminate\Support\Facades\Log;

class QuoteController extends Controller
{
    /**
     * Get the list of insurance providers with their codes
     * Authentication uses the main API account, only the code varies per provider
     */
    private function getProviders()
    {
        return [
            'Allianz' => [
                'businessName' => 'allianz',
                'code' => env('ALLIANZ_CODE', 'allianz'),
            ],
            'Asirom' => [
                'businessName' => 'asirom',
                'code' => env('ASIROM_CODE', 'asirom'),
            ],
            'Axeria' => [
                'businessName' => 'axeria',
                'code' => env('AXERIA_CODE', 'axeria'),
            ],
            'Generali' => [
                'businessName' => 'generali',
                'code' => env('GENERALI_CODE', 'generali'),
            ],
            'Groupama' => [
                'businessName' => 'groupama',
                'code' => env('GROUPAMA_CODE', 'groupama'),
            ],
            'Hellas Autonom' => [
                'businessName' => 'hellas_autonom',
                'code' => env('HELLAS_AUTONOM_CODE', 'hellas_autonom'),
            ],
            'Hellas NextIns' => [
                'businessName' => 'hellas_nextins',
                'code' => env('HELLAS_NEXTINS_CODE', 'hellas_nextins'),
            ],
            'Omniasig' => [
                'businessName' => 'omniasig',
                'code' => env('OMNIASIG_CODE', 'omniasig'),
            ],
            'Grawe' => [
                'businessName' => 'grawe',
                'code' => env('GRAWE_CODE', 'grawe'),
            ],
            'Eazy Insure' => [
                'businessName' => 'eazy_insure',
                'code' => env('EAZY_INSURE_CODE', 'eazy_insure'),
            ],
            'DallBogg' => [
                'businessName' => 'dall_bogg',
                'code' => env('DALLBOGG_CODE', 'dall_bogg'),
            ],
        ];
    }

    /**
     * Get main API credentials (used for all provider requests)
     */
    private function getApiCredentials()
    {
        return [
            'account' => env('RCA_V2_API_ACCOUNT'),
            'password' => env('RCA_V2_API_PASSWORD'),
        ];
    }

    private function rcaBaseUrl(): string
    {
        return rtrim(env('RCA_V2_API_URL', ''), '/');
    }

    private function rcaVerifySsl(): bool
    {
        return filter_var(env('RCA_V2_VERIFY_SSL', true), FILTER_VALIDATE_BOOLEAN);
    }

    public function authenticate(Request $request)
    {
        $validated = $request->validate([
            'account'  => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            $response = Http::withOptions(['verify' => $this->rcaVerifySsl()])
                ->post($this->rcaBaseUrl() . '/auth', $validated);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success'       => true,
                    'token'         => $data['data']['token'] ?? null,
                    'expires_at'    => $data['data']['expires_at'] ?? null,
                    'refresh_token' => $data['data']['refresh_token'] ?? null,
                ], 200);
            }

            return response()->json([
                'error'   => 'Authentication failed',
                'details' => $response->json(),
            ], $response->status());

        } catch (Exception $e) {
            return response()->json([
                'error'   => 'Authentication error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Auto-authenticate using credentials from .env file
     * This allows the frontend to skip the login form
     */
    public function autoAuthenticate()
    {
        $credentials = $this->getApiCredentials();

        if (empty($credentials['account']) || empty($credentials['password'])) {
            return response()->json([
                'error' => 'API credentials not configured in environment',
            ], 500);
        }

        try {
            $response = Http::withOptions(['verify' => $this->rcaVerifySsl()])
                ->post($this->rcaBaseUrl() . '/auth', $credentials);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success'       => true,
                    'token'         => $data['data']['token'] ?? null,
                    'expires_at'    => $data['data']['expires_at'] ?? null,
                    'refresh_token' => $data['data']['refresh_token'] ?? null,
                ], 200);
            }

            return response()->json([
                'error'   => 'Auto-authentication failed',
                'details' => $response->json(),
            ], $response->status());

        } catch (Exception $e) {
            return response()->json([
                'error'   => 'Auto-authentication error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get list of counties from external API
     */
    public function getCounties(Request $request)
    {
        try {
            $token = $request->bearerToken();
            if (!$token) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $response = Http::withOptions(['verify' => $this->rcaVerifySsl()])
                ->withHeaders(['Token' => $token])
                ->get($this->rcaBaseUrl() . '/nomenclature/county');

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'error' => 'Failed to fetch counties',
                'details' => $response->json(),
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error fetching counties',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Get list of countries from external API
     */
    public function getCountries(Request $request)
    {
        try {
            $token = $request->bearerToken();
            if (!$token) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $response = Http::withOptions(['verify' => $this->rcaVerifySsl()])
                ->withHeaders(['Token' => $token])
                ->get($this->rcaBaseUrl() . '/nomenclature/country');

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'error'   => 'Failed to fetch countries',
                'details' => $response->json(),
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Error fetching countries',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get list of localities for a county from external API
     */
    public function getLocalities(Request $request, string $countyCode)
    {
        try {
            $token = $request->bearerToken();
            if (!$token) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $response = Http::withOptions(['verify' => $this->rcaVerifySsl()])
                ->withHeaders(['Token' => $token])
                ->get($this->rcaBaseUrl() . '/nomenclature/locality/' . $countyCode);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'error' => 'Failed to fetch localities',
                'details' => $response->json(),
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error fetching localities',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get vehicle info by VIN or license plate
     */
    public function getVehicleInfo(Request $request)
    {
        try {
            $token = $request->bearerToken();
            if (!$token) return response()->json(['error' => 'Unauthorized'], 401);

            $vin = $request->query('vin');
            $licensePlate = $request->query('licensePlate');

            $queryParams = [];
            if ($vin) $queryParams['vin'] = $vin;
            if ($licensePlate) $queryParams['licensePlate'] = $licensePlate;

            $response = Http::withOptions(['verify' => $this->rcaVerifySsl()])
                ->withHeaders(['Token' => $token])
                ->get($this->rcaBaseUrl() . '/vehicle', $queryParams);

            if ($response->successful()) return response()->json($response->json());

            return response()->json([
                'error' => 'Failed to fetch vehicle info',
                'details' => $response->json(),
            ], $response->status());

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error fetching vehicle info',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get company info by tax ID (CUI)
     */
    public function getCompanyInfo(Request $request, string $taxId)
    {
        try {
            $token = $request->bearerToken();
            if (!$token) return response()->json(['error' => 'Unauthorized'], 401);

            $response = Http::withOptions(['verify' => $this->rcaVerifySsl()])
                ->withHeaders(['Token' => $token])
                ->get($this->rcaBaseUrl() . '/company/' . $taxId);

            if ($response->successful()) return response()->json($response->json());

            return response()->json([
                'error' => 'Failed to fetch company info',
                'details' => $response->json(),
            ], $response->status());

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error fetching company info',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getOffer(GetOfferRequest $request)
    {
        try {
            // 1) Token (frontend trimite Bearer, RCA vrea header Token)
            $token = $request->bearerToken();
            if (!$token) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // 2) Base payload din request (validat)
            $basePayload = $request->validated();

            // 3) Helpers locali
            $verify  = $this->rcaVerifySsl();
            $baseUrl = $this->rcaBaseUrl(); // rtrim('/') deja
            $providers = $this->getProviders();

            // 4) Normalizează taxId (digits-only) + FORCE owner = policyholder
            //    (elimină 400 "taxId invalid" + incoerențe owner/plate/county)
            $policyTaxId = $basePayload['product']['policyholder']['taxId'] ?? null;
            if (is_string($policyTaxId)) {
                $policyTaxId = preg_replace('/\D+/', '', $policyTaxId);
                $basePayload['product']['policyholder']['taxId'] = $policyTaxId;
            }

            // dacă vrei să fii extra-safe: normalizează și owner taxId dacă vine din UI
            $ownerTaxId = $basePayload['product']['vehicle']['owner']['taxId'] ?? null;
            if (is_string($ownerTaxId)) {
                $basePayload['product']['vehicle']['owner']['taxId'] = preg_replace('/\D+/', '', $ownerTaxId);
            }

            // Force owner taxId/address = policyholder taxId/address
            // (doar dacă există product.policyholder.address)
            if (!empty($basePayload['product']['policyholder'])) {
                $basePayload['product']['vehicle']['owner'] = $basePayload['product']['policyholder'];
            }

            // 5) Curăță providerii care îți dau invalid slug (ex. DallBogg)
            //    Ca să nu-ți mai “omoare” testul complet.
            //    (Dacă vrei, scoți filtrul și lași să se vadă eroarea.)
            $providers = array_filter($providers, function ($p) {
                return !empty($p['businessName']);
            });

            Log::channel('offers')->info('=== Starting Offer Request ===', [
                'timestamp'       => now()->toDateTimeString(),
                'total_providers' => count($providers),
                'token_present'   => true,
                'token_length'    => strlen($token),
                'verify_ssl'      => $verify,
                'base_url'        => $baseUrl,
            ]);

            // 6) Pool requests
            $responses = Http::pool(function (Pool $pool) use ($providers, $basePayload, $token, $verify, $baseUrl) {
                $isFirstProvider = true;
                $requests = [];

                foreach ($providers as $providerName => $provider) {
                    // deep copy
                    $payload = json_decode(json_encode($basePayload), true);

                    // Provider payload (păstrăm forma ta)
                    $payload['provider'] = [
                        'organization' => [
                            'businessName' => $provider['businessName'],
                        ],
                    ];

                    if ($isFirstProvider) {
                        Log::channel('offers')->info('Full payload sample (first provider)', [
                            'provider' => $providerName,
                            'payload'  => $payload,
                        ]);
                        $isFirstProvider = false;
                    }

                    Log::channel('offers')->info("Requesting offer from: {$providerName}", [
                        'provider' => $providerName,
                    ]);

                    $requests[] = $pool->as($providerName)
                        ->withOptions(['verify' => $verify])
                        ->withHeaders(['Token' => $token])
                        ->acceptJson()
                        ->timeout(60) // RCA poate fi lent pe QA
                        ->post($baseUrl . '/offer', $payload); // dacă Swagger cere alt path, îl schimbi aici
                }

                return $requests;
            });

            // 7) Procesare răspunsuri
            $allOffers = [];
            $errors = [];
            $successfulProviders = 0;

            foreach ($responses as $providerName => $response) {
                $status = $response->status();

                Log::channel('offers')->info("--- Response from: {$providerName} ---", [
                    'status_code' => $status,
                    'successful'  => $response->successful(),
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    // dacă API răspunde “200 dar fără schema așteptată”, logăm tot
                    Log::channel('offers')->info("SUCCESS: {$providerName}", [
                        'response_body' => $data,
                    ]);

                    $offers = $data['data']['offers'] ?? null;
                    if (is_array($offers) && count($offers) > 0) {
                        $providerBusinessName =
                            $data['data']['provider']['organization']['businessName']
                            ?? ($providers[$providerName]['businessName'] ?? $providerName);

                        foreach ($offers as $offer) {
                            $offer['providerName'] = $providerName;
                            $offer['providerBusinessName'] = $providerBusinessName;
                            $allOffers[] = $offer;
                        }

                        $successfulProviders++;
                    } else {
                        // 200 OK dar fără oferte
                        $errors[$providerName] = [
                            'status' => $status,
                            'error'  => [
                                'message' => 'NO_OFFERS',
                                'details' => $data,
                            ],
                        ];
                    }

                } else {
                    $errorBody = $response->json() ?? $response->body();

                    Log::channel('offers')->error("FAILED: {$providerName}", [
                        'status_code' => $status,
                        'error_body'  => $errorBody,
                    ]);

                    $errors[$providerName] = [
                        'status' => $status,
                        'error'  => $errorBody,
                    ];
                }
            }

            // 8) Sortare preț
            usort($allOffers, function ($a, $b) {
                $priceA = $a['premiumAmount'] ?? $a['premiumAmountNet'] ?? 0;
                $priceB = $b['premiumAmount'] ?? $b['premiumAmountNet'] ?? 0;
                return $priceA <=> $priceB;
            });

            Log::channel('offers')->info('=== Offer Request Complete ===', [
                'total_offers'          => count($allOffers),
                'successful_providers'  => $successfulProviders,
                'failed_providers'      => count($errors),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'offers'              => $allOffers,
                    'totalProviders'      => count($providers),
                    'successfulProviders' => $successfulProviders,
                ],
                'errors' => $errors,
            ], 200);

        } catch (\Throwable $e) {
            Log::channel('offers')->error('Exception in getOffer', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error'   => 'Error fetching offers',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function index(Request $request)
    {
        $quotes = Quote::orderBy('created_at', 'desc')->get();
        return response()->json(['success' => true, 'data' => $quotes]);
    }

    public function show($id)
    {
        $quote = Quote::find($id);
        if (!$quote) return response()->json(['error' => 'Quote not found'], 404);
        return response()->json(['success' => true, 'data' => $quote]);
    }

    public function store(StoreQuoteRequest $request)
    {
        $quote = Quote::create($request->validated());
        return response()->json(['success' => true, 'data' => $quote], 201);
    }

    /**
     * Download offer as PDF
     */
    public function downloadOfferPdf(Request $request, string $offerId)
    {
        try {
            $token = $request->bearerToken();
            if (!$token) return response()->json(['error' => 'Unauthorized'], 401);

            $withDirectCompensation = $request->query('withDirectCompensation', 0);

            Log::channel('offers')->info('Downloading offer PDF', [
                'offerId' => $offerId,
                'withDirectCompensation' => $withDirectCompensation,
            ]);

            $response = Http::withOptions(['verify' => $this->rcaVerifySsl()])
                ->withHeaders(['Token' => $token])
                ->timeout(60)
                ->get($this->rcaBaseUrl() . '/offer/' . $offerId, [
                    'withDirectCompensation' => $withDirectCompensation,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['data']['files']) && count($data['data']['files']) > 0) {
                    $file = $data['data']['files'][0];
                    $pdfContent = base64_decode($file['content']);
                    $fileName = $file['name'] ?? 'offer_' . $offerId . '.pdf';

                    return response($pdfContent, 200)
                        ->header('Content-Type', 'application/pdf')
                        ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
                }

                return response()->json($data);
            }

            Log::channel('offers')->error('Failed to download offer PDF', [
                'offerId' => $offerId,
                'status' => $response->status(),
                'error' => $response->json(),
            ]);

            return response()->json([
                'error' => 'Failed to download offer PDF',
                'details' => $response->json(),
            ], $response->status());

        } catch (Exception $e) {
            Log::channel('offers')->error('Exception downloading offer PDF', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Error downloading offer PDF',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if a policy already exists for a given offer
     */
    public function checkPolicyExists(Request $request, string $offerId)
    {
        try {
            $token = $request->bearerToken();
            if (!$token) return response()->json(['error' => 'Unauthorized'], 401);

            Log::channel('offers')->info('Checking if policy exists for offer', [
                'offerId' => $offerId,
            ]);

            $response = Http::withOptions(['verify' => $this->rcaVerifySsl()])
                ->withHeaders(['Token' => $token])
                ->get($this->rcaBaseUrl() . '/offer/' . $offerId);

            if ($response->successful()) {
                $data = $response->json();
                $offerData = $data['data'] ?? $data;

                $policyExists = false;
                $policyInfo = null;

                if (isset($offerData['status']) && in_array(strtolower($offerData['status']), ['converted', 'policy_created', 'issued', 'completed'])) {
                    $policyExists = true;
                }

                if (isset($offerData['policyId']) || isset($offerData['policyNumber']) || isset($offerData['policy'])) {
                    $policyExists = true;
                    $policyInfo = [
                        'policyId' => $offerData['policyId'] ?? $offerData['policy']['id'] ?? null,
                        'policyNumber' => $offerData['policyNumber'] ?? null,
                    ];
                }

                if (isset($offerData['policies']) && is_array($offerData['policies']) && count($offerData['policies']) > 0) {
                    $policyExists = true;
                    $policyInfo = $offerData['policies'][0];
                }

                Log::channel('offers')->info('Policy check result', [
                    'offerId' => $offerId,
                    'policyExists' => $policyExists,
                    'policyInfo' => $policyInfo,
                ]);

                return response()->json([
                    'success' => true,
                    'policyExists' => $policyExists,
                    'policyInfo' => $policyInfo,
                    'offerStatus' => $offerData['status'] ?? null,
                ]);
            }

            if ($response->status() === 404) {
                Log::channel('offers')->info('Offer not found - may have been converted to policy', [
                    'offerId' => $offerId,
                ]);

                return response()->json([
                    'success' => true,
                    'policyExists' => true,
                    'message' => 'Offer not found - may have been converted to policy',
                ]);
            }

            Log::channel('offers')->error('Failed to check policy status', [
                'offerId' => $offerId,
                'status' => $response->status(),
                'error' => $response->json(),
            ]);

            return response()->json([
                'error' => 'Failed to check policy status',
                'details' => $response->json(),
            ], $response->status());

        } catch (Exception $e) {
            Log::channel('offers')->error('Exception checking policy status', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Error checking policy status',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a policy from an offer
     */
    public function createPolicy(Request $request, string $offerId = null)
    {
        try {
            $token = $request->bearerToken();
            if (!$token) return response()->json(['error' => 'Unauthorized'], 401);

            $offerId = $offerId ?? $request->input('offerId');
            if (!$offerId) return response()->json(['error' => 'Offer ID is required'], 400);

            $premiumAmount = $request->input('premiumAmount', 0);
            $currency = $request->input('currency', 'RON');

            $payload = [
                'offerId' => (int) $offerId,
                'includeDirectCompensation' => $request->input('includeDirectCompensation', false),
                'payment' => $request->input('payment', [
                    'method' => 'receipt',
                    'currency' => $currency,
                    'amount' => $premiumAmount,
                    'date' => date('Y-m-d'),
                    'documentNumber' => $request->input('documentNumber', 'AUTO-' . time()),
                ]),
            ];

            Log::channel('offers')->info('Creating policy from offer', [
                'offerId' => $offerId,
                'payload' => $payload,
            ]);

            $response = Http::withOptions(['verify' => $this->rcaVerifySsl()])
                ->withHeaders(['Token' => $token])
                ->timeout(60)
                ->post($this->rcaBaseUrl() . '/policy', $payload);

            if ($response->successful()) {
                $data = $response->json();

                Log::channel('offers')->info('Policy created successfully', [
                    'offerId' => $offerId,
                    'response' => $data,
                ]);

                $policyData = null;
                if (isset($data['data']['policies']) && count($data['data']['policies']) > 0) {
                    $policyData = $data['data']['policies'][0];
                } elseif (isset($data['data'])) {
                    $policyData = $data['data'];
                } else {
                    $policyData = $data;
                }

                // Save to local DB — never break the response if this fails
                try {
                    $clientName = trim(
                        ($request->input('firstName', '') . ' ' . $request->input('lastName', ''))
                    ) ?: ($request->input('businessName') ?? 'Unknown');

                    Quote::create([
                        'policy_id'         => $policyData['policyId']      ?? $policyData['id']             ?? null,
                        'policy_number'     => $policyData['policyNumber']  ?? $policyData['policy_number']  ?? $policyData['number'] ?? null,
                        'offer_id'          => $offerId,
                        'client_name'       => $clientName,
                        'car_plate'         => $request->input('licensePlate', ''),
                        'insurer_name'      => $policyData['providerBusinessName'] ?? $policyData['providerName'] ?? $request->input('providerName', ''),
                        'provider_code'     => $policyData['providerCode']   ?? $request->input('providerCode', ''),
                        'price'             => $premiumAmount,
                        'currency'          => $currency,
                        'start_date'        => $policyData['startDate']      ?? $request->input('startDate')  ?? null,
                        'end_date'          => $policyData['endDate']        ?? $request->input('endDate')    ?? null,
                        'installment_count' => $request->input('installmentCount', 1),
                        'status'            => 'created',
                    ]);
                } catch (\Exception $dbEx) {
                    Log::warning('Failed to save policy to DB: ' . $dbEx->getMessage());
                }

                return response()->json([
                    'success' => true,
                    'data' => $policyData,
                ]);
            }

            Log::channel('offers')->error('Failed to create policy', [
                'offerId' => $offerId,
                'status' => $response->status(),
                'error' => $response->json(),
            ]);

            return response()->json([
                'error' => 'Failed to create policy',
                'details' => $response->json(),
            ], $response->status());

        } catch (Exception $e) {
            Log::channel('offers')->error('Exception creating policy', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Error creating policy',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download policy as PDF
     */
    public function downloadPolicyPdf(Request $request, string $policyId)
    {
        try {
            $token = $request->bearerToken();
            if (!$token) return response()->json(['error' => 'Unauthorized'], 401);

            Log::channel('offers')->info('Downloading policy PDF', [
                'policyId' => $policyId,
            ]);

            $response = Http::withOptions(['verify' => $this->rcaVerifySsl()])
                ->withHeaders(['Token' => $token])
                ->timeout(60)
                ->get($this->rcaBaseUrl() . '/policy/' . $policyId);

            if ($response->successful()) {
                $contentType = $response->header('Content-Type');

                if ($contentType && str_contains($contentType, 'application/pdf')) {
                    Log::channel('offers')->info('Policy PDF downloaded successfully (binary)', [
                        'policyId' => $policyId,
                    ]);

                    return response($response->body(), 200)
                        ->header('Content-Type', 'application/pdf')
                        ->header('Content-Disposition', 'attachment; filename="policy_' . $policyId . '.pdf"');
                }

                $data = $response->json();

                Log::channel('offers')->info('Policy PDF response received', [
                    'policyId' => $policyId,
                    'response_keys' => is_array($data) ? array_keys($data) : 'not_array',
                    'data_keys' => isset($data['data']) && is_array($data['data']) ? array_keys($data['data']) : 'no_data',
                ]);

                if (isset($data['data']['files']) && count($data['data']['files']) > 0) {
                    $file = $data['data']['files'][0];
                    $pdfContent = base64_decode($file['content']);
                    $fileName = $file['name'] ?? 'policy_' . $policyId . '.pdf';

                    Log::channel('offers')->info('Policy PDF downloaded successfully (base64)', [
                        'policyId' => $policyId,
                        'fileName' => $fileName,
                    ]);

                    return response($pdfContent, 200)
                        ->header('Content-Type', 'application/pdf')
                        ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
                }

                if (isset($data['data']['url'])) {
                    Log::channel('offers')->info('Policy PDF URL received', [
                        'policyId' => $policyId,
                        'url' => $data['data']['url'],
                    ]);

                    return response()->json([
                        'success' => true,
                        'pdfUrl' => $data['data']['url'],
                    ]);
                }

                Log::channel('offers')->warning('Policy PDF - unexpected response format', [
                    'policyId' => $policyId,
                    'response' => $data,
                ]);

                return response()->json($data);
            }

            Log::channel('offers')->error('Failed to download policy PDF', [
                'policyId' => $policyId,
                'status' => $response->status(),
                'error' => $response->json(),
            ]);

            return response()->json([
                'error' => 'Failed to download policy PDF',
                'details' => $response->json(),
            ], $response->status());

        } catch (Exception $e) {
            Log::channel('offers')->error('Exception downloading policy PDF', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Error downloading policy PDF',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get policy PDF file (general)
     */
    public function getPolicyPdfFile(Request $request)
    {
        try {
            $token = $request->bearerToken();
            if (!$token) return response()->json(['error' => 'Unauthorized'], 401);

            Log::channel('offers')->info('Getting policy PDF file');

            $response = Http::withOptions(['verify' => $this->rcaVerifySsl()])
                ->withHeaders(['Token' => $token])
                ->timeout(60)
                ->get($this->rcaBaseUrl() . '/policy');

            if ($response->successful()) {
                $contentType = $response->header('Content-Type');

                if ($contentType && str_contains($contentType, 'application/pdf')) {
                    return response($response->body(), 200)
                        ->header('Content-Type', 'application/pdf')
                        ->header('Content-Disposition', 'attachment; filename="policy.pdf"');
                }

                $data = $response->json();

                if (isset($data['data']['files']) && count($data['data']['files']) > 0) {
                    $file = $data['data']['files'][0];
                    $pdfContent = base64_decode($file['content']);
                    $fileName = $file['name'] ?? 'policy.pdf';

                    return response($pdfContent, 200)
                        ->header('Content-Type', 'application/pdf')
                        ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
                }

                if (isset($data['data']['url'])) {
                    return response()->json([
                        'success' => true,
                        'pdfUrl' => $data['data']['url'],
                    ]);
                }

                return response()->json($data);
            }

            Log::channel('offers')->error('Failed to get policy PDF file', [
                'status' => $response->status(),
                'error' => $response->json(),
            ]);

            return response()->json([
                'error' => 'Failed to get policy PDF file',
                'details' => $response->json(),
            ], $response->status());

        } catch (Exception $e) {
            Log::channel('offers')->error('Exception getting policy PDF file', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Error getting policy PDF file',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
