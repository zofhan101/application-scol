@extends('app.app_layout')
@section('title', 'Controle de la génération des résultats de l\'année avant les repêchages')

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
                        <h5 class="card-title">Génération des résultats des examens de l'A.U. avant le repêchage</h5>
                        <div class="text-center">
                            <button class="btn btn-success w-100" id="generate_button" data-bs-toggle="modal" data-bs-target="#confirmation_modal"> Générer les résultats</button>
        =                </div>
                    </div>
                </div>



                {{-- Modal d'ouverture --}}

                <div class="modal fade" id="confirmation_modal" tabindex="-1">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="titre_confirmation" >Confirmez-vous la génération des résultats annuels avant les repêchages pour l'A.U. {{ $au->intitule }}</h5>
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

   async function generer_resultats(){

        let url= "{{ route('notes.generer_resultats_au') }}";
            try {
                let response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json, text/html',
                        'Cookie': document.cookie,
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    credentials: 'include',

                });
                const contentType = response.headers.get('Content-Type');
                console.log("content-type: ",contentType);
                if(contentType.includes('application/json')){
                    let data = await response.json();
                    console.log('RESPONSE: ', data);

                    if(response.ok){
                        console.log(data);
                        alert(data.message);
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
                        else if(response.status == 422){
                            alert(data.error);
                        }
                        else{
                            console.error('Erreur lors de la génération des résultats: ', data);
                            alert('Erreur lors de la génération des résultats:'.data.error);
                        }

                    }
                }
                else if(contentType.includes('text/html')){
                    let data =  await response.text();
                    console.log("text: ",data);
                    document.open();
                    document.write(data);
                    document.close();
                }



            } catch (error) {
                console.error('Erreur lors de la génération des résultats: ',error);
                alert('Erreur lors de la génération des résultats');
            }

    }

</script>

@endsection
