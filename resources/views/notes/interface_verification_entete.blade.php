@extends('app.app_layout')
@section('title', 'Interface de vérification des en-têtes')

@section('content')
<div class="pagetitle" id="pagetitle">
    <h1>Vérification des en-têtes des feuilles de copie</h1>

    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle me-1"></i>
        Veuillez scanner le code-barres sur l'en-tête puis saisir le matricule rectifié si nécessaire.
    </div>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-6 col-md-6">
            <div class="card info-card sales-card">
              <div class="card-body">
                <h5 class="card-title">Matricule enregistré</h5>

                <div class="d-flex align-items-center justify-content-center">
                  <div class="ps-3 card-title">
                    <h1 id="matricule_enregistre" style="font-size:75pt"></h1>
                  </div>
                </div>
              </div>

            </div>
        </div>

        <div class="col-lg-6 col-md-6">
            <div class="card">
                <div class="card-body" style="padding-top: 2%">
                    <h5 class="card-title">Modifier le matricule</h5>
                  <form class="row g-3" action="#" id="form" >
                    <div class="col-12">
                        <input type="hidden" id="barcode" name="barcode">

                        <label for="inputAddress" class="form-label">Matricule modifié</label>
                        <input type="text" class="form-control" name="matricule_modifie" id="matricule_modifie" disabled>
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

<div class="pagetitle" id = "pagetitle2">
    <h1>Statistiques sur la vérification des en-têtes</h1>

    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle me-1"></i>
        Veuillez sélectionner les parcours, niveau, UE et EC à vérifier
    </div>
