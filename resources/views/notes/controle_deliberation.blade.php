@extends('app.app_layout')
@section('title', 'Controle des délibérations')

@section('content')
<div class="pagetitle">
    <h1>Controle des délibérations</h1>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            @isset($error)
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $error }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endisset

            @isset($success)
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $success }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endisset

            <div class="card">
              <div class="card-body">
                <!-- Vertical Form -->
                <form class="row g-3"  id="form" action="#" method="POST">
                    @csrf

                    <div class="col-12">
                        <label for="inputAddress" class="form-label">Parcours</label>
                            @error('id_parcours')
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ $message }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @enderror
                            <select class="form-select" aria-label="Default select example" id="parcours" name="id_parcours">
                                @foreach($parcours as $parcour)
                                    <option value="{{ $parcour->id_parcours }}" >{{ $parcour->nom_parcours }}</option>
                                @endforeach
                            </select>

                    </div>

                    <div class="col-12">
                        <label for="inputAddress" class="form-label">Niveau</label>
                            @error('id_niveau')
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ $message }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @enderror
                            <select class="form-select" aria-label="Default select example" id="niveau" name="id_niveau" disabled>

                            </select>

                    </div>
                  <div class="text-center d-flex">
                    <button class="btn btn-success w-50" id="generate_button" data-bs-toggle="modal" data-bs-target="#ouverture_modal"> Ouvrir la délibération</button>
                    <button class="btn btn-danger w-50" id="generate_button" data-bs-toggle="modal" data-bs-target="#cloture_modal"> Verrouiller la délibération</button>
                  </div>
                </form><!-- Vertical Form -->


              </div>
            </div>

            {{-- modal d'ouverture --}}

            <div class="modal fade" id="ouverture_modal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="titre_confirmation" >Confirmez-vous l'ouverture de la délibération pour les parcours et niveau sélectionnés?</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <div class="card">
                        <div class="card-body">
                          <!-- General Form Elements -->
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="button" class="btn btn-success" data-bs-dismiss="modal" onclick="ouvrir_deliberation()">Confirmer</button>
                            </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {{-- modal de verrouillage --}}

            <div class="modal fade" id="cloture_modal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="titre_confirmation" >Confirmez-vous le verrouillage de la délibération pour les parcours et niveau sélectionnés?</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <div class="card">
                        <div class="card-body">
                          <!-- General Form Elements -->
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="button" class="btn btn-success" data-bs-dismiss="modal" onclick="cloturer_deliberation()">Confirmer</button>
                            </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
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

    var form = document.getElementById('form');

    parcours.addEventListener('change', rafraichir_niveaux);

    rafraichir_niveaux();

    form.addEventListener('submit', function(event){
        event.preventDefault();
    });

    function cloturer_deliberation(){
        form.action = '{{ route("notes.cloturer_deliberation")}}';
        form.submit();
    }

    function ouvrir_deliberation(e){
        form.action = '{{ route("notes.ouvrir_deliberation") }}';
        form.submit();
    }

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
