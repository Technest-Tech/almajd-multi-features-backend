<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Almajd Payments - {{ $billingType === 'auto' ? "{$month}/{$year}" : 'Custom Billing' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #e8f5e9 0%, #f3e5f5 50%, #e1f5fe 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Decorative shapes */
        .shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.3;
            animation: float 20s infinite ease-in-out;
        }
        
        .shape-1 {
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, #a8e6cf 0%, #dcedc8 100%);
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }
        
        .shape-2 {
            width: 250px;
            height: 250px;
            background: linear-gradient(135deg, #ffd3b6 0%, #ffaaa5 100%);
            bottom: -50px;
            right: -50px;
            animation-delay: 5s;
        }
        
        .shape-3 {
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, #b8e6b8 0%, #a8d8ea 100%);
            top: 50%;
            right: -80px;
            animation-delay: 10s;
        }
        
        .shape-4 {
            width: 180px;
            height: 180px;
            background: linear-gradient(135deg, #ffc3a0 0%, #ffafbd 100%);
            bottom: 20%;
            left: -60px;
            animation-delay: 15s;
        }
        
        .shape-5 {
            width: 150px;
            height: 150px;
            background: linear-gradient(135deg, #c5e1a5 0%, #e1bee7 100%);
            top: 20%;
            left: 10%;
            animation-delay: 2s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translate(0, 0) scale(1) rotate(0deg);
            }
            25% {
                transform: translate(30px, -30px) scale(1.1) rotate(5deg);
            }
            50% {
                transform: translate(-20px, 20px) scale(0.9) rotate(-5deg);
            }
            75% {
                transform: translate(20px, 30px) scale(1.05) rotate(3deg);
            }
        }
        
        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 0.2;
                transform: scale(1);
            }
            50% {
                opacity: 0.4;
                transform: scale(1.1);
            }
        }
        
        /* Islamic/Educational Vectors */
        .islamic-vector {
            position: absolute;
            opacity: 0.15;
            z-index: 0;
        }
        
        /* Crescent Moon */
        .crescent {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
            position: relative;
            animation: float 25s infinite ease-in-out;
        }
        
        .crescent::before {
            content: '';
            position: absolute;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #e8f5e9 0%, #f3e5f5 100%);
            border-radius: 50%;
            top: 10px;
            left: 20px;
        }
        
        /* Star (8-pointed Islamic star) */
        .star {
            width: 60px;
            height: 60px;
            position: relative;
            animation: rotate 30s linear infinite;
            opacity: 0.2;
        }
        
        .star::before {
            content: '';
            position: absolute;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
            clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);
        }
        
        /* Book Icon */
        .book-icon {
            width: 80px;
            height: 60px;
            background: linear-gradient(135deg, #81c784 0%, #66bb6a 100%);
            border-radius: 5px 15px 5px 5px;
            position: relative;
            animation: float 20s infinite ease-in-out;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .book-icon::before {
            content: '';
            position: absolute;
            width: 60px;
            height: 45px;
            background: white;
            border-radius: 3px 12px 3px 3px;
            top: 7px;
            left: 10px;
        }
        
        .book-icon::after {
            content: '';
            position: absolute;
            width: 2px;
            height: 45px;
            background: #81c784;
            top: 7px;
            left: 40px;
        }
        
        /* Geometric Pattern (Islamic geometric design) */
        .geometric-pattern {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #64b5f6 0%, #42a5f5 100%);
            clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
            animation: rotate 40s linear infinite;
            opacity: 0.15;
        }
        
        /* Position vectors */
        .vector-1 {
            top: 10%;
            left: 5%;
        }
        
        .vector-2 {
            top: 60%;
            right: 8%;
        }
        
        .vector-3 {
            bottom: 15%;
            left: 15%;
        }
        
        .vector-4 {
            top: 30%;
            right: 12%;
        }
        
        .vector-5 {
            bottom: 25%;
            right: 5%;
        }
        
        .vector-6 {
            top: 5%;
            right: 20%;
        }
        
        .vector-7 {
            bottom: 10%;
            left: 8%;
        }
        
        .vector-8 {
            top: 40%;
            left: 3%;
        }
        
        .payment-container {
            max-width: 500px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
            position: relative;
            z-index: 1;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .header {
            background: linear-gradient(135deg, #81c784 0%, #64b5f6 100%);
            color: white;
            padding: 20px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -50px;
            right: -50px;
        }
        
        .header::after {
            content: '';
            position: absolute;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            bottom: -30px;
            left: -30px;
        }
        
        /* Header decorative Islamic elements */
        .header-decoration {
            position: absolute;
            opacity: 0.2;
        }
        
        .header-star {
            width: 30px;
            height: 30px;
            position: absolute;
            animation: pulse 3s ease-in-out infinite;
        }
        
        .header-star::before {
            content: '';
            position: absolute;
            width: 30px;
            height: 30px;
            background: white;
            clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);
        }
        
        .header-star-1 {
            top: 20px;
            right: 50px;
            animation-delay: 0s;
        }
        
        .header-star-2 {
            top: 40px;
            left: 60px;
            animation-delay: 1s;
        }
        
        .header-star-3 {
            bottom: 30px;
            right: 80px;
            animation-delay: 2s;
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 6px;
        }
        
        .header .greeting {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .content {
            padding: 20px;
        }
        
        .billing-card {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 15px;
            padding: 18px;
            margin-bottom: 18px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .billing-card h2 {
            color: #333;
            font-size: 20px;
            margin-bottom: 12px;
            font-weight: 600;
        }
        
        .billing-info {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: #666;
            font-size: 13px;
            font-weight: 500;
        }
        
        .info-value {
            color: #333;
            font-size: 15px;
            font-weight: 600;
        }
        
        .amount-highlight {
            background: linear-gradient(135deg, #81c784 0%, #64b5f6 100%);
            color: white;
            padding: 12px 16px;
            border-radius: 12px;
            text-align: center;
            margin-top: 12px;
        }
        
        .amount-highlight .label {
            font-size: 11px;
            opacity: 0.9;
            margin-bottom: 4px;
        }
        
        .amount-highlight .value {
            font-size: 28px;
            font-weight: 700;
        }
        
        .payment-methods {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .payment-method-card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 16px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .payment-method-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }
        
        .payment-method-card h3 {
            color: #333;
            font-size: 17px;
            margin-bottom: 6px;
            font-weight: 600;
        }
        
        .payment-method-card p {
            color: #666;
            font-size: 13px;
            margin-bottom: 10px;
        }
        
        .btn {
            width: 100%;
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            color: white;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #81c784 0%, #64b5f6 100%);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(129, 199, 132, 0.4);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #ffb74d 0%, #ff8a65 100%);
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 183, 77, 0.4);
        }
        
        .btn-blue {
            background: linear-gradient(135deg, #64b5f6 0%, #42a5f5 100%);
        }
        
        .btn-blue:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(100, 181, 246, 0.4);
        }
        
        .btn:disabled {
            background: #cccccc;
            cursor: not-allowed;
            transform: none;
        }
        
        #paypal-button-container {
            margin-top: 8px;
        }
        
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            
            .payment-container {
                border-radius: 15px;
            }
            
            .header {
                padding: 16px 20px;
            }
            
            .header h1 {
                font-size: 22px;
            }
            
            .content {
                padding: 16px;
            }
            
            .amount-highlight .value {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <!-- Decorative background shapes -->
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
    <div class="shape shape-4"></div>
    <div class="shape shape-5"></div>
    
    <!-- Islamic and Educational Vectors -->
    <div class="islamic-vector vector-1">
        <div class="crescent"></div>
    </div>
    <div class="islamic-vector vector-2">
        <div class="star"></div>
    </div>
    <div class="islamic-vector vector-3">
        <div class="book-icon"></div>
    </div>
    <div class="islamic-vector vector-4">
        <div class="geometric-pattern"></div>
    </div>
    <div class="islamic-vector vector-5">
        <div class="crescent"></div>
    </div>
    <div class="islamic-vector vector-6">
        <div class="star"></div>
    </div>
    <div class="islamic-vector vector-7">
        <div class="book-icon"></div>
    </div>
    <div class="islamic-vector vector-8">
        <div class="geometric-pattern"></div>
    </div>
    
<div class="payment-container">
    <div class="header">
        <div class="header-decoration header-star header-star-1"></div>
        <div class="header-decoration header-star header-star-2"></div>
        <div class="header-decoration header-star header-star-3"></div>
        <h1>💳 Almajd Payments</h1>
        <div class="greeting">Hello, {{ $user->name ?? 'Customer' }}</div>
    </div>
    
    <div class="content">
        <div class="billing-card">
            @if($billingType === 'auto')
                <h2>📅 Billing for {{ $month }}/{{ $year }}</h2>
            @else
                <h2>📋 Custom Billing</h2>
            @endif
            
            <div class="billing-info">
                @if($billingType === 'auto')
                    <div class="info-row">
                        <span class="info-label">⏱️ Total Hours</span>
                        <span class="info-value">{{ $billing->total_hours }} hours</span>
                    </div>
                @endif
                
                @if($billing->message)
                    <div class="info-row">
                        <span class="info-label">📝 Message</span>
                        <span class="info-value">{{ $billing->message }}</span>
                    </div>
                @endif
            </div>
            
            <div class="amount-highlight">
                <div class="label">Total Amount</div>
                <div class="value">{{ $amount }} {{ $billing->currency instanceof \App\Enums\Currency ? $billing->currency->symbol() : $billing->currency }}</div>
            </div>
        </div>

        <div class="payment-methods">
            <!-- XPay (Credit Card) - Always available -->
            <div class="payment-method-card">
                <h3>💳 Credit Card</h3>
                <p>Pay securely with your credit or debit card</p>
                <a href="{{ url('/payment/' . $token . '/xpay/form?billing_type=' . $billingType) }}" class="btn btn-primary">Pay with Credit Card</a>
            </div>

            <!-- PayPal - If enabled -->
            @if($paypalEnabled == '1')
            <div class="payment-method-card">
                <h3>🅿️ PayPal</h3>
                <p>Pay with your PayPal account</p>
                <div id="paypal-button-container"></div>
            </div>
            @endif

            <!-- AnubPay - If enabled -->
            @if($anubpayEnabled == '1')
            <div class="payment-method-card">
                <h3>💎 Credit Card & PayPal</h3>
                <p>Pay with credit card or PayPal through AnubPay</p>
                <button id="anubpay-button" class="btn btn-blue">Credit And Paypal</button>
            </div>
            @endif
        </div>
    </div>
</div>

@if($paypalEnabled == '1')
<script src="https://www.paypal.com/sdk/js?client-id={{ config('payments.paypal.client_id') }}&components=buttons&currency={{ $billing->currency instanceof \App\Enums\Currency ? $billing->currency->value : $billing->currency }}&disable-funding=credit,card"></script>
<script>
    paypal.Buttons({
        createOrder: function(data, actions) {
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: '{{ $amount }}',
                    }
                }]
            });
        },
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                window.location.href = '{{ url("/payment/" . $token . "/paypal/success") }}';
            });
        },
        onError: function(err) {
            console.error('PayPal error:', err);
            alert('Payment failed. Please try again.');
        },
        style: {
            layout: 'vertical',
            color: 'blue',
            shape: 'rect',
            label: 'paypal'
        }
    }).render('#paypal-button-container');
</script>
@endif

@if($anubpayEnabled == '1')
<script>
document.getElementById('anubpay-button').addEventListener('click', function() {
    this.disabled = true;
    this.textContent = 'Processing...';
    
    fetch('{{ url("/anubpay/create-payment") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            user_id: {{ $user->id ?? 0 }},
            amount: {{ $amount }},
            currency: '{{ $billing->currency instanceof \App\Enums\Currency ? $billing->currency->value : $billing->currency }}',
            month: '{{ $month }}',
            billing_id: {{ $billingId }},
            billing_type: '{{ $billingType }}',
            description: '{{ $billingType === "auto" ? "Payment for {$month}/{$year} billing" : "Custom billing payment" }} - {{ $user->name ?? "Customer" }}'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect_url;
        } else {
            alert('Payment creation failed: ' + (data.error || 'Unknown error'));
            this.disabled = false;
            this.textContent = 'Credit And Paypal';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Payment creation failed. Please try again.');
        this.disabled = false;
        this.textContent = 'Credit And Paypal';
    });
});
</script>
@endif
</body>
</html>
