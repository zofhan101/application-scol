@extends('app.app_layout')
@section('title', 'Mise à jour - infos sur le baccalauréat')

@section('content')
<div class="pagetitle">
    <h1>Informations sur le baccauréat</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accueil')}}">Accueil</a></li>
        <li class="breadcrumb-item">Mise à jours</li>
        <li class="breadcrumb-item active">Informations sur le baccauréat</li>
      </ol>
    </nav>
</div>

@php
    $etu = session('etu_modif');
@endphp

<section class="section">
    <div class="row">
        <div class="col-lg-12">

            <div class="card">
              <div class="card-body">
                <!-- Vertical Form -->
                <form class="row g-3" action="{{ route('form_bacc_modif') }}" method="POST" >
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
                                    <option value="{{ $serie->id_serie }}" @if($serie->id_serie == $etu->id_serie) selected @endif>{{ $serie->nom_serie }}</option>
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
                            <option value="{{ $province->id_province }}" @if($province->id_province == $etu->id_province) selected @endif>{{ $province->nom_province }}</option>
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
                    <input type="text" class="form-control" id="inputAddress" name="annee_bacc" value="{{ $etu->annee_bacc }}">
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
