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
        'id_au',
        'statut'
    ];

    protected $casts=[
        'date_inscription' => 'date',
        'date_annulation' => 'date',
    ];

    // réinscription

    public static function reinscrire($id_etudiant, $id_au, $id_niveau, $statut, $id_user){
        DB::insert('
            INSERT INTO inscription(
                date_inscription,
                id_agent_inscription,
                id_etudiant,
                id_niveau,
                id_au,
                statut
            )
            VALUES(?,?,?,?,?,?);
        ', [
            date('Y-m-d'),
            $id_user,
            $id_etudiant,
            $id_niveau,
            $id_au,
            $statut
        ]);
    }

    public static function verifier_inscription($id_au, $id_etudiant){
        $inscription = DB::select('
            SELECT
            *
            FROM inscription
            WHERE id_au = ?
            AND id_etudiant = ?
        ', [$id_au, $id_etudiant]);

        if(!empty($inscription)){
            throw new Exception('ERREUR: cet étudiant est déjà inscrit à cette A.U.');
        }
    }

    public static function get_prochaine_inscription($im){
        $prochaines_inscriptions = DB::select('
            SELECT
                DISTINCT ON(
                    id_au,
                    id_etudiants
                )
                id_au,
                intitule,
                id_etudiants,
                im,
                nom_etudiant,
                prenoms,
                id_niveau,
                nom_niveau,
                statut_au_suivante,
                id_niveau_suivant,
                nom_niveau_suivant
            FROM resultats_definitifs
            WHERE im = ?
            ORDER BY id_au desc, id_etudiants
            LIMIT 1

        ',[$im]);

        if(!empty($prochaines_inscriptions)){
            $prochaine_inscription = $prochaines_inscriptions[0];
            if($prochaine_inscription->statut_au_suivante == 'exclu')
                throw new Exception('ERREUR: Cet étudiant ne peut plus s\'inscrire car déjà exclu');

            else if($prochaine_inscription->statut_au_suivante == 'passant' && $prochaine_inscription->id_niveau_suivant == null)
                throw new Exception('MESSAGE: Cet étudiant a déjà été admis en niveau supérieur et n\'est plus du ressort du service de la scolarité. Veuillez contacter le Service du troisième cycle court');

            return $prochaine_inscription;
        }
        else{
            throw new Exception('ERREUR: Aucune iformation disponible sur la prochaine inscription de cet étudiant');
        }
    }

    public static function verifier_inscription_ue_ec($matricule, $barcode, $id_au){
        $values = explode("-", $barcode);
        $id_ue_ec = $values[0];
        $inscription = DB::select("
            select * from v_check_inscription_ue_ec where id_ue_ec=? and im=? and id_au=?
        ", [$id_ue_ec, $matricule, $id_au]);
        if(empty($inscription))
            return false;
        return true;
    }

    //annulation inscription
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
