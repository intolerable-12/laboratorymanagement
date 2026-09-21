<?php

namespace App\Exports;

use App\Models\Laboratory;
use App\Models\SchoolYear;
use App\Services\AcademicPeriodResolver;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EquipmentInventoryReportSheet implements Export, FromArray, WithColumnWidths, WithCustomStartCell, WithEvents, WithTitle
{
    private const FIXED_COLUMN_COUNT = 4;

    private const YEAR_HEADER_ROW = 9;

    private const SEMESTER_HEADER_ROW = 10;

    private const SUBHEADER_ROW = 11;

    private const LABORATORY_SECTION_ROW = 12;

    private const DATA_START_ROW = 13;

    private const ITEMS_COLUMN_WIDTH = 32;

    private const DESCRIPTION_COLUMN_WIDTH = 38;

    private const REMARKS_COLUMN_WIDTH = 18;

    public function __construct(
        private readonly ?Laboratory $laboratory,
        private readonly Collection $equipment,
        private readonly Collection $schoolYears,
        private readonly Collection $semesters,
        private readonly array $signatories,
        private readonly string $sheetTitle,
    ) {}

    public function array(): array
    {
        $rows = [];
        $latestSchoolYearId = $this->schoolYears->last()?->id;

        foreach ($this->equipment as $equipment) {
            $row = [
                $equipment->purchase_date
                    ? Date::dateTimeToExcel($equipment->purchase_date->copy()->startOfDay())
                    : null,
                (string) $equipment->equipment_name,
                (int) $equipment->quantity,
                (string) ($equipment->description ?? ''),
            ];

            $periods = $equipment->relationLoaded('inventoryPeriods')
                ? $equipment->inventoryPeriods->keyBy(fn ($period): string => $period->school_year_id.'-'.$period->semester_id)
                : collect();

            foreach ($this->schoolYears as $schoolYear) {
                foreach ($this->semesters as $semesterIndex => $semester) {
                    $range = $this->semesterRange($schoolYear, $semesterIndex);
                    $acquiredAfterPeriod = $equipment->purchase_date
                        && $equipment->purchase_date->greaterThan($range['end']);
                    $period = $periods->get($schoolYear->id.'-'.$semester->id);

                    if ($period) {
                        $row[] = $period->beginning_quantity;
                        $row[] = $period->ending_quantity;
                    } else {
                        // Keep older records readable if this report is generated before
                        // their period rows have been initialized by the migration.
                        $row[] = $acquiredAfterPeriod ? null : (int) $equipment->quantity;
                        $row[] = $acquiredAfterPeriod ? null : (int) $equipment->quantity;
                    }
                }

                $periodRemarks = $this->remarksForSchoolYear($periods, $schoolYear);
                $currentRemark = $schoolYear->id === $latestSchoolYearId
                    ? trim((string) ($equipment->remarks ?? ''))
                    : '';
                $row[] = collect([$periodRemarks, $currentRemark])
                    ->filter()
                    ->unique()
                    ->implode(' ');
            }

            $rows[] = $row;
        }

        return $rows;
    }

    public function startCell(): string
    {
        return 'A'.self::DATA_START_ROW;
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 14,
            'B' => self::ITEMS_COLUMN_WIDTH,
            'C' => 8,
            'D' => self::DESCRIPTION_COLUMN_WIDTH,
        ];

        $column = self::FIXED_COLUMN_COUNT + 1;

        foreach ($this->schoolYears as $schoolYear) {
            foreach ($this->semesters as $semester) {
                $widths[Coordinate::stringFromColumnIndex($column)] = 8;
                $widths[Coordinate::stringFromColumnIndex($column + 1)] = 8;
                $column += 2;
            }

            $widths[Coordinate::stringFromColumnIndex($column)] = 18;
            $column++;
        }

        return $widths;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $this->formatSheet($event->sheet->getDelegate());
            },
        ];
    }

    private function formatSheet(Worksheet $sheet): void
    {
        $lastColumnIndex = $this->lastColumnIndex();
        $lastColumn = Coordinate::stringFromColumnIndex($lastColumnIndex);
        $dataEndRow = self::DATA_START_ROW + max(0, $this->equipment->count() - 1);
        $footerHeadingRow = $dataEndRow + 2;
        $footerEndRow = $footerHeadingRow + 4;

        $sheet->setShowGridLines(false);
        $sheet->freezePane('E'.self::DATA_START_ROW);
        $sheet->getSheetView()->setZoomScale(75);
        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_A3)
            ->setFitToWidth(1)
            ->setFitToHeight(0);
        $sheet->getPageMargins()
            ->setTop(0.25)
            ->setRight(0.25)
            ->setBottom(0.35)
            ->setLeft(0.25)
            ->setHeader(0.1)
            ->setFooter(0.15);
        $sheet->getPageSetup()
            ->setHorizontalCentered(true)
            ->setRowsToRepeatAtTopByStartAndEnd(1, self::SUBHEADER_ROW);

        $sheet->getStyle('A1:'.$lastColumn.$footerEndRow)->getFont()
            ->setName('Arial')
            ->setSize(9);

        $this->writeReportHeading($sheet, $lastColumn);
        $this->writeTableHeading($sheet, $lastColumn);
        $this->writeFooter($sheet, $lastColumn, $footerHeadingRow);

        $sheet->getStyle('A'.self::DATA_START_ROW.':'.$lastColumn.$dataEndRow)
            ->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '808080'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ]);
        $sheet->getStyle('A'.self::DATA_START_ROW.':A'.$dataEndRow)
            ->getNumberFormat()->setFormatCode('mm/dd/yyyy');
        $sheet->getStyle('C'.self::DATA_START_ROW.':C'.$dataEndRow)
            ->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('A'.self::DATA_START_ROW.':A'.$dataEndRow)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C'.self::DATA_START_ROW.':'.$lastColumn.$dataEndRow)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B'.self::DATA_START_ROW.':B'.$dataEndRow)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('D'.self::DATA_START_ROW.':D'.$dataEndRow)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        for ($row = self::DATA_START_ROW; $row <= $dataEndRow; $row++) {
            $equipment = $this->equipment->values()->get($row - self::DATA_START_ROW);
            $lineCount = max(
                $this->wrappedLineCount((string) ($equipment?->equipment_name ?? ''), self::ITEMS_COLUMN_WIDTH),
                $this->wrappedLineCount((string) ($equipment?->description ?? ''), self::DESCRIPTION_COLUMN_WIDTH),
                $this->wrappedLineCount($this->remarksForEquipment($equipment), self::REMARKS_COLUMN_WIDTH),
            );

            $sheet->getRowDimension($row)->setRowHeight(min(90, max(18, $lineCount * 16)));
        }

        $sheet->getStyle('B'.self::DATA_START_ROW.':B'.$dataEndRow)
            ->getAlignment()->setWrapText(true);
        $sheet->getStyle('D'.self::DATA_START_ROW.':D'.$dataEndRow)
            ->getAlignment()->setWrapText(true);

        $sheet->getStyle('A'.$footerHeadingRow.':'.$lastColumn.$footerEndRow)
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
    }

    private function writeReportHeading(Worksheet $sheet, string $lastColumn): void
    {
        $headingRows = [
            2 => ['LOURDES COLLEGE', 13, true],
            3 => ['Cagayan de Oro City', 10, false],
            4 => ['HIGHER EDUCATION DEPARTMENT', 10, true],
            6 => ['INVENTORY REPORT FOR SCIENCE LABORATORY- APPARATUS', 11, true],
            7 => [$this->locationLabel(), 10, false],
        ];

        foreach ($headingRows as $row => [$value, $fontSize, $bold]) {
            $sheet->mergeCells('A'.$row.':'.$lastColumn.$row);
            $sheet->setCellValue('A'.$row, $value);
            $sheet->getStyle('A'.$row.':'.$lastColumn.$row)->applyFromArray([
                'font' => [
                    'name' => 'Arial',
                    'size' => $fontSize,
                    'bold' => $bold,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
            $sheet->getRowDimension($row)->setRowHeight($row === 6 ? 24 : 18);
        }
    }

    private function writeTableHeading(Worksheet $sheet, string $lastColumn): void
    {
        $fixedHeaders = [
            'A' => 'ACQRD',
            'B' => 'ITEMS',
            'C' => 'QTY',
            'D' => 'DESCRIPTION',
        ];

        foreach ($fixedHeaders as $column => $label) {
            $sheet->mergeCells($column.self::YEAR_HEADER_ROW.':'.$column.self::SUBHEADER_ROW);
            $sheet->setCellValue($column.self::YEAR_HEADER_ROW, $label);
        }

        // The remarks column is appended to every school-year group.
        $column = self::FIXED_COLUMN_COUNT + 1;

        foreach ($this->schoolYears as $schoolYear) {
            $yearStart = $column;
            $yearEnd = $column + ($this->semesters->count() * 2);
            $yearEndColumn = Coordinate::stringFromColumnIndex($yearEnd);
            $yearStartColumn = Coordinate::stringFromColumnIndex($yearStart);

            $sheet->mergeCells($yearStartColumn.self::YEAR_HEADER_ROW.':'.$yearEndColumn.self::YEAR_HEADER_ROW);
            $sheet->setCellValue($yearStartColumn.self::YEAR_HEADER_ROW, $schoolYear->school_year);

            foreach ($this->semesters as $semester) {
                $semesterStartColumn = Coordinate::stringFromColumnIndex($column);
                $semesterEndColumn = Coordinate::stringFromColumnIndex($column + 1);
                $sheet->mergeCells($semesterStartColumn.self::SEMESTER_HEADER_ROW.':'.$semesterEndColumn.self::SEMESTER_HEADER_ROW);
                $sheet->setCellValue($semesterStartColumn.self::SEMESTER_HEADER_ROW, $this->semesterLabel($semester));
                $sheet->setCellValue($semesterStartColumn.self::SUBHEADER_ROW, 'Beg.');
                $sheet->setCellValue($semesterEndColumn.self::SUBHEADER_ROW, 'End.');
                $column += 2;
            }

            $remarksColumn = Coordinate::stringFromColumnIndex($column);
            $sheet->mergeCells($remarksColumn.self::SEMESTER_HEADER_ROW.':'.$remarksColumn.self::SUBHEADER_ROW);
            $sheet->setCellValue($remarksColumn.self::SEMESTER_HEADER_ROW, 'Remarks');
            $column++;
        }

        $headerRange = 'A'.self::YEAR_HEADER_ROW.':'.$lastColumn.self::SUBHEADER_ROW;
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 9,
                'bold' => true,
                'color' => ['rgb' => '000000'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F2F2F2'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        $column = self::FIXED_COLUMN_COUNT + 1;
        foreach ($this->schoolYears as $schoolYear) {
            $yearStart = Coordinate::stringFromColumnIndex($column);
            $yearEnd = Coordinate::stringFromColumnIndex($column + ($this->semesters->count() * 2));
            $sheet->getStyle($yearStart.self::YEAR_HEADER_ROW.':'.$yearEnd.self::YEAR_HEADER_ROW)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFFF00'],
                ],
            ]);

            $column += ($this->semesters->count() * 2) + 1;
        }

        foreach ($this->schoolYears as $schoolYear) {
            $remarksColumn = $this->remarksColumnForSchoolYear($schoolYear);
            $sheet->getStyle($remarksColumn.self::SEMESTER_HEADER_ROW.':'.$remarksColumn.self::SUBHEADER_ROW)
                ->getFont()->getColor()->setRGB('FF0000');
        }

        $sheet->getStyle('A'.self::YEAR_HEADER_ROW.':'.$lastColumn.self::SUBHEADER_ROW)
            ->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getRowDimension(self::YEAR_HEADER_ROW)->setRowHeight(23);
        $sheet->getRowDimension(self::SEMESTER_HEADER_ROW)->setRowHeight(20);
        $sheet->getRowDimension(self::SUBHEADER_ROW)->setRowHeight(18);

        $sheet->mergeCells('A'.self::LABORATORY_SECTION_ROW.':'.$lastColumn.self::LABORATORY_SECTION_ROW);
        $sheet->setCellValue('A'.self::LABORATORY_SECTION_ROW, $this->laboratory?->laboratory_name ?: $this->sheetTitle);
        $sheet->getStyle('A'.self::LABORATORY_SECTION_ROW.':'.$lastColumn.self::LABORATORY_SECTION_ROW)->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 9,
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F0C1F0'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
        $sheet->getRowDimension(self::LABORATORY_SECTION_ROW)->setRowHeight(18);
    }

    private function writeFooter(Worksheet $sheet, string $lastColumn, int $footerHeadingRow): void
    {
        $sheet->mergeCells('A'.$footerHeadingRow.':D'.$footerHeadingRow);
        $sheet->setCellValue('A'.$footerHeadingRow, 'Certification / Signatories');

        $sheet->getStyle('A'.$footerHeadingRow.':'.$lastColumn.$footerHeadingRow)->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 9,
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFCC'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '808080'],
                ],
            ],
        ]);

        $fields = [
            'prepared_by' => 'Prepared by:',
            'checked_by' => 'Checked by:',
            'verified_by' => 'Verified by:',
            'approved_by' => 'Approved by:',
        ];

        foreach ($this->schoolYears as $yearIndex => $schoolYear) {
            $startColumnIndex = $this->schoolYearStartColumn($yearIndex);
            $endColumnIndex = $this->schoolYearEndColumn($yearIndex);
            $startColumn = Coordinate::stringFromColumnIndex($startColumnIndex);
            $endColumn = Coordinate::stringFromColumnIndex($endColumnIndex);
            $names = $this->signatories[(string) $schoolYear->id] ?? $this->signatories[$schoolYear->id] ?? [];

            $sheet->mergeCells($startColumn.$footerHeadingRow.':'.$endColumn.$footerHeadingRow);
            $sheet->setCellValue($startColumn.$footerHeadingRow, $schoolYear->school_year);

            foreach (array_values($fields) as $offset => $label) {
                $row = $footerHeadingRow + $offset + 1;
                $sheet->mergeCells($startColumn.$row.':'.$endColumn.$row);
                $field = array_keys($fields)[$offset];
                $name = trim((string) ($names[$field] ?? ''));
                $sheet->setCellValue($startColumn.$row, $label.' '.($name !== '' ? $name : ''));
            }

            $sheet->getStyle($startColumn.$footerHeadingRow.':'.$endColumn.($footerHeadingRow + 4))->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '808080'],
                    ],
                ],
            ]);
        }

        $sheet->getStyle('A'.$footerHeadingRow.':'.$lastColumn.($footerHeadingRow + 4))
            ->getFont()->setName('Arial')->setSize(9);
        $sheet->getStyle('A'.($footerHeadingRow + 1).':'.$lastColumn.($footerHeadingRow + 4))
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getRowDimension($footerHeadingRow)->setRowHeight(20);

        for ($row = $footerHeadingRow + 1; $row <= $footerHeadingRow + 4; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(24);
        }
    }

    private function lastColumnIndex(): int
    {
        return self::FIXED_COLUMN_COUNT + ($this->schoolYears->count() * (($this->semesters->count() * 2) + 1));
    }

    private function schoolYearStartColumn(int $yearIndex): int
    {
        return self::FIXED_COLUMN_COUNT + 1 + ($yearIndex * (($this->semesters->count() * 2) + 1));
    }

    private function schoolYearEndColumn(int $yearIndex): int
    {
        return $this->schoolYearStartColumn($yearIndex) + ($this->semesters->count() * 2);
    }

    private function remarksColumnForSchoolYear(SchoolYear $schoolYear): string
    {
        $yearIndex = $this->schoolYears->search(fn (SchoolYear $candidate): bool => $candidate->id === $schoolYear->id);
        $column = $this->schoolYearEndColumn((int) $yearIndex);

        return Coordinate::stringFromColumnIndex($column);
    }

    /**
     * The application has no semester start/end columns. Split the configured school-year
     * date range evenly across the configured semesters so the workbook remains auditable.
     *
     * @return array{start: Carbon, end: Carbon}
     */
    private function semesterRange(SchoolYear $schoolYear, int $semesterIndex): array
    {
        return app(AcademicPeriodResolver::class)->semesterRange(
            $schoolYear,
            $semesterIndex,
            $this->semesters->count(),
        );
    }

    private function remarksForSchoolYear(Collection $periods, SchoolYear $schoolYear): string
    {
        return $periods
            ->filter(fn ($period): bool => (int) $period->school_year_id === (int) $schoolYear->id)
            ->pluck('remarks')
            ->filter(fn ($remark): bool => trim((string) $remark) !== '')
            ->map(fn ($remark): string => trim((string) $remark))
            ->unique()
            ->implode(' ');
    }

    private function remarksForEquipment(?object $equipment): string
    {
        if (! $equipment) {
            return '';
        }

        $periodRemarks = $equipment->relationLoaded('inventoryPeriods')
            ? $equipment->inventoryPeriods->pluck('remarks')
            : collect();

        return collect([$equipment->remarks, ...$periodRemarks->all()])
            ->filter(fn ($remark): bool => trim((string) $remark) !== '')
            ->map(fn ($remark): string => trim((string) $remark))
            ->unique()
            ->implode(' ');
    }

    private function semesterLabel(object $semester): string
    {
        $name = Str::lower(trim((string) ($semester->semester_name ?? 'Semester')));

        return match (true) {
            Str::contains($name, ['first', '1st']) => '1st sem.',
            Str::contains($name, ['second', '2nd']) => '2nd sem.',
            Str::contains($name, ['summer']) => 'Summer',
            default => Str::limit((string) ($semester->semester_name ?? 'Semester'), 14, ''),
        };
    }

    private function wrappedLineCount(string $value, int $columnWidth): int
    {
        $value = trim($value);

        if ($value === '') {
            return 1;
        }

        $lineCount = 0;

        foreach (preg_split('/\R/', $value) ?: [''] as $paragraph) {
            $words = preg_split('/\s+/', trim($paragraph)) ?: [''];
            $currentLineWidth = 0;
            $paragraphLineCount = 1;

            foreach ($words as $word) {
                $wordWidth = max(1, mb_strwidth($word));

                if ($wordWidth > $columnWidth) {
                    if ($currentLineWidth > 0) {
                        $paragraphLineCount++;
                    }

                    $paragraphLineCount += intdiv($wordWidth - 1, $columnWidth);
                    $currentLineWidth = $wordWidth % $columnWidth;

                    continue;
                }

                if ($currentLineWidth === 0) {
                    $currentLineWidth = $wordWidth;
                } elseif ($currentLineWidth + 1 + $wordWidth <= $columnWidth) {
                    $currentLineWidth += 1 + $wordWidth;
                } else {
                    $paragraphLineCount++;
                    $currentLineWidth = $wordWidth;
                }
            }

            $lineCount += max(1, $paragraphLineCount);
        }

        return max(1, $lineCount);
    }

    private function locationLabel(): string
    {
        if (! $this->laboratory) {
            return 'Laboratory equipment inventory';
        }

        $location = collect([
            $this->laboratory->building,
            $this->laboratory->room_number ? 'Room '.$this->laboratory->room_number : null,
        ])->filter()->implode(' · ');

        return $location !== '' ? $location : $this->laboratory->laboratory_name;
    }
}
