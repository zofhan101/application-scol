@extends('app.app_layout')
@section('title', 'Interface de saisie des notes')

@section('content')
<div class="pagetitle" id="pagetitle">
    <h1>Vérification des notes d'examen</h1>

    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle me-1"></i>
        Veuillez scanner le code-barres sur la copie d'examen puis saisir la note rectifiée si nécessaire.
    </div>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-6 col-md-6">
            <div class="card info-card sales-card">
              <div class="card-body">
                <h5 class="card-title">Note enregistrée</h5>

                <div class="d-flex align-items-center justify-content-center">
                  <div class="ps-3 card-title">
                    <h1 id="note_enregistree" style="font-size:75pt"></h1>
                  </div>
                </div>
              </div>

            </div>
        </div>

        <div class="col-lg-6 col-md-6">
            <div class="card">
                <div class="card-body" style="padding-top: 2%">
                    <h5 class="card-title">Modifier la note</h5>
                  <form class="row g-3" action="#" id="form" >
                    <div class="col-12">
                        <input type="hidden" id="barcode" name="barcode">

                        <label for="inputAddress" class="form-label">Note modifiée</label>
                        <input type="text" class="form-control" name="note_modifiee" id="note_modifiee" disabled>
                    </div>

                    <div class="text-center">
                      <button type="submit" class="w-100 btn btn-primary" id="submit_button" disabled >Enregistrer</button>
                    </div>
                  </form>
                </div>
              </div>
        </div>
    </div>
</section>
<script>
    let code_input = document.getElementById('barcode');
    let note_input = document.getElementById('note_modifiee');
    let form = document.getElementById('form');
    let submit_button = document.getElementById('submit_button');
    let barcode="";

    note_enregistree = document.getElementById('note_enregistree');

    document.addEventListener('keydown', get_note);

    form.addEventListener('submit', modifier_note);

    async function modifier_note(e){
        e.preventDefault();
        note_enregistree.textContent = "";
        let formdata = new FormData(form);
        let url = "{{ route('modifier_note') }}";
        try {
            let response = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Cookie': document.cookie
            },
            credentials: 'include',
            body: formdata
            });

            const contentType = response.headers.get('Content-Type');
            console.log('Content type: ', contentType);


            let data = await response.json();
            barcode = "";
            code_input.value = "";
            note_input.value = "";
            note_input.disabled = true;
            submit_button.disabled = true;
            if(response.ok){
                console.log('Modification note :reponse ok ', data);
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

                console.error('Modification note :erreur ', data);
                //ajouter un label et y afficher les erreurs
                let div_erreur = document.createElement('div');
                div_erreur.className ="alert alert-danger alert-dismissible fade show";
                div_erreur.setAttribute("role", "alert");
                //div_erreur.textContent += data.message + ';;; ';
                if(data.errors != undefined){
                    if(data.errors.barcode != undefined)
                        div_erreur.textContent += data.errors.barcode + ";;; ";
                    if(data.errors.note_modifiee != undefined)
                        div_erreur.textContent += data.errors.note_modifiee;
                    if(data.errors.acces != undefined)
                        div_erreur.textContent += data.errors.acces;
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
            console.error('Enregistrement note :reponse pas ok ',error);
            barcode ="";
            alert('Erreur lors de l\'enregistrement: '+ error+ ' Veuillez réessayer');
        }
    }

    async function get_note(event){
        active_element = document.activeElement;
        if(active_element !== note_input){
            if (event.key === 'Enter') {
                console.log("Code-barres scanné :", barcode);
                code_input.value = barcode;
                let formdata = new FormData();
                formdata.append('barcode', code_input.value);

                let url = "{{ route('get_note') }}";
                try {
                    let response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json, text/html',
                        'Cookie': document.cookie
                    },
                    credentials: 'include',
                    body: formdata
                    });

                    const contentType = response.headers.get('Content-Type');
                    console.log('Content type: ', contentType);

                    if(contentType.includes('application/json')){
                        let data = await response.json();
                        if(response.ok){
                            console.log('Récupération note :reponse ok ', data);
                            note_enregistree.textContent = data.note;
                            barcode = "";
                            note_input.disabled = false;
                            submit_button.disabled = false;

                        }else {
                            console.error('Erreur à la récupération des notes', data);
                            barcode = "";
                            code_input.value = "";
                            note_enregistree.textContent ="";
                            note_input.disabled = true;
                            submit_button.disabled = true;
                            //ajouter un label et y afficher les erreurs
                            let div_erreur = document.createElement('div');
                            div_erreur.className ="alert alert-danger alert-dismissible fade show";
                            div_erreur.setAttribute("role", "alert");
                            //div_erreur.textContent += data.message + ';;; ';
                            if(data.errors != undefined){
                                if(data.errors.barcode != undefined)
                                    div_erreur.textContent += data.errors.barcode + ";;; ";
                                if(data.errors.acces != undefined)
                                    div_erreur.textContent += data.errors.acces;
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

                    }
                    else if(contentType.includes('text/html')){
                        let data = await response.text();
                        document.open;
                        document.write(data);
                        document.close();
                    }
                } catch (error) {
                    console.error('Récupération note :reponse pas ok ',error);
                    barcode = "";
                    code_input.value = "";
                    note_input.disabled = true;
                    submit_button.disabled = true;
                    note_enregistree.textContent = "";
                    alert('Erreur lors de la récupération de la note: '+ error+ ' Veuillez réessayer');
                }

            } else {
                barcode += event.key;
            }

        }

    }


</script>


@endsection

