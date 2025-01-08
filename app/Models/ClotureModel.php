<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class ClotureModel
{
    public static function refreshMaterialViews()
    {
        $views = ['v_inscrits2', 'v_liste_ue_ec_avec_nbr_inscrits'];

        foreach ($views as $view) {
            DB::statement("REFRESH MATERIALIZED VIEW  {$view}");
        }

        return true;
    }
}