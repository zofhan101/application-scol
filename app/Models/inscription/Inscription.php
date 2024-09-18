<?php

namespace App\Models\inscription;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inscription extends Model
{
    use HasFactory;
    protected $table = 'inscription';
    protected $primaryKey = 'id_inscription';
    protected $fillable =[
        'date_inscription',
        'date_annulation',
        'id_agent_inscription',
        'id_agent_annulation',
        'id_etudiant',
        'id_niveau',
        'id_au'
    ];

    protected $casts=[
        'date_inscription' => 'date',
        'date_annulation' => 'date',
    ];
}
