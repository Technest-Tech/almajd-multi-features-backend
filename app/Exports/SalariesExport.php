<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class SalariesExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    protected $salaries;
    protected $totalsByCurrency;
    protected $year;
    protected $month;

    public function __construct(array $salaries, array $totalsByCurrency, int $year, int $month)
    {
        $this->salaries = $salaries;
        $this->totalsByCurrency = $totalsByCurrency;
        $this->year = $year;
        $this->month = $month;
    }

    public function collection(): Collection
    {
        // If no salaries, return empty collection with just headers
        if (empty($this->salaries)) {
            return collect();
        }

        $data = collect($this->salaries)->map(function ($salary) {
            return [
                $salary['teacher_name'] ?? '',
                $salary['teacher_email'] ?? '',
                $salary['total_hours'] ?? 0,
                $salary['lessons_count'] ?? 0,
                $salary['currency'] ?? 'EGP',
                $salary['hour_price'] ?? 0,
                $salary['salary'] ?? 0,
            ];
        });

        // Add empty row if there are totals
        if (!empty($this->totalsByCurrency)) {
            $data->push([]);
        }

        // Add totals row
        foreach ($this->totalsByCurrency as $currency => $total) {
            $data->push([
                'الإجمالي',
                '',
                '',
                '',
                $currency,
                '',
                $total,
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'اسم المعلم',
            'البريد الإلكتروني',
            'إجمالي الساعات',
            'عدد الدروس',
            'العملة',
            'السعر بالساعة',
            'الراتب',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style header row
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Style data rows only if there are salaries
        $salaryCount = count($this->salaries);
        if ($salaryCount > 0) {
            $lastDataRow = $salaryCount + 1; // +1 for header row
            $sheet->getStyle('A2:G' . $lastDataRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Style totals rows
            if (!empty($this->totalsByCurrency)) {
                $totalStartRow = $salaryCount + 3; // +1 header +1 data +1 empty row
                $rowIndex = 0;
                foreach ($this->totalsByCurrency as $currency => $total) {
                    $row = $totalStartRow + $rowIndex;
                    $sheet->getStyle("A$row:G$row")->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 11,
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'E7E6E6'],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                    $rowIndex++;
                }
            }
        }

        return $sheet;
    }

    public function title(): string
    {
        $arabicMonths = [
            'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
            'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'
        ];
        return $arabicMonths[$this->month - 1] . ' ' . $this->year;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25, // Teacher name
            'B' => 30, // Email
            'C' => 15, // Total hours
            'D' => 12, // Lessons count
            'E' => 10, // Currency
            'F' => 15, // Hour price
            'G' => 15, // Salary
        ];
    }
}
