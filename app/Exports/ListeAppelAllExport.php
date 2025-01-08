<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\ListeAppelExport;


class ListeAppelAllExport implements WithMultipleSheets
{

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;

    }

    public function sheets(): array
    {
        $res = [];

        foreach($this->data as $liste){
            $res[] = new ListeAppelExport($liste);
        }

        return $res;
    }
}
