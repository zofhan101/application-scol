@extends('app.app_layout')
@section('title', 'Résultats d\évaluation')

@section('content')

<div class="pagetitle text-center">
    <h1>Résulats {{ $eval->nom_session_examen }}</h1>
    <h1>{{ $parcours->nom_parcours }} - {{ $niveau->nom_niveau }}</h1>
    <h1>Année Universitaire {{ $au->intitule }}</h1>
</div>
@php
    $etu1 = $resultats[0];

@endphp

<section class="section">
    <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">

              <!-- Default Table -->
              <table class="table">
                <thead>
                  <tr>
                    <th rowspan="2" class ="sticky-gauche alignement-vertical-centre">IM</th>
                    <th rowspan="2" class="alignement-vertical-centre">Nom</th>
                    <th rowspan="2" class="alignement-vertical-centre">Prénoms</th>
                    @foreach($etu1->ues as $ue)
                        <th colspan="{{ count($ue->ecs)+2 }}" class ="alignement-vertical-centre">{{ $ue->nom_ue }}</th>

                    @endforeach
                  </tr>

                  <tr>
                    @foreach($etu1->ues as $ue)
                        @foreach ($ue->ecs as $ec)
                            <th>{{ $ec->nom_ec }}</th>
                        @endforeach
                        <th>Note U.E.</th>
                        <th>Validation</th>
                    @endforeach
                  </tr>

                </thead>
                <tbody>
                    @foreach($resultats as $resultat)
                        <tr>
                            <td class ="sticky-gauche">  {{ $resultat->im }} </td>
                            <td>{{ $resultat->nom }}</td>
                            <td>{{ $resultat->prenoms }}</td>
                            @foreach($resultat->ues as $ue)
                                @foreach ($ue->ecs as $ec)
                                    <td> {{ $ec->note_ec }}</td>
                                @endforeach
                                <td> {{ $ue->note_ue }} </td>
                                <td> {{ $ue->validation }} </td>
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
