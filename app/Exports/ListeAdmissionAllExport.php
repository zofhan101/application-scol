<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\ListeAdmissionExport;

class ListeAdmissionAllExport implements WithMultipleSheets
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
                $res[] = new ListeAdmissionExport($niveau);

            }
        }

        return $res;
    }
}
