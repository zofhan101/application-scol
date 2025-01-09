<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\ListeRepechageExport;

class ListeRepechageAllExport implements WithMultipleSheets
{

    protected $data;
    protected $sous_titres;
    protected $legendes;

    public function __construct($data, $sous_titres, $legendes)
    {
        $this->data = $data;
        $this->sous_titres = $sous_titres;
        $this->legendes = $legendes;
    }

    public function sheets(): array
    {
        $res = [];

        foreach($this->data as $index => $resultat){
            $res[] = new ListeRepechageExport($resultat, $this->sous_titres[$index], $this->legendes);
        }

        return $res;
    }
}
