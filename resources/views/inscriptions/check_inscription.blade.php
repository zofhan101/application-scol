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

                <!-- Vertical Form -->
                <form class="row g-3" action="{{ route('check_inscription') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                  <div class="col-12">
                        <label for="inputNanme4" class="form-label">Numéro matricule</label>
                        @if(session()->has('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(session()->has('error_inscrit'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error_inscrit') }}
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

                @if(session()->has('error_inscrit'))
                    <div class="text-center">
                        <form action="{{ route('plutot_attestation_inscription') }}" method="POST">
                            @csrf
                            <button type="submit" class="alert alert-success bg-success text-light border-0 alert-dismissible fade show">Délivrer une attestation d'inscription</button>

                        </form>
                    </div>

                @endif
              </div>
            </div>

        </div>
    </div>
</section>
@endsection
