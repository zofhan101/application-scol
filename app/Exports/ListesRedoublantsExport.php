<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;


class ListesRedoublantsExport implements WithEvents, WithTitle,FromCollection, WithCustomStartCell, WithHeadings, WithDrawings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function startCell(): string
    {
        return 'A16';
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
        return $this->data->nom_parcours."_".$this->data->nom_niveau;
    }

    public function drawings()
    {
        $drawings = [];

        // Image logo en haut à gauche
        $logoDrawing = new Drawing();
        $logoDrawing->setName('Logo');
        $logoDrawing->setDescription('Logo');
        $logoDrawing->setPath(public_path('assets/images/logo.png'));
        $logoDrawing->setHeight(100);
        $logoDrawing->setCoordinates('A2');
        $drawings[] = $logoDrawing;

        // Image centrée en haut
        $centerDrawing = new Drawing();
        $centerDrawing->setName('Centered Image');
        $centerDrawing->setDescription('Centered Image');
        $centerDrawing->setPath(public_path('assets/images/en-tete.png'));
        $centerDrawing->setHeight(200);
        $centerDrawing->setCoordinates('C1');
        $drawings[] = $centerDrawing;

        return $drawings;
    }


    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                $sheet->setCellValue('C12', "Liste des étudiants autorisés à redoubler");
                $sheet->setCellValue('C13', $this->data->nom_niveau." - ".$this->data->nom_parcours);
                $sheet->setCellValue('C14', "Année universitaire ".$this->data->intitule);



                $sheet->getStyle('C12:C14')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                ]);


                $sheet->getStyle('A16:D16')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                ]);
            },
        ];
    }
}

