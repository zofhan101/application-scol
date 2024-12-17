<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ResultatsEvalExport implements FromCollection, WithDrawings, WithHeadings, WithEvents, WithMapping, WithCustomStartCell, WithStyles, WithTitle
{
    protected $data;
    protected $columnCount;
    protected $subtitles;
    protected $startRow;

    /**
     * Constructeur pour recevoir les données et sous-titres.
     *
     * @param array $data
     * @param array $subtitles
     * @param int $startRow
     */
    public function __construct($data, $subtitles, $startRow = 11)
    {
        $this->data = $data;
        $this->columnCount = $this->calculateColumnCount($data);
        $this->subtitles = $subtitles;
        $this->startRow = $startRow; // Ligne où les données commencent
    }

    public function title() : string{
        return $this->subtitles[1];
    }

    /**
     * Calculer le nombre de colonnes.
     *
     * @param array $data
     * @return int
     */
    private function calculateColumnCount($data)
    {
        return isset($data[0]) ? count((array)$data[0]) : 0;
    }

    /**
     * Retourner les données pour l'exportation.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect($this->data);
    }

    public function startCell(): string
    {
        return 'A17'; // Les données commenceront à partir de la cellule B2
    }


    /**
     * Insérer des images dans la feuille Excel.
     *
     * @return array
     */
    public function drawings()
    {
        $drawings = [];

        // Image logo en haut à gauche
        $logoDrawing = new Drawing();
        $logoDrawing->setName('Logo');
        $logoDrawing->setDescription('Logo');
        $logoDrawing->setPath(public_path('assets/images/logo.png')); // Chemin du logo
        $logoDrawing->setHeight(100);
        $logoDrawing->setCoordinates('B3');
        $drawings[] = $logoDrawing;

        // Image centrée en haut
        $centerColumn = chr(64 + ceil($this->columnCount / 2)); // Calculer la colonne centrale
        $centerDrawing = new Drawing();
        $centerDrawing->setName('Centered Image');
        $centerDrawing->setDescription('Centered Image');
        $centerDrawing->setPath(public_path('assets/images/en-tete.png')); // Image centrale
        $centerDrawing->setHeight(200); // Hauteur de l'image
        $centerDrawing->setCoordinates($centerColumn . '1'); // Positionner l'image au centre de la ligne 1
        $drawings[] = $centerDrawing;

        return $drawings;
    }

    /**
     * Définir les en-têtes de colonnes.
     *
     * @return array
     */
    public function headings(): array
    {
        return isset($this->data[0]) ? array_keys((array)$this->data[0]) : [];
    }

    /**
     * Mapper les données pour l'export.
     *
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return array_values((array)$row);
    }


    public function styles(Worksheet  $sheet)
    {

        return [
            17   => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                ],
            ],
        ];
    }

    /**
     * Enregistrer des événements pour ajuster la largeur des colonnes et éviter le chevauchement.
     *
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                // Ajuster l'espacement entre les éléments (sous-titres et données)
                $startRow = $this->startRow;
                $sheet->insertNewRowBefore($startRow, 1);

                // Insérer les sous-titres
                $subtitleRow = $startRow + 1; // Placer les sous-titres sous les images
                foreach ($this->subtitles as $index => $subtitle) {
                    $centerColumnRange = "A" . ($subtitleRow + $index) . ":" . chr(64 + $this->columnCount) . ($subtitleRow + $index);
                    $sheet->mergeCells($centerColumnRange); // Fusionner les cellules pour les sous-titres
                    $sheet->setCellValueByColumnAndRow(1, $subtitleRow + $index, $subtitle); // Placer les sous-titres
                    $sheet->getStyle($centerColumnRange)->getAlignment()->setHorizontal('center');
                    $sheet->getStyle($centerColumnRange)->getAlignment()->setHorizontal('center');
                    $sheet->getStyle($centerColumnRange)->getFont()->setBold(true);
                    $sheet->getStyle($centerColumnRange)->getFont()->setSize(14);

                }

                // Limiter la largeur des colonnes et permettre l'affichage complet
                for ($col = 'A'; $col <= chr(64 + $this->columnCount); $col++) {
                    $sheet->getColumnDimension($col)->setWidth(20); // Limiter la largeur des colonnes
                    $sheet->getStyle($col)->getAlignment()->setWrapText(true); // Activer le retour à la ligne
                }

                // Ajuster la hauteur des lignes pour éviter les coupures de texte
                foreach ($sheet->getRowDimensions() as $row) {
                    $row->setRowHeight(-1); // Hauteur automatique de la ligne
                }
            },
        ];
    }
}
