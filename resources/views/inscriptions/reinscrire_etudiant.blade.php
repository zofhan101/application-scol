@extends('app.app_layout')
@section('title', 'réinscription')

@section('content')
<div class="pagetitle">
    <h1>Réinscription d'un étudiant</h1>

</div>

<section class="section">
    <div class="row">
        <div class="col-lg-12">

            <div class="card">
              <div class="card-body">
                <h5 class="card-title">
                   Prochaine inscription de {{ $prochaine_inscription->nom_etudiant }} {{ $prochaine_inscription->prenoms }}
                </h5>

                <table class="table">
                    <thead>
                      <tr>
                        <th scope="col">Matricule</th>
                        <th scope="col">Dernière inscription</th>
                        <th scope="col">Statut pour l'A.U. suivante</th>
                        <th scope="col">Niveau d'inscription</th>

                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <th scope="row">{{ $prochaine_inscription->im }}</th>
                        <td>A.U. {{ $prochaine_inscription->intitule }}</td>
                        <td>{{ $prochaine_inscription->statut_au_suivante }}</td>
                        <td>{{ $prochaine_inscription->nom_niveau_suivant }}</td>
                      </tr>

                    </tbody>
                  </table>

                    <form class="row g-3" action="{{ route('reinscription') }}" method="POST">
                    @csrf
                    <input type="hidden" name="im" value="{{ $prochaine_inscription->im }}">

                  <div class="text-center">
                    <button type="submit" class="btn btn-danger w-100">Inscrire l'étudiant</button>
                  </div>
                </form>


              </div>
            </div>

        </div>

    </div>
</section>
@endsection
