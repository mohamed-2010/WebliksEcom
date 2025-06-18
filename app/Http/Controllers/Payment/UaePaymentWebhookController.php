<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CombinedOrder;
use App\Models\Order;
use App\Utility\NotificationUtility;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class UaePaymentWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        if (!$this->verifySignature($request)) {
            Log::error('Invalid webhook signature', ['payload' => $request->all()]);
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $eventType = $request->input('type');
        $details = $request->input('details.status');

        try {
            switch ($eventType) {
                case 'StatusChange':
                    return $this->handleStatusChange($details);

                case 'PaymentSuccess':
                    return $this->handlePaymentSuccess($request->all());

                case 'PaymentFailure':
                    return $this->handlePaymentFailure($request->all());

                default:
                    Log::info('Unhandled webhook event', ['type' => $eventType]);
                    return response()->json(['status' => 'unhandled_event']);
            }
        } catch (\Exception $e) {
            Log::error('Webhook processing error', [
                'error' => $e->getMessage(),
                'payload' => $request->all()
            ]);
            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    protected function verifySignature($request)
    {
        $secret = config('services.uae_gateway.webhook_secret');
        $signature = $request->header('X-Signature');
        $payload = $request->getContent();

        $computedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($signature, $computedSignature);
    }

    protected function handleStatusChange($statusDetails)
    {
        $entityType = isset($statusDetails['account']) ? 'account' : 'card';
        $entityId = $statusDetails[$entityType]['id']['value'];
        $newStatus = $statusDetails['newValue'];

        Log::info("Status changed for {$entityType}: {$entityId} to {$newStatus}");

        return response()->json(['status' => 'processed']);
    }

    protected function handlePaymentSuccess($paymentData)
    {
        $orderId = $paymentData['order_id'] ?? null;

        if (!$orderId) {
            Log::error('Payment success webhook missing order_id', $paymentData);
            return response()->json(['error' => 'Order ID missing'], 400);
        }

        $combinedOrder = CombinedOrder::findOrFail($orderId);

        foreach ($combinedOrder->orders as $order) {
            $order->payment_status = 'paid';
            $order->payment_details = json_encode($paymentData);
            $order->save();

            NotificationUtility::sendNotification($order, 'success');
        }

        return response()->json(['status' => 'success']);
    }

    protected function handlePaymentFailure($paymentData)
    {
        $orderId = $paymentData['order_id'] ?? null;

        if (!$orderId) {
            Log::error('Payment failure webhook missing order_id', $paymentData);
            return response()->json(['error' => 'Order ID missing'], 400);
        }

        $combinedOrder = CombinedOrder::findOrFail($orderId);

        foreach ($combinedOrder->orders as $order) {
            $order->payment_status = 'unpaid';
            $order->payment_details = json_encode($paymentData);
            $order->save();

            NotificationUtility::sendNotification($order, 'failed');
        }

        return response()->json(['status' => 'processed']);
    }
}
