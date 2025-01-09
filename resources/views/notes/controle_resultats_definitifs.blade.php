@extends('app.app_layout')
@section('title', 'Controle des résultats définitifs')

@section('content')
<div class="pagetitle">
    <h1>Préparation des résultats définitifs</h1>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            @if(session()->has('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif


            @if(session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif


            <div class="card">
              <div class="card-body">
                <!-- Vertical Form -->
                <form class="row g-3"  id="form" action="{{ route('notes.preparer_resultats_definitifs') }}" method="POST">
                    @csrf


                  <div class="text-center d-flex">
                    <button type="submit" class="btn btn-primary w-100"> Préparer les résultats définitifs</button>
                  </div>
                </form>


              </div>
            </div>



        </div>

    </div>
</section>

@endsection
