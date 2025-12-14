<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academy Statistics Report</title>
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
        .date-info {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border-right: 4px solid #3498db;
        }
        .date-info h2 {
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
        .statistics-section {
            margin-bottom: 30px;
        }
        .statistics-section h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 18px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }
        .stat-box {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            border-right: 4px solid #3498db;
        }
        .stat-box h4 {
            color: #555;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .stat-item {
            margin: 8px 0;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
        }
        .stat-label {
            font-weight: bold;
            color: #555;
        }
        .stat-value {
            color: #2c3e50;
            font-weight: bold;
        }
        .revenue-box {
            background-color: #e8f5e9;
            border-right-color: #4caf50;
        }
        .collected-box {
            background-color: #e3f2fd;
            border-right-color: #2196f3;
        }
        .remaining-box {
            background-color: #fff3cd;
            border-right-color: #ffc107;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 10px;
        }
        thead {
            background-color: #2c3e50;
            color: white;
        }
        th {
            padding: 10px 6px;
            text-align: right;
            font-weight: bold;
            border: 1px solid #34495e;
        }
        td {
            padding: 8px 6px;
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

    <div class="date-info">
        <h2>معلومات التقرير</h2>
        <div class="info-row">
            <span class="info-label">من تاريخ:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($statistics['from_date'])->format('Y-m-d') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">إلى تاريخ:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($statistics['to_date'])->format('Y-m-d') }}</span>
        </div>
    </div>

    <div class="statistics-section">
        <h3>الإحصائيات المالية</h3>
        
        <div class="stat-box revenue-box">
            <h4>إجمالي الإيرادات حسب العملة</h4>
            @foreach($statistics['revenue_by_currency'] as $currencyKey => $data)
                <div class="stat-item">
                    <span class="stat-label">{{ $data['currency']->symbol() }}:</span>
                    <span class="stat-value">{{ number_format($data['amount'], 2) }}</span>
                </div>
            @endforeach
            @if(empty($statistics['revenue_by_currency']))
                <div class="stat-item">
                    <span class="stat-label">لا توجد إيرادات</span>
                </div>
            @endif
        </div>

        <div class="stat-box collected-box">
            <h4>إجمالي المحصل</h4>
            @foreach($statistics['total_collected'] as $currencyKey => $amount)
                @if($amount > 0)
                    <div class="stat-item">
                        <span class="stat-label">{{ \App\Enums\Currency::from($currencyKey)->symbol() }}:</span>
                        <span class="stat-value">{{ number_format($amount, 2) }}</span>
                    </div>
                @endif
            @endforeach
            @if(collect($statistics['total_collected'])->sum() == 0)
                <div class="stat-item">
                    <span class="stat-label">لا يوجد محصل</span>
                </div>
            @endif
        </div>

        <div class="stat-box remaining-box">
            <h4>إجمالي المتبقي</h4>
            @foreach($statistics['total_remaining'] as $currencyKey => $amount)
                @if($amount > 0)
                    <div class="stat-item">
                        <span class="stat-label">{{ \App\Enums\Currency::from($currencyKey)->symbol() }}:</span>
                        <span class="stat-value">{{ number_format($amount, 2) }}</span>
                    </div>
                @endif
            @endforeach
            @if(collect($statistics['total_remaining'])->sum() == 0)
                <div class="stat-item">
                    <span class="stat-label">لا يوجد متبقي</span>
                </div>
            @endif
        </div>
    </div>

    <div class="statistics-section">
        <h3>تفاصيل الدروس (مرتبة حسب اسم الطالب)</h3>
        <table>
            <thead>
                <tr>
                    <th>اسم الطالب</th>
                    <th>اسم المعلم</th>
                    <th>مدة الدرس (دقيقة)</th>
                    <th>تاريخ الدرس</th>
                    <th>تكلفة الدرس</th>
                    <th>العملة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($statistics['lessons'] as $lesson)
                    @php
                        $lessonCost = $reportService->calculateLessonCost($lesson);
                    @endphp
                    <tr>
                        <td>{{ $lesson->course?->student?->name ?? 'N/A' }}</td>
                        <td>{{ $lesson->course?->teacher?->name ?? 'N/A' }}</td>
                        <td>{{ $lesson->duration ?? 0 }}</td>
                        <td>{{ \Carbon\Carbon::parse($lesson->date)->format('Y-m-d') }}</td>
                        <td>{{ number_format($lessonCost, 2) }}</td>
                        <td>{{ $lesson->course?->student?->currency?->symbol() ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px;">
                            لا توجد دروس في هذا الفترة
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        <div class="manager-name">
            Almajd Manager: Ibrahim Mohamed
        </div>
        <div class="signature-image">
            <img src="{{ public_path('ketm3.png') }}" alt="Signature">
        </div>
    </div>
</body>
</html>
