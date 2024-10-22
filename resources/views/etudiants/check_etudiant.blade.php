@extends('app.app_layout')
@section('title', 'Mise à jour des informations étudiant')

@section('content')
<div class="pagetitle">
    <h1>Mise à jour des informations étudiant</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accueil')}}">Accueil</a></li>
        <li class="breadcrumb-item">Etudiant</li>
        <li class="breadcrumb-item active">Mise à jour des informations</li>
      </ol>
    </nav>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-12">

            <div class="card">
              <div class="card-body">

                        @if(session()->has('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                <!-- Vertical Form -->

                <form id="delForm"class="row g-3" action="{{ route('maj_etu_search') }}" method="POST" >
                    @csrf

                    <div class="col-12">
                        <label for="inputNanme4" class="form-label">Numéro matricule</label>

                        @error('matricule')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                            <input type="text" class="form-control" id="matricule" name="matricule">
                  </div>

                  <div class="justify-content-center">
                    <button type="submit" class="w-100 btn btn-primary" data-bs-toggle="modal" data-bs-target="#monmodal">Rechercher</button>
                  </div>
                </form><!-- Vertical Form -->
              </div>
            </div>


        </div>
    </div>


</section>
@endsection
