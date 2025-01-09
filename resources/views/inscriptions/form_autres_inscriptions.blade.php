@extends('app.app_layout')
@section('title', 'Inscription - autres inscriptions')

@section('content')
<div class="pagetitle">
    <h1>Informations sur les autres inscription</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accueil')}}">Accueil</a></li>
        <li class="breadcrumb-item">Inscriptions</li>
        <li class="breadcrumb-item active">Autres inscriptions</li>
      </ol>
    </nav>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-6">

            <div class="card">
              <div class="card-body">
                <!-- Vertical Form -->
                <form class="row g-3" action="finaliser" method="POST" >
                    @csrf

                    <div class="col-12">
                        <label for="inputAddress" class="form-label">Etablissement</label>
                            @error('serie')
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ $message }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @enderror
                            <input type="text" class="form-control" id="inputAddress" name="autre_etablissement" value="{{ old('autre_etablissement') }}">

                    </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Année d'étude</label>
                        @error('aautre_annee_etude')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <input type="text" class="form-control" id="inputAddress" name="autre_annee_etude" value="{{ old('autre_annee_etude') }}">

                  </div>


                  <div class="text-center">
                    <button type="submit" class="btn btn-primary">Finaliser l'inscription</button>
                  </div>
                </form><!-- Vertical Form -->


              </div>
            </div>

        </div>

    </div>
</section>
@endsection
