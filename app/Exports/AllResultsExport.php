<?php

namespace App\Exports;

use App\Exports\ResultatsEvalExport;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AllResultsExport implements WithMultipleSheets
{
    protected $data;
    protected $sous_titres;

    public function __construct($data, $sous_titres)
    {
        $this->data = $data;
        $this->sous_titres = $sous_titres;
    }


    /**
    * @return \Illuminate\Support\Collection
    */
    public function sheets(): array
    {
        $res = [];

        foreach($this->data as $index => $resultat){
            $res[] = new ResultatsEvalExport($resultat, $this->sous_titres[$index]);
        }

        return $res;
    }
}
