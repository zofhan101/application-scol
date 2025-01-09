<?php

namespace App\Models\mention_parcours;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Parcours extends Model
{
    use HasFactory;
    protected $table = "parcours";
    protected $primaryKey = "id_parcours";

    public static function get_mention_by_id_parcours($id_parcours){
        $res = DB::select('
            SELECT m.*
            FROM parcours p
            JOIN mention m ON p.id_mention = m.id_mention
            WHERE p.id_parcours = ?
        ', [$id_parcours]);

        return $res[0];
    }
}
