@extends('app.app_layout')
@section('title', 'Transfert  - infos sur les parents')

@section('content')
<div class="pagetitle">
    <h1>Informations sur les parents</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accueil')}}">Accueil</a></li>
        <li class="breadcrumb-item">Transfert</li>
        <li class="breadcrumb-item active">Informations sur les parents</li>
      </ol>
    </nav>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-12">

            <div class="card">
              <div class="card-body">

                <!-- Vertical Form -->
                <form class="row g-3" action="{{  route('form_parents_transfert') }}" method="POST" >
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
                    <input type="text" class="form-control" id="inputAddress" name="nom_pere" value="{{ old('nom_pere') }}">
                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Profession</label>
                        @error('profession_pere')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <input type="text" class="form-control" id="inputAddress" name="profession_pere" value="{{ old('profession_pere') }}">
                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Contact</label>
                    @error('contact_pere')
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ $message }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @enderror
                    <input type="text" class="form-control" id="inputAddress" name="contact_pere" value="{{ old('contact_pere') }}">

                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Adresse</label>
                        @error('adresse_pere')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <input type="text" class="form-control" id="inputAddress" name="adresse_pere" value="{{ old('adresse_pere') }}">
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
                    <input type="text" class="form-control" id="inputAddress" name="nom_mere" value="{{ old('nom_mere') }}">
                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Profession</label>
                        @error('profession_mere')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <input type="text" class="form-control" id="inputAddress" name="profession_mere" value="{{ old('profession_mere') }}">
                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Contact</label>
                    @error('contact_mere')
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ $message }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @enderror
                    <input type="text" class="form-control" id="inputAddress" name="contact_mere" value="{{ old('contact_mere') }}">

                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Adresse</label>
                        @error('adresse_mere')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <input type="text" class="form-control" id="inputAddress" name="adresse_mere" value="{{ old('adresse_mere') }}">
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
