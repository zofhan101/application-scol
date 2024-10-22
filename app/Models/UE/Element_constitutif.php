<?php

namespace App\Models\UE;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Element_constitutif extends Model
{
    use HasFactory;
    protected $table = 'element_constitutif';
    protected $primaryKey = 'id_element_constitutif';
    protected $fillable =[
        'nom_element_constitutif'
    ];
}
