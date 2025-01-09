<?php

namespace App\Models\AU;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use Exception;

class AU extends Model
{
    use HasFactory;

    protected $table = 'au';
    protected $primaryKey = 'id_au';

    protected $fillable = [
       'intitule'
    ];

    public static function get_concours_paces($id_au){
        $examens = DB::select("
            select epa.id_examen_par_au, se.id_session_examen, se.nom_session_examen, se.type_session
            from examen_par_au as epa
            join session_examen as se on epa.id_session_examen = se.id_session_examen
            where epa.id_au = ? and se.type_session = 'conc'
        ",[$id_au]);

        if(empty($examens))
            throw new Exception("Aucune session de concours n'a encore été programmée pour cette AU");
        return $examens[0];
    }

    public static function get_liste_evaluations($id_au){
        $examens = DB::select("
            select epa.id_examen_par_au, se.id_session_examen, se.nom_session_examen, se.type_session
            from examen_par_au as epa
            join session_examen as se on epa.id_session_examen = se.id_session_examen
            where epa.id_au = ? and se.type_session = 'eval'
        ",[$id_au]);

        if(empty($examens))
            throw new Exception('aucune session d\'examen n\'a encore été selectionnée pour l\'AU demandée');
        return $examens;
    }

    public static function get_examen_by_id($id_examen_par_au){
        $examens = DB::select('
            select epa.id_examen_par_au, se.id_session_examen, se.nom_session_examen, se.type_session
            from examen_par_au as epa
            join session_examen as se on epa.id_session_examen = se.id_session_examen
            where epa.id_examen_par_au = ?
        ',[$id_examen_par_au]);
        return $examens[0];
    }

    public static function get_liste_examen_by_id_au($id_au){
        $examens = DB::select('
            select epa.id_examen_par_au, se.id_session_examen, se.nom_session_examen, se.type_session
            from examen_par_au as epa
            join session_examen as se on epa.id_session_examen = se.id_session_examen
            where epa.id_au = ?
        ',[$id_au]);

        return $examens;
    }

    public static function get_liste_examens(){
        $au = AU::get_au_en_cours();
        $examens = DB::select('
            select epa.id_examen_par_au, se.id_session_examen, se.nom_session_examen, se.type_session
            from examen_par_au as epa
            join session_examen as se on epa.id_session_examen = se.id_session_examen
            where epa.id_au = ?
        ',[$au->id_au]);

        if(empty($examens))
            throw new Exception('aucune session d\'examen n\'a encore été selectionnée pour l\'année en cours');
        return $examens;
    }

    public static function get_au_fermees(){
        $au_fermees = AU::where('cloture','!=', null)->get();
        return $au_fermees;
    }

    public static function clore_au($id_au){
        $au =  AU::find($id_au);
        if($au != null){
            $au->cloture = date('Y-m-d');
            $au->save();
        }

    }

    public static function get_au_en_cours(){
        $au_en_cours = AU::where('cloture','=', null)->get();

        if($au_en_cours->isEmpty()){
            throw new Exception('Désolé! Aucune A.U. n\'est en cours. Veuillez contacter votre administrateur pour en ouvrir une.');
        }
        else{
            return $au_en_cours[0];
        }
    }
}
