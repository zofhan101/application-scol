@extends('app.app_layout')
@section('title', 'Choix de parcours')

@section('content')
<div class="pagetitle">
    <h1>Choix de parcours</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accueil')}}">Accueil</a></li>
        <li class="breadcrumb-item">Inscriptions</li>
        <li class="breadcrumb-item active">Choix de parcours</li>
      </ol>
    </nav>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-6">

            <div class="card">
              <div class="card-body">
                <h5 class="card-title">
                   Cet étudiant pourra s'inscrire dans l'un des mentions ou parcours suivants
                </h5>
                <!-- Vertical Form -->
                <form class="row g-3" action="inscription/parcours" method="POST">
                    @csrf

                  <div class="col-12">
                    @error('parcours')
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ $message }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @enderror
                    <select class="form-select" aria-label="Default select example" name="parcours">
                        @foreach($parcours as $parcour)
                            <option value="{{ $parcour->id_parcours }}">{{ $parcour->nom_parcours }}</option>
                        @endforeach
                    </select>
                  </div>

                  <div class="col-12">
                    @error('matricule')
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ $message }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @enderror
                    <label for="inputAddress" class="form-label">Matricule</label>
                    <input type="text" class="form-control" id="inputAddress" name="matricule">
                  </div>

                  <div class="text-center">
                    <button type="submit" class="btn btn-primary">Suivant</button>
                  </div>
                </form><!-- Vertical Form -->


              </div>
            </div>

        </div>

    </div>
</section>
@endsection
