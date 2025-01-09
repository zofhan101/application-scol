@extends('app.app_layout')
@section('title', 'Liste de repêchage')

@section('content')

<div class="pagetitle text-center">
    <h1>Liste de repêchage des évaluations </h1>
    <h1>{{ $parcours->nom_parcours }} - {{ $niveau->nom_niveau }}</h1>
    <h1>Année Universitaire {{ $au->intitule }}</h1>
</div>
@php
    $ligne1 = $resultats[0];

    $cles = array_keys(get_object_vars($ligne1));
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
                    @foreach($cles as $cle)
                        <th>{{ $cle }}</th>

                    @endforeach
                  </tr>
                </thead>
                <tbody>
                    @foreach($resultats as $resultat)
                        @php
                            $values = array_values(get_object_vars($resultat));
                        @endphp
                        <tr>
                            @foreach ($values as $value )
                                <td>{{ $value }}</td>
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
