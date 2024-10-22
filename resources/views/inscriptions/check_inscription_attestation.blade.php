@extends('app.app_layout')
@section('title', 'Vérification d\'inscription')

@section('content')
<div class="pagetitle">
    <h1>Vérification d'inscription</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accueil')}}">Accueil</a></li>
        <li class="breadcrumb-item">Inscriptions</li>
        <li class="breadcrumb-item active">Vérification d'inscription</li>
      </ol>
    </nav>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-6">

            <div class="card">
              <div class="card-body">

                @isset($success)
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ $success }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endisset

                @isset($error)
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ $error }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endisset

                <!-- Vertical Form -->
                @isset($aus)
                <form class="row g-3" action="{{ route('check_inscription_attestation') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="col-12">
                        <label for="inputNanme4" class="form-label">Année universitaire</label>
                        <select class="form-select" aria-label="Default select example" name="annee_universitaire">
                            <option value="">Choisir une A.U.</option>
                            @foreach($aus as $au)
                                <option value="{{ $au->id_au }}">{{ $au->intitule }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label for="inputNanme4" class="form-label">Numéro matricule</label>
                        @if(session()->has('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @error('matricule')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                            <input type="text" class="form-control" id="inputEmail4" name="matricule">
                  </div>

                  <div class="text-center">
                    <button type="submit" class="btn btn-primary">Vérifier</button>
                  </div>
                </form><!-- Vertical Form -->
                @endisset


              </div>
            </div>

        </div>
    </div>
</section>
@endsection
