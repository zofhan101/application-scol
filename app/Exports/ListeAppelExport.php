<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithHeadings;


class ListeAppelExport implements WithEvents, WithTitle,FromCollection, WithCustomStartCell, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function headings(): array
    {
        return isset($this->data->liste_etu) ? array_keys((array)$this->data->liste_etu[0]) : [];
    }



    public function collection()
    {
        return collect($this->data->liste_etu);
    }

    public function title(): string
    {
        return $this->data->nom_parcours."_".$this->data->nom_niveau."_".$this->data->nom_ue;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                $sheet->setCellValue('D1', "Liste d'appel du repêchage des évaluations");
                $sheet->setCellValue('D2', $this->data->nom_niveau." - ".$this->data->nom_parcours);
                $sheet->setCellValue('D3', $this->data->intitule);

                $sheet->setCellValue('A5', 'Matière: '.$this->data->nom_ue);

                $sheet->getStyle('D1:D3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                ]);

                $sheet->getStyle('A5')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                ]);

                $sheet->getStyle('A8:D8')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                ]);
            },
        ];
    }
}
