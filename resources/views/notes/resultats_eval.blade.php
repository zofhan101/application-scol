@extends('app.app_layout')
@section('title', 'Résultats d\évaluation')

@section('content')

<div class="pagetitle text-center">
    <h1>Résulats {{ $eval->nom_session_examen }}</h1>
    <h1>{{ $parcours->nom_parcours }} - {{ $niveau->nom_niveau }}</h1>
    <h1>Année Universitaire {{ $au->intitule }}</h1>
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
                    @foreach($resultats[0] as $entete)
                        <th class="text-centers">{{ $entete }}</th>
                    @endforeach
                  </tr>
                </thead>
                <tbody>
                    @foreach($resultats[1] as $valeur)
                        <tr>
                            @foreach ($valeur as $element)
                                <td class="text-center">{{ $element }}</td>
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
