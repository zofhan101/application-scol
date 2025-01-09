@extends('app.app_layout')
@section('title', 'Controle de la saisie des entetes')

@section('content')


<section class="section">
    @if(isset($error))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $error }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

    @else
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card info-card">
                    <div class="card-body">
                        <h5 class="card-title">Controle de la saisie des entetes</h5>
                        <select class="form-select" name="examen" id="examen">
                            @foreach($examens as $examen)
                                <option value="{{$examen->id_examen_par_au }}##--##{{ $examen->nom_session_examen }}">{{ $examen->nom_session_examen }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="text-center" id="buttons">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#ouverture_modal" disabled> Ouvrir la saisie</button>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cloture_modal" disabled> Verrouiller la saisie</button>
                </div>

                {{-- Modal de cloture --}}
                <div class="modal fade" id="cloture_modal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="titre_cloture" ></h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <div class="card">
                            <div class="card-body">
                              <!-- General Form Elements -->
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                    <button type="button" class="btn btn-success" data-bs-dismiss="modal" onClick="verrouiller_saisie()">Confirmer</button>
                                </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                {{-- Modal d'ouverture --}}

                <div class="modal fade" id="ouverture_modal" tabindex="-1">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="titre_ouverture" ></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <div class="card">
                          <div class="card-body">
                            <!-- General Form Elements -->
                              <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                  <button type="button" class="btn btn-success" data-bs-dismiss="modal" onClick="ouvrir_saisie()">Confirmer</button>
                              </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
            </div>
        </div>

    @endif
</section>

<script>

    var examen = document.getElementById('examen')
    examen.addEventListener('change', get_status);
    console.log(examen);

    get_status();

    async function verrouiller_saisie(){
        let examen_par_au = examen.value.split("##--##");
        let id_examen_par_au =  examen_par_au[0];
        let formdata = new FormData();
        formdata.append("id_examen_par_au", id_examen_par_au);
        //console.log(formdata);

        let url= "{{ route('verrouiller_saisie_entete') }}";
            try {
                let response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json;q=0.9, text/html;q=1',
                        'Cookie': document.cookie,
                    },
                    credentials: 'include',
                    body: formdata
                });

                const contentType = response.headers.get('Content-Type');
                console.log('Content type: ', contentType);

                if(contentType.includes('application/json')){
                    let data = await response.json();

                    if(response.ok){
                        get_status();
                        //alert(data.message);
                    }
                    else {
                        console.error('Erreur lors du verrouillage de la saisie des notes: ', data);
                        alert('Erreur lors du verrouillage de la saisie des notes');

                    }
                }
                else if(contentType.includes('text/html')){
                    let data = await response.text();
                    document.open;
                    document.write(data);
                    document.close();
                }
            } catch (error) {
                console.error('Erreur lors du verrouillage de la saisie des notes: ',error);
                alert('Erreur lors du verrouillage de la saisie des notes');
            }

    }

    async function ouvrir_saisie(){
        let examen_par_au = examen.value.split("##--##");
        let id_examen_par_au =  examen_par_au[0];
        let formdata = new FormData();
        formdata.append("id_examen_par_au", id_examen_par_au);
        //console.log(formdata);

        let url= "{{ route('ouvrir_saisie_entete') }}";
            try {
                let response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json;q=0.9, text/html;q=1',
                        'Cookie': document.cookie,
                    },
                    credentials: 'include',
                    body: formdata
                });
                let data = await response.json();
                const contentType = response.headers.get('Content-Type');
                console.log('Content type: ', contentType);

                if(contentType.includes('application/json')){

                    if(response.ok){
                        get_status();
                        //alert(data.message);
                    }
                    else {
                        console.error('Erreur lors de l\'ouverture de la saisie des entetes: ', data);
                        alert('Erreur lors de l\'ouverture de la saisie des entetes');

                    }
                }
                else if(contentType.includes('text/html')){
                    let data = await response.text();
                    document.open();
                    document.write(data);
                    document.close();
                }
            } catch (error) {
                console.error('ouverture de la saisie des entetes: erreur ',error);
                alert('Erreur lors de l\'ouverture de la saisie des entetes');
            }

    }

    async function get_status(){
        let examen_par_au = examen.value.split("##--##");
        let id_examen_par_au =  examen_par_au[0];
        let nom_examen_par_au = examen_par_au[1];
        let formdata = new FormData();
        console.log('id_examen_par_au: ', id_examen_par_au);
        formdata.append('id_examen_par_au', id_examen_par_au);

        let url= "{{ route('get_operation_par_examen') }}";
            try {
                let response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json;q=0.9, text/html;q=1',
                        'Cookie': document.cookie,
                    },
                    credentials: 'include',
                    body: formdata
                });

                const contentType = response.headers.get('Content-Type');
                console.log('Content type: ', contentType);

                if(contentType.includes('application/json')){
                    let data = await response.json();
                    console.log('operation reçue: ', data);

                    if(response.ok){
                        let boutons = document.getElementById('buttons');
                        let liste_boutons = boutons.querySelectorAll('button');
                        //aucune opération n'a encore été lancée
                        if(data.operation.length == 0){
                            liste_boutons[0].disabled = false;
                            liste_boutons[1].disabled = true;
                            let titre_ouverture = document.getElementById('titre_ouverture');
                            titre_ouverture.textContent = "Confirmez-vous l'ouverture de la saisie des entetes pour: " + nom_examen_par_au;
                        }
                        else if(data.operation.date_ouverture_saisie_en_tete == null){
                            console.log('operation non ouverte');
                            let titre_cloture = document.getElementById('titre_ouverture');
                            titre_cloture.textContent = "Confirmez-vous l'ouverture de la saisie des entetes pour: " + nom_examen_par_au;
                            liste_boutons[0].disabled = false;
                            liste_boutons[1].disabled = true;

                        }
                        else if(data.operation.date_ouverture_saisie_en_tete != null && data.operation.date_cloture_saisie_en_tete == null){
                            console.log('operation ouverte et  non cloturée');
                            let titre_cloture = document.getElementById('titre_cloture');
                            titre_cloture.textContent = "Confirmez-vous la cloture de la saisie des entetes pour: " + nom_examen_par_au;
                            liste_boutons[0].disabled = true;
                            liste_boutons[1].disabled = false;
                        }

                        else if(data.operation.date_ouverture_saisie_en_tete != null && data.operation.date_cloture_saisie_en_tete != null){
                            console.log('operation ouverte et deja cloturée');
                            liste_boutons[0].disabled = true;
                            liste_boutons[1].disabled = true;
                        }

                    }else {
                        console.error('récupération de l\'operation sur examen: erreur ', data);
                        alert('Erreur lors de la récupération de l\'operation sur l\'examen');

                    }
                }
                else if(contentType.includes('text/html')){
                    let data = await response.text();
                    document.open;
                    document.write(data);
                    document.close();
                }
            } catch (error) {
                console.error('récupération de l\'operation sur examen: erreur ',error);
                alert('Erreur lors de la récupération de l\'operation sur l\'examen');
            }


    }
</script>

@endsection
