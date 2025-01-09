@extends('app.app_layout')
@section('title', 'Réinscription')

@section('content')
<div class="pagetitle">
    <h1>Réinscription</h1>
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

                @if(session()->has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif


                <!-- Vertical Form -->
                <form class="row g-3" action="{{route('get_prochaine_inscription')}}" method="POST" >
                    @csrf

                    <div class="col-12">
                        <label for="inputAddress" class="form-label">Matricule</label>
                            @error('matricule')
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ $message }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @enderror
                            <input type="text" class="form-control" name="matricule">

                    </div>

                  <div class="text-center">
                    <button type="submit" class="btn btn-primary w-100">Vérifier la prochaine inscription</button>
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
