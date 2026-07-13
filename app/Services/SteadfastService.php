<?php

namespace App\Services;

use App\Models\DeliveryPartner;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin wrapper around Steadfast Courier's order API
 * (https://portal.steadfast.com.bd/api/v1). Credentials live per-partner on
 * DeliveryPartner (api_key/secret_key) rather than in config — a business
 * could plausibly run more than one courier account. Status updates come
 * back separately via the webhook — see SteadfastWebhookController.
 */
class SteadfastService
{
    private const BASE_URL = 'https://portal.steadfast.com.bd/api/v1';

    /**
     * Books a consignment and returns Steadfast's own identifiers —
     * ['external_id' => consignment_id, 'tracking_no' => tracking_code] —
     * for the caller to store on the CourierConsignment record.
     */
    public function createOrder(DeliveryPartner $partner, array $data): array
    {
        $response = Http::withHeaders([
            'Api-Key' => $partner->api_key,
            'Secret-Key' => $partner->secret_key,
        ])->post(self::BASE_URL.'/create_order', [
            'invoice' => $data['invoice'],
            'recipient_name' => $data['recipient_name'],
            'recipient_phone' => $data['recipient_phone'],
            'recipient_address' => $data['recipient_address'],
            'cod_amount' => $data['cod_amount'],
            'note' => $data['note'] ?? null,
        ])->json();

        $consignment = $response['consignment'] ?? null;

        if (($response['status'] ?? null) !== 200 || ! $consignment) {
            throw new RuntimeException('Steadfast order booking failed: '.($response['message'] ?? 'unknown error'));
        }

        return [
            'external_id' => (string) $consignment['consignment_id'],
            'tracking_no' => (string) $consignment['tracking_code'],
        ];
    }
}
