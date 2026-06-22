<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="max-w-2xl; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 8px;">
        <h1 style="color: #333333;">Thank you for your order!</h1>
        <p>Hi {{ $order->shipping_address['first_name'] ?? 'Customer' }},</p>
        <p>We've received your order <strong>#{{ $order->id }}</strong> and are currently processing it.</p>
        
        <h3 style="margin-top: 30px; border-bottom: 1px solid #eeeeee; padding-bottom: 10px;">Order Summary</h3>
        <ul style="list-style-type: none; padding: 0;">
            @foreach($order->items as $item)
                <li style="padding: 10px 0; border-bottom: 1px solid #f9f9f9;">
                    {{ $item->quantity }}x {{ $item->name }} - &euro; {{ number_format(($item->price / 100) * $item->quantity, 2, ',', '.') }}
                </li>
            @endforeach
        </ul>
        
        <p style="text-align: right; font-weight: bold; font-size: 18px;">
            Total: &euro; {{ number_format($order->total_amount / 100, 2, ',', '.') }}
        </p>

        <p style="margin-top: 40px; color: #777777; font-size: 14px;">
            If you have any questions, feel free to reply to this email.
        </p>
    </div>
</body>
</html>
