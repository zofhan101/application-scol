@extends('app.app_layout')
@section('title', 'Recherche d\'un étudiant')

@section('content')
<div class="pagetitle" id="pagetitle">
    <h1>Résultats de recherche</h1>

    @isset($error)
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle me-1"></i>
            {{ $error }}
        </div>
    @endisset
</div>


@endsection

