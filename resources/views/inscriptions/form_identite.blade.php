@extends('app.app_layout')
@section('title', 'Inscription - identité de l\'étudiant')

@section('content')
<div class="pagetitle">
    <h1>Identité de l'étudiant</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accueil')}}">Accueil</a></li>
        <li class="breadcrumb-item">Inscriptions</li>
        <li class="breadcrumb-item active">Identité de l'étudiant</li>
      </ol>
    </nav>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-6">

            <div class="card">
              <div class="card-body">
                <!-- Vertical Form -->
                <form class="row g-3" action="form_identite" method="POST" >
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
                                    <option value="{{ $nationalite->id_nationalites }}">{{ $nationalite->nom_nationalite }}</option>
                                @endforeach
                            </select>

                    </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">CIN ou Passeport</label>
                        @error('p_identite')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <select name="type_pi" id="">
                        <option value="">Choir un type de pièce d'identité</option>
                        <option value="cin">CIN</option>
                        <option value="pass">Passeport</option>

                    </select>
                    <input type="text" class="form-control" id="inputAddress" name="p_identite" value="{{ old('p_identite') }}" placeholder="Numéro">
                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Date de délivrance</label>
                        @error('date_delivrance')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <input type="date" class="form-control" id="inputAddress" name="date_delivrance" value="{{ old('date_delivrance') }}">
                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Lieu de délivrance</label>
                    @error('lieu_delivrance')
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ $message }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @enderror
                        <input type="text" class="form-control" id="inputAddress" name="lieu_delivrance" value="{{ old('lieu_delivrance') }}">
                  </div>


                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Adresse</label>
                        @error('adresse')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <input type="text" class="form-control" id="inputAddress" name="adresse" value={{ old('adresse') }}>
                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Contact</label>
                        @error('contact')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <input type="text" class="form-control" id="inputAddress" name="contact" value={{ old('contact') }}>
                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Adresse E-mail</label>
                        @error('email')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <input type="text" class="form-control" id="inputAddress" name="email" value="{{ old('email') }}">
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
