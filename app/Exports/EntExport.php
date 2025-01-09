<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EntExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function headings(): array
    {
        return isset($this->data[0]) ? array_keys((array)$this->data[0]) : [];
    }



    public function collection()
    {
        return collect($this->data);
    }
}
