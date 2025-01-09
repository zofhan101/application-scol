@extends('app.app_layout')
@section('title', 'Recherche d\'un étudiant')

@section('content')
<div class="pagetitle" id="pagetitle">
    <h1>Résultats de recherche</h1>

    @isset($error)
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle me-1"></i>
            {{ $error }}
        </div>
    @endisset
</div>

<section class="section">
    <div class="row">
        <div class="col-12">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">Informations et notes de l'étudiant</h5>
                <table class="table">
                    <tr>
                        <th>Nom</th>
                        <td>{{ $etudiant->nom }}</td>
                    </tr>

                    <tr>
                        <th>Prénoms</th>
                        <td>{{ $etudiant->prenoms }}</td>
                    </tr>

                    <tr>
                        <th>IM</th>
                        <td>{{ $etudiant->im }}</td>
                    </tr>


                    <tr>
                        <th>Mention (parcours)</th>
                        <td>{{ $parcours->nom_parcours }}</td>
                    </tr>


                    <tr>
                        <th>Date de première insription</th>
                        <td>{{ $etudiant->date_premiere_inscription }}</td>
                    </tr>

                </table>

                @if(!empty($resultats))

                    @foreach ($resultats as $resultat)
                        <h5 class="card-title">Résultats {{ $resultat[0]->nom_niveau }}({{ $resultat[0]->intitule }})</h5>

                        <table class="table">
                            @foreach ($resultat as $ligne)
                                <tr>
                                    <td>{{ $ligne->nom_unite_enseignement }}</td>
                                    <td>{{ $ligne->note_ue }} / {{ $ligne->note_max }}</td>
                                </tr>
                            @endforeach
                        </table>

                    @endforeach
                    <br>
                    <b >
                        <div>TOTAL: {{ round($resultat[0]->total, 2) }}  / {{ round($resultat[0]->total_max, 2) }}</div>
                        <div>TOTAL: {{  round($resultat[0]->moyenne, 2) }} / {{ round($resultat[0]->note_max, 2) }}</div>
                        <div>DECISION: {{ $resultat[0]->decision }}</div>

                    </b>
                @endif


            </div>
        </div>

    </div>
</section>

@endsection

