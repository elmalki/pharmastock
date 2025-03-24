<?php

namespace App\Exports;

use App\Models\Produit;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProduitsExport implements FromView, WithHeadings, WithEvents, ShouldAutoSize
{
    private $size;
    public function view(): View
    {
        $meds = Produit::all();
        $this->size = count($meds) + 1;
        return view(
            'export.stock',
            ['produits' => $meds]
        );
    }
    public function headings(): array
    {
        return [
            '#',
            'Label',
            'barcode',
        ];
    }
    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [

            AfterSheet::class    => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:D1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $event->sheet->getStyle('B2:D'.$this->size)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $event->sheet->getStyle('A1:D1')->applyFromArray(
                    [
                        'font' => [
                            'bold' => true,
                            'color'=>[
                                'rgb'=>'FFFFFF'
                            ]
                        ],
                        'fill' => [
                            'fillType'=>Fill::FILL_SOLID,
                            'color' => ['rgb' => '000000'],
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => [
                                    'rgb' => '000000'
                                ]
                            ],
                            'bottom' => [
                                'borderStyle' => Border::BORDER_THICK,
                                'color' => [
                                    'rgb' => '000000'
                                ]
                            ]
                        ]
                    ]
                );
                $event->sheet->getStyle('A2:D' . $this->size)->applyFromArray(
                    [
                        'font' => [
                            'bold' => false,
                            'background-color' => ['argb' => 'FFFF0000'],
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => [
                                    'rgb' => '000000'
                                ]
                            ]
                        ]
                    ]
                );
            },
        ];
    }
}
