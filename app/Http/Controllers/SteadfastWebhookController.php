<?php

namespace App\Http\Controllers;

use App\Models\CourierConsignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Receives Steadfast's delivery-status push notifications — see
 * CourierConsignmentController's docblock for the manual-tracking
 * alternative this replaces for API-booked consignments. Configure this
 * route's URL in Steadfast's merchant panel with the same bearer token as
 * services.steadfast.webhook_token.
 *
 * Steadfast's exact webhook payload/status vocabulary wasn't available to
 * verify against a live account at build time — statusFor() below maps by
 * keyword rather than an exact enum, and anything it can't confidently map
 * is logged instead of guessed at. Adjust both once real payloads are seen.
 */
class SteadfastWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $token = str($request->header('Authorization', ''))->after('Bearer ')->trim()->toString();

        if (! $token || ! hash_equals((string) config('services.steadfast.webhook_token'), $token)) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        $externalId = $request->input('consignment_id');
        $trackingNo = $request->input('tracking_code') ?? $request->input('tracking_no');
        $rawStatus = (string) $request->input('status', $request->input('delivery_status', ''));

        $consignment = CourierConsignment::query()
            ->when($externalId, fn ($q) => $q->orWhere('external_id', $externalId))
            ->when($trackingNo, fn ($q) => $q->orWhere('tracking_no', $trackingNo))
            ->first();

        if (! $consignment) {
            Log::warning('Steadfast webhook: no matching consignment', $request->all());

            return response()->json(['status' => 'ignored'], 200);
        }

        $status = $this->statusFor($rawStatus);

        if (! $status) {
            Log::warning('Steadfast webhook: unrecognized status', ['raw_status' => $rawStatus, 'consignment_id' => $consignment->id]);

            return response()->json(['status' => 'ignored'], 200);
        }

        if (! $consignment->cod_settled_at) {
            $consignment->update(['status' => $status]);
        }

        return response()->json(['status' => 'ok']);
    }

    private function statusFor(string $raw): ?string
    {
        $raw = Str::lower($raw);

        return match (true) {
            str_contains($raw, 'deliver') && ! str_contains($raw, 'fail') => 'delivered',
            str_contains($raw, 'cancel') || str_contains($raw, 'return') => 'returned',
            str_contains($raw, 'lost') => 'lost',
            str_contains($raw, 'transit') || str_contains($raw, 'hub') || str_contains($raw, 'progress') => 'in_transit',
            str_contains($raw, 'pick') => 'picked_up',
            default => null,
        };
    }
}
