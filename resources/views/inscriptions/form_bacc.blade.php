@extends('app.app_layout')
@section('title', 'Inscription - infos sur le baccalauréat')

@section('content')
<div class="pagetitle">
    <h1>Informations sur le baccauréat</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accueil')}}">Accueil</a></li>
        <li class="breadcrumb-item">Inscriptions</li>
        <li class="breadcrumb-item active">Informations sur le baccauréat</li>
      </ol>
    </nav>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-6">

            <div class="card">
              <div class="card-body">
                <!-- Vertical Form -->
                <form class="row g-3" action="form_bacc" method="POST" >
                    @csrf

                    <div class="col-12">
                        <label for="inputAddress" class="form-label">Série au baccalauréat</label>
                            @error('serie')
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ $message }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @enderror
                            <select class="form-select" aria-label="Default select example" name="serie">
                                @foreach($series as $serie)
                                    <option value="{{ $serie->id_serie }}">{{ $serie->nom_serie }}</option>
                                @endforeach
                            </select>

                    </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Province d'obtention</label>
                        @error('province')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <select class="form-select" aria-label="Default select example" name="province">
                        @foreach($provinces as $province)
                            <option value="{{ $province->id_province }}">{{ $province->nom_province }}</option>
                        @endforeach
                    </select>
                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Année d'obtention</label>
                        @error('annee_bacc')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <input type="text" class="form-control" id="inputAddress" name="annee_bacc" value="{{ old('annee_bacc') }}">
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
