<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Report</title>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            direction: rtl;
            text-align: right;
            color: #333;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #2c3e50;
        }
        .header h1 {
            font-size: 32px;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .student-info {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border-right: 4px solid #3498db;
        }
        .student-info h2 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 20px;
        }
        .info-row {
            margin: 8px 0;
            font-size: 14px;
        }
        .info-label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            width: 120px;
        }
        .info-value {
            color: #2c3e50;
        }
        .cost-summary {
            background-color: #e8f5e9;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border-right: 4px solid #4caf50;
        }
        .cost-summary h3 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 18px;
        }
        .total-cost {
            font-size: 20px;
            font-weight: bold;
            color: #1b5e20;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 12px;
        }
        thead {
            background-color: #2c3e50;
            color: white;
        }
        th {
            padding: 12px 8px;
            text-align: right;
            font-weight: bold;
            border: 1px solid #34495e;
        }
        td {
            padding: 10px 8px;
            border: 1px solid #ddd;
            text-align: right;
        }
        tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        tbody tr:hover {
            background-color: #e3f2fd;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            text-align: center;
        }
        .manager-name {
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .signature-image {
            text-align: center;
            margin-top: 20px;
        }
        .signature-image img {
            max-width: 150px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Almajd Academy</h1>
    </div>

    <div class="student-info">
        <h2>معلومات الطالب</h2>
        <div class="info-row">
            <span class="info-label">اسم الطالب:</span>
            <span class="info-value">{{ $student->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">من تاريخ:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($fromDate)->format('Y-m-d') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">إلى تاريخ:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($toDate)->format('Y-m-d') }}</span>
        </div>
    </div>

    <div class="cost-summary">
        <h3>إجمالي التكلفة</h3>
        <div class="total-cost">
            {{ number_format($totalCost, 2) }} 
            @if($student->currency)
                {{ is_object($student->currency) && method_exists($student->currency, 'symbol') ? $student->currency->symbol() : $student->currency }}
            @else
                {{ 'EGP' }}
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>اسم المعلم</th>
                <th>مدة الدرس (دقيقة)</th>
                <th>تاريخ الدرس</th>
                <th>تكلفة الدرس</th>
                <th>العملة</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lessons as $lesson)
                @php
                    $lessonCost = $reportService->calculateLessonCost($lesson);
                @endphp
                <tr>
                    <td>{{ $lesson->course?->teacher?->name ?? 'N/A' }}</td>
                    <td>{{ $lesson->duration ?? 0 }}</td>
                    <td>{{ \Carbon\Carbon::parse($lesson->date)->format('Y-m-d') }}</td>
                    <td>{{ number_format($lessonCost, 2) }}</td>
                    <td>
                        @php
                            $currency = $student->currency ?? ($lesson->course?->student?->currency ?? null);
                            if ($currency) {
                                $currencySymbol = is_object($currency) && method_exists($currency, 'symbol') ? $currency->symbol() : $currency;
                                echo $currencySymbol;
                            } else {
                                echo 'EGP';
                            }
                        @endphp
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">
                        لا توجد دروس في هذا الفترة
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="manager-name">
            Almajd Manager: Ibrahim Mohamed
        </div>
        <div class="signature-image">
            @if(file_exists(public_path('ketm3.png')))
                <img src="{{ public_path('ketm3.png') }}" alt="Signature">
            @endif
        </div>
    </div>
</body>
</html>
