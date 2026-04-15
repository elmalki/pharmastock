<?php

namespace App\Exports;

use App\Models\Produit;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ProduitsExport implements FromView, WithEvents, WithColumnWidths
{
    private int $dataStart = 10;   // first product row
    private int $dataEnd = 10;     // last product row
    private int $totalRow = 11;    // row with "TOTAL" summary

    public function view(): View
    {
        $produits = Produit::with('categorie')
            ->withSum('lots as lots_sum_qte', 'qte')
            ->withSum(['lots as lots_perime_sum' => fn($q) => $q->where('expirationDate', '<', now())], 'qte')
            ->orderBy('label')
            ->get();

        $stats = [
            'total'    => $produits->count(),
            'rupture'  => $produits->filter(fn($p) => ($p->qte ?? 0) == 0)->count(),
            'critique' => $produits->filter(fn($p) => ($p->qte ?? 0) > 0 && ($p->qte ?? 0) <= ($p->limit_command ?? 0))->count(),
            'valeur'   => $produits->sum(fn($p) => ($p->qte ?? 0) * ($p->prix_public ?? 0)),
        ];

        $this->dataStart = 10;
        $this->dataEnd   = 9 + $produits->count();
        $this->totalRow  = $this->dataEnd + 1;

        return view('export.stock', compact('produits', 'stats'));
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // #
            'B' => 16,  // Code-barres
            'C' => 32,  // Désignation
            'D' => 20,  // DCI
            'E' => 12,  // Dosage
            'F' => 18,  // Catégorie
            'G' => 9,   // Stock
            'H' => 9,   // Périmé
            'I' => 13,  // Statut
            'J' => 13,  // Prix public
            'K' => 14,  // Valeur
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $dataEnd = $this->dataEnd;
                $totalRow = $this->totalRow;

                // ── Merge header rows ──
                $sheet->mergeCells('A1:K1');   // Pharmacy name
                $sheet->mergeCells('A2:K2');   // Contact
                $sheet->mergeCells('A3:K3');   // Title
                $sheet->mergeCells('A4:K4');   // Date

                // Stats (row 6 labels, row 7 values)
                $sheet->mergeCells('A6:B6'); $sheet->mergeCells('A7:B7');
                $sheet->mergeCells('C6:D6'); $sheet->mergeCells('C7:D7');
                $sheet->mergeCells('E6:F6'); $sheet->mergeCells('E7:F7');
                $sheet->mergeCells('G6:K6'); $sheet->mergeCells('G7:K7');

                // Total row at bottom
                $sheet->mergeCells("A{$totalRow}:J{$totalRow}");

                // ── Row heights ──
                $sheet->getRowDimension(1)->setRowHeight(26);
                $sheet->getRowDimension(3)->setRowHeight(30);
                $sheet->getRowDimension(6)->setRowHeight(18);
                $sheet->getRowDimension(7)->setRowHeight(26);
                $sheet->getRowDimension(9)->setRowHeight(22);
                $sheet->getRowDimension($totalRow)->setRowHeight(24);

                // ── Pharmacy name (row 1) ──
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1A5276']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
                ]);

                // ── Contact info (row 2) ──
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['size' => 9, 'color' => ['rgb' => '555555']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
                ]);

                // ── Title bar (row 3) ──
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1A5276']],
                ]);

                // ── Date (row 4) ──
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => ['size' => 9, 'italic' => true, 'color' => ['rgb' => '888888']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // ── Stats labels (row 6) ──
                $sheet->getStyle('A6:K6')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 8, 'color' => ['rgb' => '888888']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FAFBFC']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']]],
                ]);

                // ── Stats values (row 7) ──
                $sheet->getStyle('A7:K7')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FAFBFC']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']]],
                ]);
                $sheet->getStyle('A7:B7')->getFont()->getColor()->setRGB('1A5276');   // total (blue)
                $sheet->getStyle('C7:D7')->getFont()->getColor()->setRGB('C0392B');   // rupture (red)
                $sheet->getStyle('E7:F7')->getFont()->getColor()->setRGB('D68910');   // critique (amber)
                $sheet->getStyle('G7:K7')->getFont()->getColor()->setRGB('27AE60');   // valeur (green)

                // ── Table header (row 9) ──
                $sheet->getStyle('A9:K9')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1A5276']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '1A5276']]],
                ]);

                // ── Data rows ──
                if ($dataEnd >= $this->dataStart) {
                    $range = "A{$this->dataStart}:K{$dataEnd}";
                    $sheet->getStyle($range)->applyFromArray([
                        'font' => ['size' => 10],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']]],
                    ]);

                    // Centered columns (A, B, G, H, I)
                    foreach (['A', 'B', 'G', 'H', 'I'] as $col) {
                        $sheet->getStyle("{$col}{$this->dataStart}:{$col}{$dataEnd}")
                            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                    // Right-aligned price columns (J, K)
                    foreach (['J', 'K'] as $col) {
                        $sheet->getStyle("{$col}{$this->dataStart}:{$col}{$dataEnd}")
                            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $sheet->getStyle("{$col}{$this->dataStart}:{$col}{$dataEnd}")
                            ->getNumberFormat()->setFormatCode('#,##0.00');
                    }

                    // Zebra striping (alternate rows)
                    for ($r = $this->dataStart; $r <= $dataEnd; $r++) {
                        if ((($r - $this->dataStart) % 2) === 0) {
                            $sheet->getStyle("A{$r}:K{$r}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F8F9FA']],
                            ]);
                        }

                        // Status column color
                        $statut = $sheet->getCell("I{$r}")->getValue();
                        $statutColor = match ($statut) {
                            'Rupture'    => ['text' => 'C0392B', 'bg' => 'FDECEA'],
                            'Critique'   => ['text' => 'B9770E', 'bg' => 'FEF4E4'],
                            'Disponible' => ['text' => '1E8449', 'bg' => 'E8F6EF'],
                            default      => null,
                        };
                        if ($statutColor) {
                            $sheet->getStyle("I{$r}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => $statutColor['text']]],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $statutColor['bg']]],
                                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                            ]);
                        }

                        // Highlight qte = 0 cells
                        $qteVal = (int) $sheet->getCell("G{$r}")->getValue();
                        if ($qteVal === 0) {
                            $sheet->getStyle("G{$r}")->getFont()->setBold(true)->getColor()->setRGB('C0392B');
                        }
                        // Highlight périmé > 0
                        $perimeVal = (int) $sheet->getCell("H{$r}")->getValue();
                        if ($perimeVal > 0) {
                            $sheet->getStyle("H{$r}")->getFont()->setBold(true)->getColor()->setRGB('C0392B');
                        }
                    }
                }

                // ── Total row ──
                $sheet->getStyle("A{$totalRow}:K{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1A5276']],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '1A5276']]],
                ]);
                $sheet->getStyle("A{$totalRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setIndent(1);
                $sheet->getStyle("K{$totalRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("K{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.00');

                // ── Freeze panes on header row ──
                $sheet->freezePane('A10');
            },
        ];
    }
}
