@extends('app.app_layout')
@section('title', 'Inscription - liste des inscrits')

@section('content')
<div class="pagetitle">
    <h1>Sélectionnez l'A.U., le parcours et le niveau</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accueil')}}">Accueil</a></li>
        <li class="breadcrumb-item">Inscriptions</li>
        <li class="breadcrumb-item active">Liste des inscrits</li>
      </ol>
    </nav>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-12">

            <div class="card">
              <div class="card-body">


                @if(session()->has('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Vertical Form -->
                <form class="row g-3" action="{{route('liste_inscrits')}}" method="POST" >
                    @csrf

                    <div class="col-12">
                        <label for="inputAddress" class="form-label">Année universitaire</label>
                            @error('annee_universitaire')
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ $message }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @enderror
                            <select class="form-select" aria-label="Default select example" name="annee_universitaire">
                                @foreach($aus as $au)
                                    <option value="{{ $au->id_au }}">{{ $au->intitule }}</option>
                                @endforeach
                            </select>

                    </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Mention ou parcours</label>
                        @error('parcours')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    <select class="form-select" aria-label="Default select example" name="parcours" id="id_parcours">
                        <option value="">-- Choisir un parcours --</option>
                        @foreach($parcours as $parcour)
                            <option value="{{ $parcour->id_parcours }}">{{ $parcour->nom_parcours }}</option>
                        @endforeach
                    </select>
                  </div>

                  <div class="col-12">
                    <label for="inputAddress" class="form-label">Niveau</label>
                        @error('niveau')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror

                        <select class="form-select" aria-label="Default select example" name="niveau" id="id_niveau">

                        </select>
                  </div>

                  <div class="text-center">
                    <button type="submit" class="btn btn-primary">Suivant</button>
                  </div>
                </form><!-- Vertical Form -->


              </div>
            </div>

        </div>

    </div>
</section>
<script>
    var id_parcours = document.getElementById('id_parcours');
    var id_niveau = document.getElementById('id_niveau');
    var url = "{{route('get_niveaux_parcours')}}";

    id_parcours.addEventListener('change', function(){
        let formdata = new FormData();
        formdata.append("id_parcours", this.value);
        fetch(url,{
            method: 'POST',
            body: formdata
        })
        .then(response => response.json())
        .then(data=>{
            id_niveau.innerHTML = '';
            for(let i=0; i<data.length; i++){
                let niveau = data[i];
                let newOption = document.createElement('option');
                newOption.value = niveau.id_niveau;
                newOption.textContent = niveau.nom_niveau;

                // Ajout du nouveau paragraphe au parent
                id_niveau.appendChild(newOption);

            }
        })
        .catch(
            error =>{
                console.error('Erreur: ', error);
            }
        );
    });
</script>
@endsection
