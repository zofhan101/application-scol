@extends('app.admin_app_layout')
@section('title', 'Créer A.U. facmed-scol')

@section('content')
<div class="pagetitle">
    <h1>Ouvrir une nouvelle année universitaire</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accueil')}}">Accueil</a></li>
        <li class="breadcrumb-item">Année universitaire</li>
        <li class="breadcrumb-item active">Créer nouvelle année</li>
      </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="col-lg-6">
    <div class="card">
        <div class="card-body">
          {{-- <h5 class="card-title">Vertical Form</h5> --}}
          @isset($success)
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ $success }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endisset

          <!-- Vertical Form -->
          <form class="row g-3" action="ouvrir_au" method="POST">
            @csrf
            <div class="col-12">
              <label for="inputNanme4" class="form-label">Intitulé de l'A.U.</label>
              <input name="intitule" type="text" class="form-control" id="inputNanme4" value="{{ old('intitule') }}">
                @error('intitule')
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ $message }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @enderror
            </div>

            <div class="text-center">
              <button type="submit" class="btn btn-primary">Valider</button>
              <button type="reset" class="btn btn-secondary">Réinitialiser</button>
            </div>
          </form><!-- Vertical Form -->

        </div>
    </div>
    </div>
</section>
@endsection
