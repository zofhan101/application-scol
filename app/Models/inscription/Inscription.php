<?php

namespace App\Models\inscription;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\EtudiantNonInscritAuCourantException;
use App\Models\AU\AU;
use Illuminate\Support\Facades\DB;
use Exception;



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

    public static function annuler_inscription($inscription, $id_user): void{
        $id_inscription = $inscription->id_inscription;
        $inscription_elq = Inscription::find($id_inscription);
        $inscription_elq->date_annulation = date('Y-m-d');
        $inscription_elq->id_agent_annulation = $id_user;
        $inscription_elq->save();
    }

    public static function prend_certificat_scol(int $id_inscription): void {
        $inscription = Inscription::find($id_inscription);
        $inscription->date_certificat_scol = date('Y-m-d');
        $inscription->save();
    }

    public static function check_inscription(int $matricule, int $id_au){
        //vérifier que ce matricule est inscrit à l 'AU en cours


        $inscriptions = DB::select('select * from v_inscrits where id_au = ? and im = ?',[$id_au, $matricule]);
        if(!empty($inscriptions)){
            $inscription = $inscriptions[0];

            return $inscription;
        }
        else{
            throw new Exception('Etudiant non inscrit à l\'A.U. selectionnée');
        }
    }
}
