<?php

namespace App\Imports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\inscription\Etudiant;
use App\Models\notes\Operation_sur_au;
use App\Models\notes\Resultat_definitif;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PacesImport implements ToModel, WithHeadingRow
{
    protected $au;
    protected $parcours;
    protected $paces;
    protected $epa;
    protected $l2;
    protected $matieres;
    protected $user;

    /**
     * Constructeur de la classe
     *
     * @param int $au
     * @param string $parcours
     * @param string $paces
     * @param string $epa
     * @param string $l2
     * @param array $matieres
     */
    public function __construct($au, $parcours, $paces, $epa, $l2, $matieres, $user)
    {
        $this->user = $user;
        $this->au = $au;
        $this->parcours = $parcours;
        $this->paces = $paces;
        $this->epa = $epa;
        $this->l2 = $l2;
        $this->matieres = $matieres;
    }

    /**
     * Convertir une ligne du fichier Excel en un modèle
     *
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {

        $row_indexe = array_values($row);
        //var_dump($row_indexe);
        // Récupérer l'id de l'étudiant à partir de l'IM
        $etudiant = Etudiant::where('im', $row['im'])->first();

        // Déterminer le niveau suivant selon la décision
        $niveau_suivant = null;
        $statut_au_suivante = 'exclu';
        if (strtolower($row['decision']) === 'admis') {
            $niveau_suivant = $this->l2;
            $statut_au_suivante = 'passant';
        } else if (strtolower($row['decision']) === 'ajourne') {
            $niveau_suivant = $this->paces;
            $statut_au_suivante = 'redoublant';
        }


        // Créer une entrée pour chaque matière
        foreach ($this->matieres as $matiere) {
            /*var_dump($matiere);
            echo '<br>';*/
            $note = $row_indexe[$matiere[1]];
            /*var_dump($note);
            echo '<br>';*/
                Resultat_definitif::create([
                    // Infos sur l'AU, parcours, niveau
                    'id_au' => $this->au->id_au,
                    'intitule' => $this->au->intitule,
                    'id_parcours' => $this->parcours->id_parcours,
                    'nom_parcours' => $this->parcours->nom_parcours,
                    'id_niveau' => $this->paces->id_niveau,
                    'cycle' => $this->paces->cycle,
                    'nom_niveau' => $this->paces->nom_niveau,
                    'rang' => $this->paces->rang,
                    'nom_niveau_long' => $this->paces->nom_niveau_long,

                    // Infos sur l'examen
                    'id_examen_par_au' => $this->epa->id_examen_par_au,
                    'id_session_examen' => $this->epa->id_session_examen,
                    'nom_session_examen' => $this->epa->nom_session_examen,

                    // Infos sur l'étudiant
                    'id_etudiants' => $etudiant->id_etudiants,
                    'nom_etudiant' => $row['nom'],
                    'prenoms' => $row['prenoms'],
                    'date_naissance' => Date::excelToDateTimeObject($row['date_de_naissance'])->format('Y-m-d'),
                    'lieu_naissance' => $row['lieu_de_naissance'],
                    'im' => $row['im'],
                    'statut' => $row['statut'],

                    // Infos sur les résultats
                    'total' => $row['total'],
                    'moyenne' => $row['moyenne'],
                    'statut_au_suivante' => $statut_au_suivante,
                    'id_niveau_suivant' => $niveau_suivant->id_niveau ?? null,
                    'nom_niveau_suivant' => $niveau_suivant->nom_niveau ?? null,
                    'rang_suivant' => $niveau_suivant->rang ?? null,
                    'cycle_suivant' => $niveau_suivant->cycle ?? null,

                    // Infos sur l'UE
                    'id_ue' => $matiere[0]->id_unite_enseignement,
                    'note_ue' => $note,
                    'nom_unite_enseignement' => $matiere[0]->nom_unite_enseignement,
                ]);

        }

        Operation_sur_au::enregistrer_import($this->au->id_au, $this->parcours->id_parcours, $this->user->id);
        return null;

    }
}
