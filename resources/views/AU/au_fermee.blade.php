@extends('app.app_layout')
@section('title', 'Erreur, aucune A.U. ouverte')

@section('content')
<div class="pagetitle">
    <h1>Année universitaire fermée</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accueil')}}">Accueil</a></li>
        <li class="breadcrumb-item">Année universitaire</li>
        <li class="breadcrumb-item active">Année universitaire fermée</li>
      </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
              {{-- <h5 class="card-title">Vertical Form</h5> --}}

                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Désolé! Aucune A.U. n'est en cours. Veuillez contacter votre administrateur pour en ouvrir une.
                </div>



            </div>
        </div>
        </div>
</section>
@endsection
