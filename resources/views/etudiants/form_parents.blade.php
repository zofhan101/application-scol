@extends('app.app_layout')
@section('title', 'Inscription - infos sur les parents')

@section('content')
<div class="pagetitle">
    <h1>Informations sur les parents</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accueil')}}">Accueil</a></li>
        <li class="breadcrumb-item">Mise à jour</li>
        <li class="breadcrumb-item active">Informations sur les parents</li>
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
                <form class="row g-3" action="{{  route('form_parents_modif') }}" method="POST" >
                    @csrf
                    <h5 class="card-title">Père</h5>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Nom du père</label>
                        @error('nom_pere')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <input type="text" class="form-control" id="inputAddress" name="nom_pere" value="{{ $etu->pere }}">
                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Profession</label>
                        @error('profession_pere')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <input type="text" class="form-control" id="inputAddress" name="profession_pere" value="{{ $etu->profession_pere }}">
                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Contact</label>
                    @error('contact_pere')
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ $message }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @enderror
                    <input type="text" class="form-control" id="inputAddress" name="contact_pere" value="{{ $etu->tel_pere }}">

                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Adresse</label>
                        @error('adresse_pere')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <input type="text" class="form-control" id="inputAddress" name="adresse_pere" value="{{ $etu->adresse_pere }}">
                  </div>

                  <h5 class="card-title">Mère</h5>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Nom de la mère</label>
                        @error('nom_mere')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <input type="text" class="form-control" id="inputAddress" name="nom_mere" value="{{ $etu->mere }}">
                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Profession</label>
                        @error('profession_mere')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <input type="text" class="form-control" id="inputAddress" name="profession_mere" value="{{ $etu->profession_mere }}">
                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Contact</label>
                    @error('contact_mere')
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ $message }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @enderror
                    <input type="text" class="form-control" id="inputAddress" name="contact_mere" value="{{ $etu->tel_mere }}">

                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Adresse</label>
                        @error('adresse_mere')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <input type="text" class="form-control" id="inputAddress" name="adresse_mere" value="{{ $etu->adresse_mere }}">
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
