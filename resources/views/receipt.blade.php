<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Parking Receipt - {{ $plate }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            padding: 20px;
        }
        .receipt-card {
            background: white;
            width: 300px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-top: 5px solid #1e40af; /* Blue theme */
        }
        .header { text-align: center; border-bottom: 1px dashed #ccc; padding-bottom: 10px; }
        .details { margin: 20px 0; font-size: 14px; line-height: 1.6; }
        .total { font-size: 20px; font-weight: bold; text-align: center; margin: 15px 0; border-top: 1px solid #eee; padding-top: 10px; }
        .footer { font-size: 10px; text-align: center; color: #777; margin-top: 20px; }
        @media print {
            body { background: white; padding: 0; }
            .receipt-card { box-shadow: none; border: none; width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="receipt-card">
        <div class="header">
            <h2 style="margin:0;">SMARTPARK</h2>
            <p style="font-size:10px; margin:5px 0;">Nairobi, Kenya</p>
        </div>

        <div class="details">
            <p><strong>REF:</strong> {{ $ref }}</p>
            <p><strong>PLATE:</strong> <span style="font-size:18px;">{{ $plate }}</span></p>
            <p><strong>IN:</strong> {{ $arrival }}</p>
            <p><strong>OUT:</strong> {{ $exit }}</p>
        </div>

        <div class="total">
            TOTAL: KES {{ number_format($amount, 2) }}
        </div>

        <div class="header" style="border-top: 1px dashed #ccc; border-bottom:none; margin-top:10px;">
            <p style="font-size:11px;">PAID VIA M-PESA / CASH</p>
        </div>

        <div class="footer">
            <p>Thank you for choosing SmartPark.</p>
            <p>You have 15 minutes to clear the gate.</p>
            <button class="no-print" onclick="window.print()" style="margin-top:10px; cursor:pointer; padding:5px 10px;">Print Receipt</button>
        </div>
    </div>
</body>
</html>
