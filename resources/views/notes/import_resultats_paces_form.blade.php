@extends('app.app_layout')
@section('title', 'Import résultats PACES')

@section('content')
<div class="pagetitle">
    <h1>Importation des résultats du concours PACES</h1>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-12">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif


            @if(session('manquantes'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    @php
                        $manquantes = session('manquantes');
                    @endphp
                    @foreach ($manquantes as $manquante)

                        <div>
                            {{ $manquante->nom_unite_enseignement }}
                        </div>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif


            @if(session('erreurs_fichier'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    @php
                        $erreurs_fichier = session('erreurs_fichier');
                    @endphp
                    @foreach ($erreurs_fichier as $erreur)

                        <div>
                            {{ $erreur }}
                        </div>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif



            <div class="card">
              <div class="card-body">
                <!-- Vertical Form -->
                <form class="row g-3" action="{{ route('notes.import_resultats_paces') }}" method="POST" enctype="multipart/form-data">
                    @csrf


                    <div class="col-12">
                        <label for="inputAddress" class="form-label">Parcours</label>
                            @error('id_parcours')
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ $message }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @enderror
                            <select class="form-select" aria-label="Default select example" id="parcours" name="parcours">
                                @foreach($parcours as $parcour)
                                    <option value="{{ $parcour->id_parcours }}" >{{ $parcour->nom_parcours }}</option>
                                @endforeach
                            </select>
                    </div>


                    <div class="col-12">
                        <label for="inputAddress" class="form-label">Fichier Excel</label>
                            @error('fichier_excel')
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ $message }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @enderror

                            <input type="file" class="form-control" name="fichier_excel">
                    </div>

                  <div class="text-center">
                    <button type="submit" class="w-100 btn btn-primary">Effectuer l'importation</button>
                  </div>
                </form><!-- Vertical Form -->


              </div>
            </div>

        </div>

    </div>
</section>

<script>

    //refraichissement des niveaux en fonction du parcours sélectionné
    var id_parcours;
    var parcours = document.getElementById('parcours');
    id_parcours = parcours.value;

    parcours.addEventListener('change', rafraichir_niveaux);

    rafraichir_niveaux();

    async function rafraichir_niveaux(){
        //récupération des niveaux correspondants au parcours sélectionné
        let url = "{{route('get_niveaux_parcours')}}";
        id_parcours = parcours.value;
        let formdata = new FormData();
        formdata.append("id_parcours", id_parcours);
        try {
            response = await fetch(url,{
                method: 'POST',
                body: formdata,
                headers: {
                    'Accept': 'application/json',
                    'Cookie': document.cookie,
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            data = await response.json();

            let niveaux = document.getElementById('niveau');
            if(response.ok){
                niveaux.disabled = false;
                niveaux.innerHTML = '';
                let niveau;
                for(let i=0; i<data.length; i++){
                    niveau = data[i];
                    let newOption = document.createElement('option');
                    newOption.value = niveau.id_niveau;
                    newOption.textContent = niveau.nom_niveau;
                    niveaux.appendChild(newOption);
                }
            }
            else{
                if(response.status == 401){
                    if(data.message != undefined){
                        if(data.message == "Unauthenticated."){
                            window.location.href = "{{ route('login') }}";
                        }
                        else if(data.message == 'authentification_contradictoire_necessaire'){
                            window.location.href = "{{ route('authentification_contradictoire.form') }}";
                        }
                        else if(data.message == 'au_fermee'){
                            window.location.href = "{{ route('au_fermee') }}"
                        }
                    }
                }
                niveaux.disabled = true;
                niveaux.innerHTML = "";
                console.error('ERREUR  à la récupération des niveaux: ', data);

                //ajouter un label et y afficher les erreurs
                let div_erreur = document.createElement('div');
                div_erreur.className ="alert alert-danger alert-dismissible fade show";
                div_erreur.setAttribute("role", "alert");
                //div_erreur.textContent += data.message + ';;; ';
                if(data.errors  != undefined){
                    if(data.errors.id_parcours != undefined)
                        div_erreur.textContent += data.errors.id_parcours + ";;; ";
                    if(data.errors.autres != undefined)
                        div_erreur.textContent += data.errors.autres;

                }
                let bouton = document.createElement('button');
                bouton.className = "btn-close";
                bouton.setAttribute("type", "button");
                bouton.setAttribute("role", "alert");
                bouton.setAttribute("data-bs-dismiss", "alert");
                bouton.setAttribute("aria-label", "Close");
                div_erreur.appendChild(bouton);
                document.getElementById("pagetitle").appendChild(div_erreur);

            }

        } catch (error) {
            let niveaux = document.getElementById('niveau');
            niveaux.disabled = true;
            niveaux.innerHTML = "";
            console.error('ERREUR à la récupération des niveaux: ', error);
            alert('ERREUR à la récupération des niveaux:');
        }
    }

</script>
@endsection
