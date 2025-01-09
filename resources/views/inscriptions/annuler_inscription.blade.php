@extends('app.app_layout')
@section('title', 'Annulation d\'inscription')

@section('content')
<div class="pagetitle">
    <h1>Annulation d'inscription</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accueil')}}">Accueil</a></li>
        <li class="breadcrumb-item">Inscriptions</li>
        <li class="breadcrumb-item active">Annulation d'inscription</li>
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

                @if(session()->has('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                <!-- Vertical Form -->

                <form id="delForm"class="row g-3" action="{{ route('annuler_inscription') }}" method="POST" >
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
                    <button type="submit" class="w-100 btn btn-primary" data-bs-toggle="modal" data-bs-target="#monmodal">Annuler l'inscription</button>
                  </div>
                </form><!-- Vertical Form -->


            {{-- modal --}}
            <div class="card" id="modal">
                <div class="card-body">

                  <!-- Vertically centered Modal -->

                  <div class="modal fade" id="monmodal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="confirmation" ></h5>
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
    </div>


</section>
<script>

    document.getElementById('delForm').addEventListener('submit', function(event){
        event.preventDefault();
        matricule = document.getElementById('matricule');
        val_matricule = matricule.value;

        confirmation = document.getElementById('confirmation');
        confirmation.innerText = "Confirmez-vous l'annulation de l'inscription de l'étudiant numéro "+val_matricule;

    });

    function soumettre(){
        console.log('soumision');
        document.getElementById('delForm').submit();
    }

</script>
@endsection
