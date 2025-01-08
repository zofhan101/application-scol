@extends('app.app_layout')
@section('title', 'Obtenir des relevés de notes')

@section('content')
<div class="pagetitle">
    <h1>Obtenir des relevés de notes</h1>
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
                <form class="row g-3" action="{{ route('notes.get_releve_notes') }}" method="POST" id="form">
                    @csrf

                    <div class="col-12">
                        <label for="inputAddress" class="form-label">Numéro matricule</label>
                            @error('im')
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ $message }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @enderror
                            <div class="col-12">
                                <input type="text" name="im" class="form-control">
                            </div>

                    </div>

                  <div class="text-center d-flex">
                    <button type="submit" class="w-100 btn btn-primary" id="all">Obtenir les relevés de notes</button>

                  </div>
                </form>


              </div>
            </div>

        </div>

    </div>
</section>
@endsection
