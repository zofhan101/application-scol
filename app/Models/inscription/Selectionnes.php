<?php

namespace App\Models\inscription;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;

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

    public static function admission_exists($au,$num_bacc, $id_parcours){
        $parcours = Selectionnes::where('id_au',$au)
                                    ->where('num_bacc', $num_bacc)
                                    ->where('id_parcours', $id_parcours)
                                    ->get();
        if(empty($parcours))
            throw new Exception('Cet étudiant n\'est pas admis au parcours sélectionné');
        return $parcours;
    }

    public static function check_admission($au,$num_bacc){
        $parcours = DB::select('
            select * from v_selectionnes_parcours where id_au = ? and num_bacc = ? and est_inscrit is null
        ',[$au, $num_bacc]);

        if(empty($parcours)){
            throw new Exception('Etudiant non admis ou déjà inscrit pour l\'A.U. en cours');
        }
        return $parcours;
    }

}
