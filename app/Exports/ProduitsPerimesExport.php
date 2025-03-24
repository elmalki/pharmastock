<?php

namespace App\Exports;

use App\Models\CommandeProduit;
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

class ProduitsPerimesExport implements FromView, WithHeadings, WithEvents, ShouldAutoSize
{
    private $size;
    public function view(): View
    {
        $items = CommandeProduit::whereNotNull('expirationDate')->where('expirationDate','<',now())->with('produit')->get();
        $this->size = count($items) + 1;
        return view(
            'export.perimes',
            ['items' => $items]
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
                $event->sheet->getStyle('A1:E1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $event->sheet->getStyle('B2:E'.$this->size)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $event->sheet->getStyle('A1:E1')->applyFromArray(
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
                $event->sheet->getStyle('A2:E' . $this->size)->applyFromArray(
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
