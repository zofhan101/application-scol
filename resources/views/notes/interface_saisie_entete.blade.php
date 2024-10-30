@extends('app.app_layout')
@section('title', 'Interface de saisie des entetes')

@section('content')
<div class="pagetitle" id="pagetitle">
    <h1>Saisie des entêtes des feuilles de copie</h1>

    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle me-1"></i>
        Veuillez scanner le code-barres sur la copie d'examen puis saisir le matricule ci-dessous
    </div>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-12">

            <div class="card">
                <div class="card-body" style="padding-top: 2%">
                  <form class="row g-3" action="#" id="form">
                    <div class="col-12">
                        <input type="hidden" id="barcode" name="barcode">

                        <label for="inputAddress" class="form-label">Matricule</label>
                        <input type="text" class="form-control" name="matricule" id="matricule" disabled>
                    </div>

                    <div class="text-center">
                      <button type="submit" class="w-100 btn btn-primary" id="submit_button" disabled>Enregistrer</button>
                    </div>
                  </form>


                </div>
              </div>


        </div>
    </div>
</section>
<script>
    var input_du_code = document.getElementById('barcode');
    var matricule_input = document.getElementById('matricule');
    var form = document.getElementById('form');
    var submit_button = document.getElementById('submit_button');
    var barcode="";
    var formdata;

    document.addEventListener('keydown', get_code);

    form.addEventListener('submit', async function(event){
        event.preventDefault();
        formdata = new FormData(this);
        await enregistrer_donnees(formdata);
        document.addEventListener('keydown', get_code);
        matricule_input.value = "";
        matricule_input.disabled = true;
        submit_button.disabled = true;
        barcode = "";
    });


    function get_code(event){
        if (event.key === 'Enter') {
            console.log("Code-barres scanné :", barcode);
            input_du_code.value = barcode;
            matricule_input.disabled = false;
            setTimeout(function() {
                matricule_input.focus();
            }, 0);
            submit_button.disabled = false;
            barcode = "";
            document.removeEventListener('keydown', get_code);
        } else {
            barcode += event.key;
        }
    }

   async function enregistrer_donnees(formdata){
        let url = "{{ route('enregistrer_entete') }}";
        try {
            let response = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept':'application/json',
                'Cookie': document.cookie
            },
            credentials: 'include',
            body: formdata
            });

            const contentType = response.headers.get('Content-Type');
            console.log('Content type: ', contentType);
            //console.log( await response.text());

            let data = await response.json();
            if(response.ok){
                console.log('Enregistrement matricule :reponse ok ', data);
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
                console.error('ERREUR: Enregistrement matricule : ', data);
                barcode = "";
                //ajouter un label et y afficher les erreurs
                let div_erreur = document.createElement('div');;
                div_erreur.className ="alert alert-danger alert-dismissible fade show";
                div_erreur.setAttribute("role", "alert");
                div_erreur.textContent += data.message + ';;; ';
                if(data.errors != undefined){
                    if(data.errors.barcode != undefined)
                        div_erreur.textContent += data.errors.barcode + ";;; ";
                    if(data.errors.matricule != undefined)
                        div_erreur.textContent += data.errors.matricule;
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
</script>

@endsection

