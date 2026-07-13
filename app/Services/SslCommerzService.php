<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin wrapper around SSLCommerz's Session (v4) and Validation APIs —
 * see https://developer.sslcommerz.com. Sandbox vs live is selected by
 * config('services.sslcommerz.sandbox'); credentials come from the same
 * config array.
 */
class SslCommerzService
{
    public function initiateSession(Sale $sale): string
    {
        $response = Http::asForm()->post($this->baseUrl().'/gwprocess/v4/api.php', [
            'store_id' => config('services.sslcommerz.store_id'),
            'store_passwd' => config('services.sslcommerz.store_password'),
            'total_amount' => (float) $sale->total_amount,
            'currency' => 'BDT',
            'tran_id' => $sale->sale_no,
            'success_url' => route('payment.sslcommerz.success', $sale),
            'fail_url' => route('payment.sslcommerz.fail', $sale),
            'cancel_url' => route('payment.sslcommerz.cancel', $sale),
            'cus_name' => $sale->shipping_name,
            'cus_email' => 'guest@'.(parse_url(config('app.url'), PHP_URL_HOST) ?: 'example.com'),
            'cus_add1' => $sale->shipping_address,
            'cus_city' => 'Dhaka',
            'cus_country' => 'Bangladesh',
            'cus_phone' => $sale->shipping_phone,
            // 'NO' skips SSLCommerz's own ship_* required fields (city,
            // postcode, etc.) — courier delivery is arranged separately by
            // us, not through the gateway.
            'shipping_method' => 'NO',
            'product_name' => 'Online Order '.$sale->sale_no,
            'product_category' => 'General',
            'product_profile' => 'general',
            'num_of_item' => $sale->items()->count(),
        ])->json();

        if (($response['status'] ?? null) !== 'SUCCESS' || empty($response['GatewayPageURL'])) {
            throw new RuntimeException('SSLCommerz session init failed: '.($response['failedreason'] ?? 'unknown error'));
        }

        return $response['GatewayPageURL'];
    }

    /**
     * Server-side confirmation of a browser redirect's val_id — never trust
     * the redirect's own status/amount fields, since those are attacker-
     * controlled form data.
     */
    public function validateTransaction(string $valId): ?array
    {
        $response = Http::get($this->baseUrl().'/validator/api/validationserverAPI.php', [
            'val_id' => $valId,
            'store_id' => config('services.sslcommerz.store_id'),
            'store_passwd' => config('services.sslcommerz.store_password'),
            'format' => 'json',
        ])->json();

        if (! in_array($response['status'] ?? null, ['VALID', 'VALIDATED'], true)) {
            return null;
        }

        return $response;
    }

    private function baseUrl(): string
    {
        return config('services.sslcommerz.sandbox')
            ? 'https://sandbox.sslcommerz.com'
            : 'https://securepay.sslcommerz.com';
    }
}
