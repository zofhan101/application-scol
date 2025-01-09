@extends('app.app_layout')
@section('title', 'Transfert - infos sur le transfert')

@section('content')
<div class="pagetitle">
    <h1>Informations sur le transfert</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accueil')}}">Accueil</a></li>
        <li class="breadcrumb-item">Transfert</li>
        <li class="breadcrumb-item active">Informations sur le transfert</li>
      </ol>
    </nav>
</div>


<section class="section">
    <div class="row">
        <div class="col-lg-12">

            <div class="card">
              <div class="card-body">
                 @if(session()->has('error'))
                     <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                 @endif

                 @if(session()->has('success'))
                     <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                 @endif
                <!-- Vertical Form -->
                <form class="row g-3" action="{{ route('transfert_etu') }}" method="POST" >
                    @csrf

                    <div class="col-12">
                        <label for="inputAddress" class="form-label">Etablissement d'origine</label>
                            @error('etablissement')
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ $message }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @enderror
                            <select class="form-select" aria-label="Default select example" name="etablissement">
                                @foreach($etablissements as $etablissement)
                                    <option value="{{ $etablissement->id_autre_etablissement }}" @if($etablissement->id_autre_etablissement == old('etablissement')) selected @endif >{{ $etablissement->nom_autre_etablissement }}</option>
                                @endforeach
                            </select>
                    </div>

                    <div class="col-12">
                        <label for="inputAddress" class="form-label">Dernière année universitaire passée à l'établissement d'origine</label>
                            @error('annee_universitaire_transfert')
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ $message }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @enderror
                            <select class="form-select" aria-label="Default select example" name="annee_universitaire_transfert">
                                @foreach($aus as $au)
                                    <option value="{{ $au->id_au }}" @if($au->id_au == old('annee_universitaire_transfert')) selected @endif >{{ $au->intitule }}</option>
                                @endforeach
                            </select>

                    </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Dernier niveau effectué à l'établissement d'origine</label>
                        @error('niveau_transfert')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                        <select class="form-select" aria-label="Default select example" name="niveau_transfert">
                            @foreach($niveaux as $niveau)
                                <option value="{{ $niveau->id_niveau }}" @if($niveau->id_niveau == old('niveau_transfert')) selected @endif >{{ $niveau->nom_niveau }}</option>
                            @endforeach
                        </select>

                   </div>

                   <div class="col-12">
                    <label for="inputAddress" class="form-label">Mention ou parcours choisi</label>
                        @error('parcours')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <select class="form-select" aria-label="Default select example" name="parcours">
                        @foreach($parcours as $parcour)
                            <option value="{{ $parcour->id_parcours }}" @if($parcour->id_parcours == old('parcours')) selected @endif>{{ $parcour->nom_parcours }}</option>
                        @endforeach
                    </select>
                  </div>


                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Matricule</label>
                        @error('matricule')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                        <input type="text" class="form-control" id="inputAddress" name="matricule" value="{{ old('matricule') }}">
                  </div>


                  <div class="text-center">
                    <button type="submit" class="w-100 btn btn-primary">Suivant</button>
                  </div>
                </form><!-- Vertical Form -->


              </div>
            </div>

        </div>

    </div>
</section>
@endsection
