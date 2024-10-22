<?php

namespace App\Models\mention_parcours;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parcours extends Model
{
    use HasFactory;
    protected $table = "parcours";
    protected $primaryKey = "id_parcours";
}
