@extends('app.app_layout')
@section('title', 'Mise à jour - identité de l\'étudiant')

@section('content')
<div class="pagetitle">
    <h1>Identité de l'étudiant</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accueil')}}">Accueil</a></li>
        <li class="breadcrumb-item">Mise à jour</li>
        <li class="breadcrumb-item active">Identité de l'étudiant</li>
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
                <form class="row g-3" action="{{ route('form_identite_modif') }}" method="POST" enctype="multipart/form-data" >
                    @csrf

                    <div class="col-12">
                        <label for="inputAddress" class="form-label">Nationalite</label>
                            @error('nationalite')
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ $message }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @enderror
                            <select class="form-select" aria-label="Default select example" name="nationalite">
                                @foreach($nationalites as $nationalite)
                                    <option value="{{ $nationalite->id_nationalites }}" @if($etu->id_nationalite == $nationalite->id_nationalites) selected @endif>{{ $nationalite->nom_nationalite }}</option>
                                @endforeach
                            </select>

                    </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Numéro CIN ou Passeport</label>
                        @error('p_identite')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <select name="type_pi" id="">
                        <option value="">Choir un type de pièce d'identité</option>
                        <option value="cin"@if($etu->type_piece_identite ==='cin') selected @endif>CIN</option>
                        <option value="pass" @if($etu->type_piece_identite ==='pass') selected @endif>Passeport</option>

                    </select>
                    <input type="text" class="form-control" id="inputAddress" name="p_identite" value="{{ $etu->num_piece_identite }}" placeholder="Numéro">
                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Date de délivrance</label>
                        @error('date_delivrance')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <input type="date" class="form-control" id="inputAddress" name="date_delivrance" value="{{ $etu->date_delivrance }}">
                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Lieu de délivrance</label>
                    @error('lieu_delivrance')
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ $message }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @enderror
                        <input type="text" class="form-control" id="inputAddress" name="lieu_delivrance" value="{{ $etu->lieu_delivrance }}">
                  </div>


                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Adresse</label>
                        @error('adresse')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <input type="text" class="form-control" id="inputAddress" name="adresse" value="{{ $etu->adresse }}">
                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Contact</label>
                        @error('contact')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <input type="text" class="form-control" id="inputAddress" name="contact" value="{{ $etu->telephone }}">
                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Adresse E-mail</label>
                        @error('email')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <input type="text" class="form-control" id="inputAddress" name="email" value="{{ $etu->email }}">
                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Photo d'identité (2Mo max)</label>
                        @error('photo')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <input type="file" class="form-control" name="photo" value="{{ old('photo') }}">
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
