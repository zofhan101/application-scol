@extends('app.app_layout')
@section('title', 'Controle de la génération des résultats des évaluations')

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
                        <h5 class="card-title">Controle de la génération des résultats</h5>
                        <select class="form-select" name="examen" id="examen">
                            @foreach($examens as $examen)
                                <option value="{{$examen->id_examen_par_au }}##--##{{ $examen->nom_session_examen }}">{{ $examen->nom_session_examen }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="text-center">
                    <button class="btn btn-success w-100" id="generate_button" data-bs-toggle="modal" data-bs-target="#confirmation_modal" disabled> Générer les résultats</button>
=                </div>

                {{-- Modal d'ouverture --}}

                <div class="modal fade" id="confirmation_modal" tabindex="-1">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="titre_confirmation" ></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <div class="card">
                          <div class="card-body">
                            <!-- General Form Elements -->
                              <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                  <button type="button" class="btn btn-success" data-bs-dismiss="modal" onClick="generer_resultats()">Confirmer</button>
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
    //console.log(examen);

    get_status();

    async function generer_resultats(){
        let examen_par_au = examen.value.split("##--##");
        let id_examen_par_au =  examen_par_au[0];
        let formdata = new FormData();
        formdata.append("id_examen_par_au", id_examen_par_au);
        //console.log(formdata);

        let url= "{{ route('notes.generer_resultats') }}";
            try {
                let response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json, text/html',
                        'Cookie': document.cookie,
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    credentials: 'include',
                    body: formdata
                });
                const contentType = response.headers.get('Content-Type');
                if(contentType.includes('application/json')){
                    let data = await response.json();

                    if(response.ok){
                        get_status();

                    }
                    else {
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
                        console.error('Erreur lors de la génération des résultats: ', data);
                        alert('Erreur lors de la génération des résultats:');
                    }
                }
                else if(contentType.includes('text/html')){
                    let data =  await response.text();
                    document.open();
                    document.write(data);
                    document.close();
                    //console.log(data);
                }



            } catch (error) {
                console.error('Erreur lors de la génération des résultats: ',error);
                alert('Erreur lors de la génération des résultats');
            }

    }

    async function get_status(){
        let examen_par_au = examen.value.split("##--##");
        let id_examen_par_au =  examen_par_au[0];
        let nom_examen_par_au = examen_par_au[1];
        let formdata = new FormData();
        //console.log('id_examen_par_au: ', id_examen_par_au);
        formdata.append('id_examen_par_au', id_examen_par_au);

        let url= "{{ route('get_operation_par_examen') }}";
            try {
                let response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Cookie': document.cookie,
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    credentials: 'include',
                    body: formdata
                });
                let data = await response.json();
                console.log('operation reçue: ', data);

                if(response.ok){
                    let bouton = document.getElementById('generate_button');
                    //aucune opération n'a encore été lancée
                    if(data.operation.length == 0){
                        bouton.disabled = true;


                    }
                    else if(data.operation.date_cloture_verification_note != null && data.operation.date_cloture_verification_en_tete != null && data.operation.date_resultats == null){
                        console.log('Notes et en-têtes cloturées');
                        let titre_confirmation = document.getElementById('titre_confirmation');
                        titre_confirmation.textContent = "Confirmez-vous la générationd des résultats pour : " + nom_examen_par_au;

                        bouton.disabled = false;

                    }
                    else{
                        bouton.disabled = true;

                    }
                }else {
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
                    console.error('récupération de l\'operation sur examen: erreur ', data);
                    alert('Erreur lors de la récupération de l\'operation sur examen');

                }
            } catch (error) {
                console.error('récupération de l\'operation sur examen: erreur ',error);
                alert('Erreur lors de la récupération de l\'operation sur examen');
            }


    }
</script>

@endsection
