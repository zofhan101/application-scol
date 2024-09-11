<?php

namespace App\Models\inscription;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Selectionnes extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $primaryKey = 'id_selectionnes';
    protected $table="selectionnes";
    protected $fillable = [
        'nom',
        'prenoms',
        'num_bacc',
        'id_parcours',
        'id_au'
    ];

}
