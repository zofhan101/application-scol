<?php

namespace App\Http\Controllers;

use App\Models\ClotureModel;
use Illuminate\Http\Request;
use Exception;
class ClotureInscriptionController extends Controller
{
    public function refreshViews()
    {
        try {
            $result = ClotureModel::refreshMaterialViews();
            return response()->json(['message' => $result], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}