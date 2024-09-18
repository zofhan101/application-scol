<?php

namespace App\Models\inscription;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\AU\AU;


class Etudiant extends Model
{
    use HasFactory;
    use HasFactory;
    protected $primaryKey = 'id_etudiants';
    protected $table="etudiants";
    protected $fillable = [
        'im',
        'nom',
        'prenoms',
        'sexe',
        'date_premiere_inscription',
        'date_naissance',
        'lieu_naissance',
        'type_piece_identite',
        'num_piece_identite',
        'date_delivrance',
        'lieu_delivrance',
        'adresse',
        'telephone',
        'pere',
        'profession_pere',
        'tel_pere',
        'adresse_pere',
        'mere',
        'profession_mere',
        'tel_mere',
        'adresse_mere',
        'etablissement_transfert',
        'niveau_au_transfert',
        'au_transfert',
        'est_officier',
        'annee_bacc',
        'id_serie',
        'id_province',
        'id_nationalite',
        'id_parcours',
        'id_agent'
    ];

    public static function check_inscription($etu){
        
    }

    public static  function inscrire($new_etu){
        $user = Auth::user();
        $id_user = $user->id;
        $au = AU::get_au_en_cours();

        //recuperation du niveau L1
        $l1 = DB::Select('select * from niveau where rang = ?',[1]);


        DB::transaction(function () use($new_etu, $id_user,$au, $l1){
            //créationd de l'étudiant
        $etu = Etudiant::create([
            'im'=>$new_etu->matricule,
            'nom'=>$new_etu->nom_candidat,
            'prenoms'=>$new_etu->prenom_candidat,
            'sexe'=>$new_etu->sexe,
            'date_premiere_inscription'=>date('Y-m-d'),
            'date_naissance'=>$new_etu->dtn,
            'lieu_naissance'=>$new_etu->ldn,
            'type_piece_identite'=>$new_etu->type_pi,
            'num_piece_identite'=>$new_etu->num_pi,
            'date_delivrance'=>$new_etu->date_delivrance,
            'lieu_delivrance'=>$new_etu->lieu_delivrance,
            'adresse'=>$new_etu->adresse,
            'telephone'=>$new_etu->contact,
            'pere'=>$new_etu->nom_pere,
            'profession_pere'=>$new_etu->profession_pere,
            'tel_pere'=>$new_etu->contact_pere,
            'adresse_pere'=>$new_etu->adresse_pere,
            'mere'=>$new_etu->nom_mere,
            'profession_mere'=>$new_etu->profession_mere,
            'tel_mere'=>$new_etu->contact_mere,
            'adresse_mere'=>$new_etu->adresse_mere,
            'est_officier'=>$new_etu->est_officier,
            'annee_bacc'=>$new_etu->annee_bacc,
            'id_serie'=>$new_etu->id_serie,
            'id_province'=>$new_etu->id_province,
            'id_nationalite'=>$new_etu->id_nationalite,
            'id_parcours'=>$new_etu->id_parcours,
            'id_agent'=>$id_user
        ]);

        //enregistrement des autres inscription
        if($new_etu->autre_etab !== null){
            Autre_inscription::create([
                'id_au'=>$au->id_au,
                'id_etudiants'=>$etu->id_etudiants,
                'etablissement'=>$new_etu->autre_etab,
                'niveau'=>$new_etu->autre_ae,
            ]);
        }

        //enregistrement de l'inscription
        Inscription::create([
            'date_inscription'=>date('Y-m-d'),
            'id_agent_inscription'=>$id_user,
            'id_etudiant'=>$etu->id_etudiants,
            'id_au'=>$au->id_au,
            'id_niveau'=>$l1[0]->id_niveau
        ]);

        // bloquer la possibilité de s'inscrire à nouveau pour cette personne

        $maj = DB::update('update selectionnes set est_inscrit = true where num_bacc =? and id_au = ?',[$new_etu->num_bacc, $au->id_au]);

     });

    }
}

