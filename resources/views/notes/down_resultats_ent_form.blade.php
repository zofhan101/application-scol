@extends('app.app_layout')
@section('title', 'Téléchargement du CSV pour l\'export ENT')

@section('content')
<div class="pagetitle">
    <h1>Téléchargement du CSV pour l'export ENT</h1>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
              <div class="card-body">
                <!-- Vertical Form -->
                <form class="row g-3" action="{{ route('notes.down_resultats_ent') }}" method="POST" id="form">
                    @csrf

                    <div class="col-12">
                        <label for="inputAddress" class="form-label">Année Universitaire</label>
                            @error('id_au')
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ $message }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @enderror
                            <select class="form-select" aria-label="Default select example" id="au" name="id_au">
                                @foreach($aus as $au)
                                    <option value="{{ $au->id_au }}">{{ $au->intitule }}</option>
                                @endforeach
                            </select>

                    </div>

                  <div class="text-center d-flex">
                    <button type="submit" class="w-100 btn btn-primary" id="all">Télécharger le fichier CSV</button>

                  </div>
                </form>


              </div>
            </div>

        </div>

    </div>
</section>
@endsection
