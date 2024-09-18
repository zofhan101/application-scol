@extends('app.app_layout')
@section('title', 'Vérification d\'admission')

@section('content')
<div class="pagetitle">
    <h1>Inscription en PACES</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accueil')}}">Accueil</a></li>
        <li class="breadcrumb-item">Inscriptions</li>
        <li class="breadcrumb-item active">Vérification d'admission</li>
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
                <form class="row g-3" action="{{ route('verifier_admission') }}" method="GET" enctype="multipart/form-data">
                    @csrf

                  <div class="col-12">
                        <label for="inputNanme4" class="form-label">Numéro d'inscription au baccalauréat</label>
                        @if(session()->has('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @error('num_bacc')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                            <input type="text" class="form-control" id="inputEmail4" name="num_bacc">
                  </div>
                  <div class="text-center">
                    <button type="submit" class="btn btn-primary">Vérifier</button>
                  </div>
                </form><!-- Vertical Form -->


              </div>
            </div>

        </div>
    </div>
</section>
@endsection
