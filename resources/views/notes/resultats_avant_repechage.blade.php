@extends('app.app_layout')
@section('title', 'Résultats d\évaluation')

@section('content')
@php
    $etu1 = $resultats[0];

@endphp

<div class="pagetitle text-center">
    <h1>Résulats annuels avant le repêchage A.U. {{ $etu1->intitule }}</h1>
    <h1>{{ $etu1->parcours }} - {{ $etu1->niveau }}</h1>
</div>


<section class="section">
    <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">

              <!-- Default Table -->
              <table class="table">
                <thead>
                  <tr>
                    <th rowspan="3" class="alignement-vertical-centre">Rang</th>
                    <th rowspan="3" class ="sticky-gauche alignement-vertical-centre">IM</th>
                    <th rowspan="3" class="alignement-vertical-centre">Nom</th>
                    <th rowspan="3" class="alignement-vertical-centre">Prénoms</th>
                    <th rowspan="3" class="alignement-vertical-centre">Annulation d'inscription</th>
                    <th rowspan="3" class="alignement-vertical-centre">Total</th>
                    <th rowspan="3" class="alignement-vertical-centre">Total Coefficient</th>
                    <th rowspan="3" class="alignement-vertical-centre">Moyenne</th>
                    <th rowspan="3" class="alignement-vertical-centre">Nombre d'U.E.</th>
                    <th rowspan="3" class="alignement-vertical-centre">U.E. validées</th>
                    <th rowspan="3" class="alignement-vertical-centre">Pourcentage validation</th>
                    <th rowspan="3" class="alignement-vertical-centre">Nombre note eliminatoire</th>
                    <th rowspan="3" class="alignement-vertical-centre demarcation" >Decision</th>


                    @foreach($etu1->evals as $eval)
                        @php
                            $nbr_col = 0;
                            foreach($eval->ues as $ue){
                                $nbr_col += count($ue->ecs)+3;
                            }
                        @endphp
                        <th colspan="{{ $nbr_col }}" class ="alignement-vertical-centre">{{ $eval->nom_session_examen }}</th>
                        <th class="demarcation"></th>
                    @endforeach
                  </tr>

                  <tr>
                    @foreach($etu1->evals as $eval)
                        @foreach($eval->ues as $ue)
                                <th colspan="{{ count($ue->ecs)+3 }}" >{{ $ue->nom_ue }}</th>
                        @endforeach
                        <th class="demarcation"></th>
                    @endforeach
                  </tr>

                  <tr>
                    @foreach($etu1->evals as $eval)
                        @foreach($eval->ues as $ue)
                            @foreach ($ue->ecs as $ec)
                                <th>{{ $ec->nom_ec }}</th>
                            @endforeach
                            <th>Note U.E.</th>
                            <th>Coefficient</th>
                            <th>Validation</th>
                        @endforeach
                        <th class="demarcation"></th>
                    @endforeach
                  </tr>

                </thead>
                <tbody>
                    @foreach($resultats as $resultat)
                        <tr>
                            <td>{{ $resultat->rang }}</td>
                            <td class ="sticky-gauche">  {{ $resultat->im }} </td>
                            <td>{{ $resultat->nom }}</td>
                            <td>{{ $resultat->prenoms }}</td>
                            <td>{{ $resultat->date_annulation }}</td>
                            <td>{{ $resultat->total }}</td>
                            <td>{{ $resultat->total_coefficient }}</td>
                            <td>{{ $resultat->moyenne }}</td>
                            <td>{{ $resultat->nombre_ue }}</td>
                            <td>{{ $resultat->nombre_ue_validees }}</td>
                            <td>{{ $resultat->pourcentage_validation }}</td>
                            <td>{{ $resultat->nombre_note_eliminatoire }}</td>
                            <td class="demarcation">{{ $resultat->decision }}</td>


                            @foreach ($resultat->evals as $eval)

                                @foreach($eval->ues as $ue)
                                    @foreach ($ue->ecs as $ec)
                                        <td> {{ $ec->note_ec }}</td>
                                    @endforeach
                                    <td> {{ $ue->note_ue }} </td>
                                    <td> {{ $ue->coefficient }} </td>
                                    <td> {{ $ue->validation }} </td>
                                @endforeach
                                <td class="demarcation"></td>

                            @endforeach


                        </tr>
                    @endforeach
                </tbody>
              </table>
              <!-- End Default Table Example -->
            </div>
          </div>
        </div>
    </div>
</section>

@endsection