</div>
<section class="section">
    <div class="row">
        <div class="col-lg-3 col-md-3">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">Mention ou parcours</h5>
                    <select class="form-select" name="parcours" id="parcours">
                        @foreach($parcours as $parcour)
                            <option value="{{$parcour->id_parcours}}">{{ $parcour->nom_parcours }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-3">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">Niveau</h5>
                    <select class="form-select" name="niveau" id="niveau" disabled>
                    </select>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-3">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">UE</h5>
                    <select class="form-select" name="ue" id="ue" disabled>
                    </select>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-3">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">EC</h5>
                    <select class="form-select" name="ec" id="ec" disabled>
                    </select>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-4">
            <div class="card info-card sales-card">
                <div class="card-body">
                    <h5 class="card-title">Nombre d'inscrits</h5>

                    <div class="d-flex align-items-center justify-content-center">
                      <div class="ps-3 card-title">
                        <h1  id="inscrits" style="font-size: 50pt"></h1>
                      </div>
                    </div>
                  </div>

            </div>
        </div>

        <div class="col-lg-4 col-md-4">
            <div class="card info-card sales-card">
                <div class="card-body">
                    <h5 class="card-title">Nombre d'en-têtes vérifiées</h5>

                    <div class="d-flex align-items-center justify-content-center">
                      <div class="ps-3 card-title">
                        <h1  id="nombre_enregistres" style="font-size: 50pt"></h1>
                      </div>
                    </div>
                  </div>

            </div>
        </div>

        <div class="col-lg-4 col-md-4">
            <div class="card info-card sales-card">
                <div class="card-body">
                    <h5 class="card-title">En-têtes encore à vérifier</h5>

                    <div class="d-flex align-items-center justify-content-center">
                      <div class="ps-3 card-title">
                        <h1  id="nombre_restants" style="font-size: 50pt"></h1>
                      </div>
                    </div>
                  </div>

            </div>
        </div>

    </div>
</section>

<script>

    //STATISTIQUES

    //refraichissement des niveaux en fonction du parcours sélectionné
    var id_parcours;
    var parcours = document.getElementById('parcours');
    id_parcours = parcours.value;
    rafraichir_niveaux();
    parcours.addEventListener('change', rafraichir_niveaux);

    //rafraichissement des ue en fonction des niveaux et parcours
    let niveaux_el = document.getElementById('niveau');
    niveaux_el.addEventListener('change', rafraichir_ue);

    //refraichissement des EC en fonction de parcours, niveau, UE
    let ues_el = document.getElementById('ue');
    ues_el.addEventListener('change', rafraichir_ec);

    //rafraichissement des statistiques en fonction des parcours, niveau, UE, ec
    let ecs_el = document.getElementById('ec');
    ecs_el.addEventListener('change', get_stats_saisie_entete);

    async function get_stats_verification_note(){
        //récupération des ec correspondants aux niveau et parcours et ue sélectionnés
        let url = "{{route('get_stats_verification_entete')}}";

        id_ue_ec = ecs_el.value;
        let formdata = new FormData();
        formdata.append("id_ue_ec", id_ue_ec);
        console.log('id_ue_ec: ', id_ue_ec);

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
            console.log("Données récupérées get stats: ",data);

            let inscrits = document.getElementById('inscrits');
            let nbr_enregistres = document.getElementById('nombre_enregistres');
            let nbr_restants = document.getElementById('nombre_restants');

            if(response.ok){
                inscrits.disabled = false;
                nbr_enregistres.disabled = false;
                nbr_restants.disabled = false;

                inscrits.textContent = data.inscrits;
                nbr_enregistres.textContent = data.nbr_enregistres;
                nbr_restants.textContent = data.nbr_restants;

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
                inscrits.disabled = true;
                nbr_enregistres.disabled = true;
                nbr_restants.disabled = true;

                inscrits.textContent = "";
                nbr_enregistres.textContent = "";
                nbr_restants.textContent = "";

                console.error('ERREUR  à la récupération des stats: ', data);

                //ajouter un label et y afficher les erreurs
                let div_erreur = document.createElement('div');
                div_erreur.className ="alert alert-danger alert-dismissible fade show";
                div_erreur.setAttribute("role", "alert");
                //div_erreur.textContent += data.message + ';;; ';
                if(data.errors  != undefined){
                    if(data.errors.id_ue_ec != undefined)
                        div_erreur.textContent += data.errors.id_ue_ec + ";;; ";
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
                document.getElementById("pagetitle2").appendChild(div_erreur);
            }
        } catch (error) {
            let inscrits = document.getElementById('inscrits');
            let nbr_enregistres = document.getElementById('nombre_enregistres');
            let nbr_restants = document.getElementById('nombre_restants');

            inscrits.disabled = true;
            nbr_enregistres.disabled = true;
            nbr_restants.disabled = true;
            inscrits.textContent = "";
            nbr_enregistres.textContent = "";
            nbr_restants.textContent = "";

            console.error('ERREUR à la récupération des stats: ', error);
            alert('ERREUR à la récupération des stats:');
        }
    }

    async function rafraichir_ec(){
        //récupération des ec correspondants aux niveau et parcours et ue sélectionnés
        let url = "{{route('get_liste_ec')}}";
        id_parcours = parcours.value;
        id_niveau = niveaux_el.value;
        id_ue = ues_el.value;
        let formdata = new FormData();
        formdata.append("id_parcours", id_parcours);
        formdata.append("id_niveau", id_niveau);
        formdata.append("id_ue", id_ue);

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
            console.log("Données récupérées: ",data);

            let ecs = document.getElementById('ec');
            if(response.ok){
                ecs.disabled = false;
                ecs.innerHTML = '';
                let ec;
                for(let i=0; i<data.ecs.length; i++){
                    ec = data.ecs[i];
                    let newOption = document.createElement('option');
                    newOption.value = ec.id_ue_ec;
                    newOption.textContent = ec.nom_element_constitutif;
                    ecs.appendChild(newOption);
                }
                await get_stats_verification_note();
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
                ecs.disabled = true;
                ecs.innerHTML = "";

                let inscrits = document.getElementById('inscrits');
                let nbr_enregistres = document.getElementById('nombre_enregistres');
                let nbr_restants = document.getElementById('nombre_restants');

                inscrits.disabled = true;
                nbr_enregistres.disabled = true;
                nbr_restants.disabled = true;
                inscrits.textContent = "";
                nbr_enregistres.textContent = "";
                nbr_restants.textContent = "";
                console.error('ERREUR  à la récupération des EC: ', data);

                //ajouter un label et y afficher les erreurs
                let div_erreur = document.createElement('div');
                div_erreur.className ="alert alert-danger alert-dismissible fade show";
                div_erreur.setAttribute("role", "alert");
                //div_erreur.textContent += data.message + ';;; ';
                if(data.errors  != undefined){
                    if(data.errors.id_parcours != undefined)
                        div_erreur.textContent += data.errors.id_parcours + ";;; ";
                    if(data.errors.id_niveau != undefined)
                        div_erreur.textContent += data.errors.id_niveau + ";;; ";
                    if(data.errors.id_unite_enseignement != undefined)
                        div_erreur.textContent += data.errors.id_unite_enseignement + ";;; ";
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
                document.getElementById("pagetitle2").appendChild(div_erreur);
            }
        } catch (error) {
            let ecs = document.getElementById('ec');
            ecs.disabled = true;
            ecs.innerHTML = "";

            let inscrits = document.getElementById('inscrits');
            let nbr_enregistres = document.getElementById('nombre_enregistres');
            let nbr_restants = document.getElementById('nombre_restants');

            inscrits.disabled = true;
            nbr_enregistres.disabled = true;
            nbr_restants.disabled = true;
            inscrits.textContent = "";
            nbr_enregistres.textContent = "";
            nbr_restants.textContent = "";
            console.error('ERREUR à la récupération des EC: ', error);
            alert('ERREUR à la récupération des EC:');
        }

    }

    async function rafraichir_ue(){
        //récupération des ue correspondants aux niveau et parcours sélectionnés
        let url = "{{route('get_liste_ue')}}";
        id_parcours = parcours.value;
        id_niveau = niveaux_el.value;
        let formdata = new FormData();
        formdata.append("id_parcours", id_parcours);
        formdata.append("id_niveau", id_niveau);

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
            console.log("Données récupérées: ",data);

            let ues = document.getElementById('ue');
            if(response.ok){
                ues.disabled = false;
                ues.innerHTML = '';
                let ue;
                for(let i=0; i<data.ues.length; i++){
                    ue = data.ues[i];
                    let newOption = document.createElement('option');
                    newOption.value = ue.id_unite_enseignement;
                    newOption.textContent = ue.nom_unite_enseignement;
                    ues.appendChild(newOption);
                }
                await rafraichir_ec();
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
                ues.disabled = true;
                ues.innerHTML = "";

                ecs = document.getElementById('ec');
                ecs.disabled = true;
                ecs.innerHTML = "";

                let inscrits = document.getElementById('inscrits');
                let nbr_enregistres = document.getElementById('nombre_enregistres');
                let nbr_restants = document.getElementById('nombre_restants');

                inscrits.disabled = true;
                nbr_enregistres.disabled = true;
                nbr_restants.disabled = true;
                inscrits.textContent = "";
                nbr_enregistres.textContent = "";
                nbr_restants.textContent = "";
                console.error('ERREUR  à la récupération des UE: ', data);

                //ajouter un label et y afficher les erreurs
                let div_erreur = document.createElement('div');
                div_erreur.className ="alert alert-danger alert-dismissible fade show";
                div_erreur.setAttribute("role", "alert");
                //div_erreur.textContent += data.message + ';;; ';
                if(data.errors  != undefined){
                    if(data.errors.id_parcours != undefined)
                        div_erreur.textContent += data.errors.id_parcours + ";;; ";
                    if(data.errors.id_niveau != undefined)
                        div_erreur.textContent += data.errors.id_niveau + ";;; ";
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
                document.getElementById("pagetitle2").appendChild(div_erreur);
            }
        } catch (error) {
            let ues = document.getElementById('ue');
            ues.disabled = true;
            ues.innerHTML = "";
            ecs = document.getElementById('ec');
            ecs.disabled = true;
            ecs.innerHTML = "";

            let inscrits = document.getElementById('inscrits');
            let nbr_enregistres = document.getElementById('nombre_enregistres');
            let nbr_restants = document.getElementById('nombre_restants');

            inscrits.disabled = true;
            nbr_enregistres.disabled = true;
            nbr_restants.disabled = true;
            inscrits.textContent = "";
            nbr_enregistres.textContent = "";
            nbr_restants.textContent = "";
            console.error('ERREUR à la récupération des UE: ', error);
            alert('ERREUR à la récupération des UE:');
        }

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
                    'Cookie': document.cookie
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
                await rafraichir_ue();
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
                let ues = document.getElementById('ue');
                ues.disabled = true;
                ues.innerHTML = "";
                let ecs = document.getElementById('ec');
                ecs.disabled = true;
                ecs.innerHTML = "";
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
                document.getElementById("pagetitle2").appendChild(div_erreur);

            }

        } catch (error) {
            let niveaux = document.getElementById('niveau');
            niveaux.disabled = true;
            niveaux.innerHTML = "";
            let ues = document.getElementById('ue');
            ues.disabled = true;
            ues.innerHTML = "";
            let ecs = document.getElementById('ec');
            ecs.disabled = true;
            ecs.innerHTML = "";
            console.error('ERREUR à la récupération des niveaux: ', error);
            alert('ERREUR à la récupération des niveaux:');
        }
    }


    // SCAN DES CODE-BARRES ET VERIFICATION DES EN-TÊTES
    let code_input = document.getElementById('barcode');
    let matricule_input = document.getElementById('matricule_modifie');
    let form = document.getElementById('form');
    let submit_button = document.getElementById('submit_button');
    let barcode="";

    matricule_enregistre = document.getElementById('matricule_enregistre');

    document.addEventListener('keydown', get_matricule);

    form.addEventListener('submit', modifier_matricule);

    async function modifier_matricule(e){
        e.preventDefault();
        matricule_enregistre.textContent = "";
        let formdata = new FormData(form);
        let url = "{{ route('modifier_matricule') }}";
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
            matricule_input.value = "";
            matricule_input.disabled = true;
            submit_button.disabled = true;
            if(response.ok){
                console.log('Modification matricule :reponse ok ', data);
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

                console.error('Modification matricule :erreur ', data);
                //ajouter un label et y afficher les erreurs
                let div_erreur = document.createElement('div');
                div_erreur.className ="alert alert-danger alert-dismissible fade show";
                div_erreur.setAttribute("role", "alert");
                //div_erreur.textContent += data.message + ';;; ';
                if(data.errors != undefined){
                    if(data.errors.barcode != undefined)
                        div_erreur.textContent += data.errors.barcode + ";;; ";
                    if(data.errors.matricule_modifie != undefined)
                        div_erreur.textContent += data.errors.matricule_modifie;
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
            console.error('Enregistrement matricule :reponse pas ok ',error);
            barcode ="";
            alert('Erreur lors de l\'enregistrement du matricule: '+ error+ ' Veuillez réessayer');
        }
    }

    async function get_matricule(event){
        active_element = document.activeElement;
        if(active_element !== matricule_input){
            if (event.key === 'Enter') {
                console.log("Code-barres scanné :", barcode);
                code_input.value = barcode;
                let formdata = new FormData();
                formdata.append('barcode', code_input.value);

                let url = "{{ route('get_matricule') }}";
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
                    if(response.ok){
                        console.log('Récupération note :reponse ok ', data);
                        matricule_enregistre.textContent = data.matricule;
                        barcode = "";
                        matricule_input.disabled = false;
                        submit_button.disabled = false;
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
                        console.error('Erreur à la récupération du matricule', data);
                        barcode = "";
                        code_input.value = "";
                        matricule_enregistre.textContent ="";
                        matricule_input.disabled = true;
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
                } catch (error) {
                    console.error('Récupération note :reponse pas ok ',error);
                    barcode = "";
                    code_input.value = "";
                    matricule_input.disabled = true;
                    submit_button.disabled = true;
                    matricule_enregistre.textContent = "";
                    alert('Erreur lors de la récupération du matricule: '+ error+ ' Veuillez réessayer');
                }

            } else {
                barcode += event.key;
            }

        }

    }


</script>


@endsection

