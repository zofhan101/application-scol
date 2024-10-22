<?php

namespace App\Models\inscription;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Autre_inscription extends Model
{
    use HasFactory;
    protected $table='autres_inscriptions';
    protected $primaryKey = 'id_au';
    protected $fillable= [
        'id_au',
        'id_etudiants',
        'etablissement',
        'niveau'
    ];
}
