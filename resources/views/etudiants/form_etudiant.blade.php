@extends('app.app_layout')
@section('title', 'Mise à jour - infos étudiant')

@section('content')
<div class="pagetitle">
    <h1>Informations sur l'étudiant</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accueil')}}">Accueil</a></li>
        <li class="breadcrumb-item">Etudiant</li>
        <li class="breadcrumb-item active">Informations sur l'étudiant</li>
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
                <form class="row g-3" action="{{ route('form_etudiant_modif') }}" method="POST" >
                    @csrf

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Nom</label>
                        @error('nom')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <input type="text" class="form-control" id="inputAddress" name="nom" value="{{ $etu->nom }}">
                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Prénoms</label>
                        @error('prenoms')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <input type="text" class="form-control" id="inputAddress" name="prenoms" value="{{ $etu->prenoms }}">
                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Sexe</label>
                    @error('sexe')
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ $message }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @enderror
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="sexe" id="gridRadios1" value="m" @if($etu->sexe==='m') checked @endif>
                        <label class="form-check-label" for="gridRadios1">
                          Masculin
                        </label>
                    </div>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="sexe" id="gridRadios2" value="f" @if($etu->sexe==='f') checked @endif>
                        <label class="form-check-label" for="gridRadios2">
                          Féminin
                        </label>
                      </div>
                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Elève officer</label>
                    @error('est_officier')
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ $message }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @enderror
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="est_officier" id="gridRadios1" value="1" @if($etu->est_officier === true) checked @endif >
                        <label class="form-check-label" for="gridRadios1">
                          Oui
                        </label>
                    </div>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="est_officier" id="gridRadios2" value="0" @if($etu->est_officier === false) checked @endif >
                        <label class="form-check-label" for="gridRadios2">
                          Non
                        </label>
                      </div>
                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Date de naissance</label>
                        @error('dtn')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <input type="date" class="form-control" id="inputAddress" name="dtn" value="{{ $etu->date_naissance }}">
                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Lieu de naissance</label>
                        @error('ldn')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <input type="text" class="form-control" id="inputAddress" name="ldn" value="{{ $etu->lieu_naissance }}">
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
