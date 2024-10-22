<?php

namespace App\Models\notes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class operation_sur_resultats extends Model
{
    use HasFactory;
    protected $table = "operation_sur_resultats";
    protected $primaryKey = "id_operation_sur_resultats";
}
