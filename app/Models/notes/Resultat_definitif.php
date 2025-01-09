<?php

namespace App\Models\notes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resultat_definitif extends Model
{
    use HasFactory;
    protected $table = "resultats_definitifs";
    protected $primaryKey = "id_resultat_definitif";

    protected $fillable = [
        //infos sur l' AU, parcours, niveau
        'id_au',
        'intitule',
        'id_parcours',
        'nom_parcours',
        'id_niveau',
        'cycle',
        'nom_niveau',
        'rang',
        'nom_niveau_long',

        //infos sur l'examen
        'id_examen_par_au',
        'id_session_examen',
        'nom_session_examen',

        //infos sur l'etudiant
        'id_etudiants',
        'nom_etudiant',
        'prenoms',
        'date_naissance',
        'lieu_naissance',
        'im',
        'statut',

        //infos sur les résultats étudiant
        'total',
        'moyenne',
        'statut_au_suivante',
        'id_niveau_suivant',
        'nom_niveau_suivant',
        'rang_suivant',
        'cycle_suivant',

        //infos sur l'UE
        'id_ue',
        'note_ue',
        'nom_unite_enseignement'
    ];
}
