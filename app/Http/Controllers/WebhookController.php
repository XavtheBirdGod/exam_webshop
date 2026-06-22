<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentLog;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret') ?: env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                
                $orderId = $session->client_reference_id;
                $order = Order::with('items.productVariant')->find($orderId);

                if ($order && $order->status === 'pending') {
                    // 1. Create Payment Log
                    PaymentLog::create([
                        'order_id' => $order->id,
                        'provider' => 'stripe',
                        'transaction_id' => $session->id,
                        'amount' => $session->amount_total,
                        'status' => 'succeeded',
                        'provider_response' => $session->toArray(),
                    ]);

                    // 2. Mark order as paid
                    $order->update(['status' => 'paid']);

                    // 3. Deduct Stock via StockMovements
                    foreach ($order->items as $item) {
                        if ($item->productVariant) {
                            StockMovement::create([
                                'product_variant_id' => $item->productVariant->id,
                                'type' => 'sale',
                                'quantity' => -$item->quantity,
                                'description' => 'Order #' . $order->id,
                            ]);

                            // Update the variant's actual stock quantities
                            $item->productVariant->decrement('stock_on_hand', $item->quantity);
                            $item->productVariant->decrement('stock_available', $item->quantity);
                        }
                    }

                    // 4. Queue Email
                    \Illuminate\Support\Facades\Mail::to($order->email)->queue(new \App\Mail\OrderConfirmation($order));
                    Log::info('Order #' . $order->id . ' processed successfully.');
                }
                break;
            default:
                Log::info('Received unhandled Stripe webhook event: ' . $event->type);
        }

        return response()->json(['status' => 'success'], 200);
    }
}
