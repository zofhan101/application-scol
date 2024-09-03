@extends('app.app_layout')
@section('title', 'Accueil facmed-scol')

@section('content')
<div class="pagetitle">
      <h1>Bienvenue</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="#">Accueil</a></li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <div class="card">
            <div class="card-body">
              <h5 class="card-title">Soyez le bienvenu dans Facmed-scol</h5>
              {{ Auth::user()->name }}
            </div>
          </div><!-- End Default Card -->

@endsection