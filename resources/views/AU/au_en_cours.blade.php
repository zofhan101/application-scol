@extends('app.admin_app_layout')
@section('title', 'A.U. en cours facmed-scol')

@section('content')
<div class="pagetitle">
    <h1>Année universitaire en cours</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accueil')}}">Accueil</a></li>
        <li class="breadcrumb-item">Année universitaire</li>
        <li class="breadcrumb-item active">Année en cours</li>
      </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
              {{-- <h5 class="card-title">Vertical Form</h5> --}}
              @isset($success)
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ $success }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              @endisset

              <!-- Vertical Form -->
              <form class="row g-3" action="{{ route('cloture_au') }}" method="POST" id="delForm">
                @csrf
                <div class="col-12">
                    @isset($au)
                        <div>
                            <span><strong> Intitulé: </strong></span>{{ $au->intitule }}
                        </div>
                        <div>
                            <span><strong>Ouverture: </strong></span>{{ $au->ouverture }}
                        </div>
                    @endisset

                    @isset($error)
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ $error }}

                        </div>
                    @enderror
                </div>

                @isset($au)
                <div class="text-center">
                    <input type="hidden" name="id_au" value="{{ $au->id_au }}">
                  <button type="submit" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ModalClotureAU">Cloturer</button>
                </div>
                @endisset
              </form><!-- Vertical Form -->

              <div class="card" id="modal">
                <div class="card-body">

                  <!-- Vertically centered Modal -->

                  <div class="modal fade" id="ModalClotureAU" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title">Confirmez vous la cloture de l'A.U. @isset($au){{ $au->intitule  }} @endisset</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <div class="card">
                            <div class="card-body">


                              <!-- General Form Elements -->
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                    <button type="button" class="btn btn-danger" onClick="soumettre()">Confirmer</button>
                                </div>

                            </div>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div><!-- End Vertically centered Modal-->

                </div>
              </div>


            </div>
        </div>
        </div>
</section>
<script>
        document.getElementById('delForm').addEventListener('submit', function(event){event.preventDefault();});

        function soumettre(){
            console.log('soumision');
            document.getElementById('delForm').submit();
        }

</script>
@endsection
