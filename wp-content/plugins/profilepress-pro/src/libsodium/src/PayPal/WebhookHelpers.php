<?php

namespace ProfilePress\Libsodium\PayPal;

use ProfilePress\Libsodium\PayPal\WebhookHandlers\BillingSubscriptionActivated;
use ProfilePress\Libsodium\PayPal\WebhookHandlers\BillingSubscriptionCancelled;
use ProfilePress\Libsodium\PayPal\WebhookHandlers\BillingSubscriptionExpired;
use ProfilePress\Libsodium\PayPal\WebhookHandlers\PaymentCaptureCompleted;
use ProfilePress\Libsodium\PayPal\WebhookHandlers\PaymentCaptureDenied;
use ProfilePress\Libsodium\PayPal\WebhookHandlers\PaymentCaptureRefunded;
use ProfilePress\Libsodium\PayPal\WebhookHandlers\PaymentSaleCompleted;
use ProfilePress\Libsodium\PayPal\WebhookHandlers\PaymentSaleRefunded;

class WebhookHelpers
{
    public static function valid_events()
    {
        return apply_filters('ppress_paypal_webhooks_whitelist', [
            'PAYMENT.CAPTURE.COMPLETED'      => new PaymentCaptureCompleted(),
            'PAYMENT.CAPTURE.REFUNDED'       => new PaymentCaptureRefunded(),
            'PAYMENT.CAPTURE.DENIED'         => new PaymentCaptureDenied(),
            'BILLING.SUBSCRIPTION.ACTIVATED' => new BillingSubscriptionActivated(),
            'BILLING.SUBSCRIPTION.CANCELLED' => new BillingSubscriptionCancelled(),
            'BILLING.SUBSCRIPTION.EXPIRED'   => new BillingSubscriptionExpired(),
            'PAYMENT.SALE.COMPLETED'         => new PaymentSaleCompleted(),
            'PAYMENT.SALE.REFUNDED'          => new PaymentSaleRefunded()
        ]);
    }

    /**
     * @throws \Exception
     */
    public static function validate_webhook($apiClient, $event, $webhook_id)
    {
        $headers = array_change_key_case(getallheaders(), CASE_UPPER);

        $header_map = array(
            'PAYPAL-AUTH-ALGO'         => 'auth_algo',
            'PAYPAL-CERT-URL'          => 'cert_url',
            'PAYPAL-TRANSMISSION-ID'   => 'transmission_id',
            'PAYPAL-TRANSMISSION-SIG'  => 'transmission_sig',
            'PAYPAL-TRANSMISSION-TIME' => 'transmission_time'
        );

        foreach (array_keys($header_map) as $required_key) {
            if ( ! array_key_exists($required_key, $headers)) {
                throw new \InvalidArgumentException(
                    sprintf('Missing PayPal header %s', $required_key)
                );
            }
        }

        $body = [
            'webhook_id'    => $webhook_id,
            'webhook_event' => $event
        ];

        foreach ($header_map as $header_key => $body_key) {
            $body[$body_key] = $headers[$header_key];
        }

        $response = $apiClient->make_request('v1/notifications/verify-webhook-signature', $body);

        if ( ! ppress_is_http_code_success($apiClient->last_response_code)) {
            throw new \Exception(
                sprintf('Invalid response code: %d. Response: %s', $apiClient->last_response_code, json_encode($response))
            );
        }

        if (empty($response->verification_status) || 'SUCCESS' !== strtoupper($response->verification_status)) {
            throw new \Exception(
                sprintf('Verification failure. Response: %s', json_encode($response))
            );
        }

        return true;
    }
}