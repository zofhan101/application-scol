@extends('app.admin_app_layout')
@section('title', 'Définir les examens')

@section('content')
<div class="pagetitle">
    <h1>Définition des examens pour l'année universitaire</h1>

</div><!-- End Page Title -->

<section class="section">
    <div class="col-lg-12">
    <div class="card">
        <div class="card-body">
          {{-- <h5 class="card-title">Vertical Form</h5> --}}
          @if(session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endisset

          @if(session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endisset


          <!-- Vertical Form -->
          <form class="row g-3" action="{{  route('create_exam') }}" method="POST">
            @csrf
                @error('sessions_examen')
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ $message }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @enderror
            <label for="inputNanme4" class="form-label">Examens choisis</label>
            @foreach($examens as $examen)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="sessions_examen[]" value={{ $examen->id_session_examen }}>
                    <label class="form-check-label" for="gridCheck2">
                        {{ $examen->nom_session_examen }}
                    </label>
                </div>
            @endforeach


            <div class="text-center">
              <button type="submit" class="btn btn-primary w-100">Valider</button>
            </div>
          </form><!-- Vertical Form -->

        </div>
    </div>
    </div>
</section>
@endsection
