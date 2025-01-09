<?php

namespace App\Exports;

use App\Exports\ListeExclusExport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ListeExclusAllExport implements WithMultipleSheets
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }


    /**
    * @return \Illuminate\Support\Collection
    */
    public function sheets(): array
    {
        $res = [];

        foreach($this->data as $parcours){
            foreach($parcours->niveaux as $niveau){
                $res[] = new ListeExclusExport($niveau);

            }
        }

        return $res;
    }
}
