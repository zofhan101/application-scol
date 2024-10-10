@extends('app.admin_app_layout')
@section('title', 'Définition des unités d\'enseigenement')

@section('content')

<div class="pagetitle">
    <h1>Définition des unités d'enseignement pour l'année universitaire</h1>
</div>

<section class="section" id="crud_ue_section">
    {{-- boutons pour sélectionner des U.E. --}}
    <form>
        <select id="eval" name="eval" id="">
            @foreach ($exams as $exam)
                <option value="{{ $exam->id_examen_par_au }}-{{ $exam->nom_session_examen }}">{{ $exam->nom_session_examen }}</option>
            @endforeach
        </select>
    </form>

    <form id="coef" >
        Coef
        <input  type="number" name="coef" value="1">
    </form>

    <button id="ajout_btn">
        Ajouter >
    </button>


    {{-- choix de parcours et niveau --}}
    <div class="row">
        <div class="col-lg-4 col-md-4">
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

        <div class="col-lg-4 col-md-4">
        </div>

        <div class="col-lg-4 col-md-4">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">Niveau</h5>
                    <select class="form-select" name="niveau" id="niveau">
                        @foreach($niveaux as $niveau)
                            <option value="{{$niveau->id_niveau}}">{{ $niveau->nom_niveau }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- affichage des UE et EC --}}
    <div class="row">
        <div class="col-4">
            {{-- création nouvelle UE --}}
            <div class ="row">
                <div class="col-lg-12">
                    <div class="card info-card">
                        <div class="card-body">
                            <h5 class="card-title">Créer une nouvelle U.E.</h5>
                            <form action="" id="create_ue">
                                <input type="text" class="form-control" name="nom_ue" id="input_ue">
                                <button type="submit" class="btn btn-primary w-100">Créer</button>
                            </form>

                        </div>
                    </div>
                </div>

            </div>

            <div class="card info-card ">
                <div class="card-body">
                    <h5 class="card-title">U.E. préexistantes</h5>
                    <div class="limit-height">
                        <form action="" id="unites_enseignement">
                            <ul class="list-group list-group-flush" id="liste_ue">
                                @foreach($UE as $ue)
                                    <li class="list-group-item">
                                      <input class="form-check-input me-1" type="radio" value="{{ $ue->nom_unite_enseignement }}-{{ $ue->id_unite_enseignement }}" name="ue">
                                      {{ $ue->nom_unite_enseignement }}
                                    </li>
                                @endforeach
                            </ul>
                        </form>
                    </div>


                </div>

            </div>
        </div>

        <div class="col-4">
            {{-- création nouvel EC --}}
            <div class ="row">
                <div class="col-lg-12">
                    <div class="card info-card">
                        <div class="card-body">
                            <h5 class="card-title">Créer un nouvel E.C.</h5>
                            <form action="" id="create_ec">
                                <input type="text" class="form-control" name="nom_ec" id="input_ec">
                                <button type="submit" class="btn btn-primary w-100">Créer</button>
                            </form>

                        </div>
                    </div>
                </div>

            </div>

            <div class="card info-card ">
                <div class="card-body">
                    <h5 class="card-title">E.C. préexistantss</h5>
                    <div class="limit-height">
                        <form action="" id="elements_constitutifs">
                            <ul class="list-group list-group-flush" id="liste_ec">
                                @foreach($EC as $ec)
                                    <li class="list-group-item">
                                      <input class="form-check-input me-1" type="checkbox" value="{{ $ec->nom_element_constitutif }}-{{ $ec->id_element_constitutif }}" name="ec">
                                      {{ $ec->nom_element_constitutif }}
                                    </li>
                                @endforeach
                            </ul>
                        </form>
                    </div>
                </div>

            </div>
        </div>

        <div class="col-md-4 col-lg-4">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title">U.E. sélectionnés</h5>
                    <div class="ue_selected">
                        <ul class="list-group" id="ue_selectionnes">

                        </ul>

                    </div>


                </div>

            </div>
        </div>

    </div>

</section>

<script>
    //mise à jour de la liste des niveaux selon le parcours
    //parcours sélectionné
    var id_parcours;
    var parcours = document.getElementById('parcours');
    id_parcours = parcours.value;
    parcours.addEventListener('change', function(){
        //récupération des niveaux correspondants au parcours sélectionné
        let url = "{{route('get_niveaux_parcours')}}";
        id_parcours = this.value;
        let formdata = new FormData();
        formdata.append("id_parcours", id_parcours);
        fetch(url,{
            method: 'POST',
            body: formdata
        })
        .then(response => response.json())
        .then(data=>{
            let niveaux = document.getElementById('niveau');
            niveaux.innerHTML = '';
            let niveau;
            for(let i=0; i<data.length; i++){
                niveau = data[i];
                let newOption = document.createElement('option');
                newOption.value = niveau.id_niveau;
                newOption.textContent = niveau.nom_niveau;
                niveaux.appendChild(newOption);
            }
            //charger les UE correspondants aux Classes et Niveaux sélectionnés pour cette A.U.
            rafraichir_ue_ec_selectionnes();
            reset_selection();


        })
        .catch(error=>{
            console.error(error);
        })
    });

    // niveau sélectionné
    niveau = document.getElementById('niveau');
    var id_niveau = niveau.value;
    niveau.addEventListener('change', function(){
        id_niveau = this.value;
        rafraichir_ue_ec_selectionnes();
        reset_selection();
    });


    //charger les UE correspondants aux Classes et Niveaux sélectionnés pour cette A.U.
    rafraichir_ue_ec_selectionnes();


    //coefficient des UE
    var coef;
    var coef_form = document.getElementById('coef');
    var coef_input = coef_form.querySelector('input');
    coef = coef_input.value;
    coef_input.addEventListener('change', function(){
        coef = this.value;
    });

    //evaluation comprenant l'ue selectionnée
    var eval_selectionne;
    var eval_select = document.getElementById('eval');
    table_eval_select = eval_select.value.split('-');
    eval_selectionne = {
        'id_examen_par_au': table_eval_select[0],
        'nom_session_examen': table_eval_select[1]
    };
    //console.log(eval_selectionne);
    eval_select.addEventListener('change', function(){
        let table_value = this.value.split('-');
        eval_selectionne={
            'id_examen_par_au': table_value[0],
            'nom_session_examen': table_value[1]
        };
        //console.log(eval_selectionne);
    });

    //EC selectionnes
    var ec_selectionnes=[];
    document.getElementById('elements_constitutifs').addEventListener('change', function(event){
        let input = event.target;
        let input_value = input.value;
        let table_ec_selectionne = input_value.split("-");
        ec_selectionnes.push({'id_ec': table_ec_selectionne[1], 'nom_ec':table_ec_selectionne[0]});
        //console.log(ec_selectionnes);
    });

    //U.E. sélectionné
    var ue_selectionne = null;
    document.getElementById('unites_enseignement').addEventListener('change', function(event){
        let input = event.target;
        let input_value = input.value;
        let table_ue_selectionne = input_value.split("-");
        ue_selectionne = {
            'id_ue':table_ue_selectionne[1],
            'nom_ue': table_ue_selectionne[0],
        };
        //onsole.log(ue_selectionne);
    });


    //création d'UE
    document.getElementById('create_ue').addEventListener('submit',function(e){
        e.preventDefault();
        let formdata = new FormData(this);
        let url="{{ route('create_ue') }}";
        fetch(url,{
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Cookie': document.cookie
            },
            body: formdata,
            credentials: 'include'

        })
        .then(response => {

            if(response.ok){
                return response.json().then(data=>{
                    //alert(data.message);
                    //rafraichissement de la colonne contenant les UE
                    rafraichir_ue();
                    let input_ue = document.getElementById('input_ue');
                    input_ue.value="";
                    ue_selectionne = null;
                });
            }
            else if(response.status ==422){
                return response.json().then(data=>{
                    alert(data.errors.nom_ue);
                });
            }
            else{
                return response.json().then(data=>{
                    alert(data.message);
                })
            }
        })
        .catch(error=>{
            console.error(error);
        })


    });

    //création d'EC
    document.getElementById('create_ec').addEventListener('submit',function(e){
        e.preventDefault();
        let formdata = new FormData(this);
        let url="{{ route('create_ec') }}";
        fetch(url,{
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Cookie': document.cookie
            },
            body: formdata,
            credentials: 'include'

        })
        .then(response => {

            if(response.ok){
                return response.json().then(data=>{
                    //alert(data.message);
                    //rafraichissement de la colonne contenant les UE
                    rafraichir_ec();
                    let input_ue = document.getElementById('input_ec');
                    input_ue.value="";
                    ec_selectionnes=[];
                });
            }
            else if(response.status ==422){
                return response.json().then(data=>{
                    alert(data.errors.nom_ue);
                });
            }
            else{
                return response.json().then(data=>{
                    alert(data.message);
                })
            }
        })
        .catch(error=>{
            console.error(error);
        })


    });


    //validation d'U.E. et E.C. sélectionnés
    document.getElementById('ajout_btn').addEventListener('click', ajout_ue_ec);

    async function ajout_ue_ec(){
        if(ue_selectionne != null && ec_selectionnes.length>0){
            ue_selectionne.id_examen_par_au = eval_selectionne.id_examen_par_au;
            ue_selectionne.nom_session_examen = eval_selectionne.nom_session_examen;
            ue_selectionne.coef = coef;


            let url= "{{ route('ajouter_ue_ec') }}";
            try {
                let response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Cookie': document.cookie,
                        'Content-Type': 'application/json'
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        'ue': ue_selectionne,
                        'ecs': ec_selectionnes,
                        'id_parcours': id_parcours,
                        'id_niveau': id_niveau
                    })
                });
                let data = await response.json();
                if(response.ok){
                    console.log('mise à jour :reponse ok: ', data);
                    rafraichir_ue_ec_selectionnes();
                    reset_selection();


                }else {
                    console.error('mise à jour :reponse pas ok(else block): ', data);
                    alert('Erreur lors de l\'enregistrement');

                }
            } catch (error) {
                console.error('mise à jour :reponse pas ok(catch block): ',error);
                alert('Erreur lors de l\'enregistrement');
            }
        }
    }



    function reset_selection(){
        let inputs = document.querySelectorAll('#unites_enseignement input[type="radio"], #elements_constitutifs input[type="checkbox"]');
            for(let i = 0; i<inputs.length; i++){
                inputs[i].checked = false;
            }
            ue_selectionne = null;
            ec_selectionnes = [];

    }

    async function supprimer_old(parent){
        let suppressions=[];
        $liste_ec_element = parent.querySelectorAll('div > ul > li');
        let id_ue_ec;
        for(let i=0; i<$liste_ec_element.length; i++){
            id_ue_ec = $liste_ec_element[i].getAttribute('data-id_ue_ec');
            suppressions.push(id_ue_ec);
        }

        let url= "{{ route('supprimer_ue_ec') }}";
            try {
                let response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Cookie': document.cookie,
                        'Content-Type': 'application/json'
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        "suppressions":suppressions
                    })
                });
                let data = await response.json();
                if(response.ok){
                    console.log('suppression :reponse ok: ', data);
                    rafraichir_ue_ec_selectionnes();
                    reset_selection();


                }else {
                    console.error('suppression :reponse pas ok(else block): ', data);
                    alert('Erreur lors de la suppression');

                }
            } catch (error) {
                console.error('suppression :reponse pas ok(catch block): ',error);
                alert('Erreur lors de la suppression');
            }


    }

    function supprimerSelection(e){
        //suppression visuelle et aussi dans la base

        parent = e.target.parentNode.parentNode.parentNode;
        //console.log(e.target);
        div_code = parent.querySelector('div > div');

        let id_ue_ec = div_code.getAttribute('data-id_ue_ec');
        supprimer_old(parent);

        parent.remove();
    }

    async function get_liste_ue_ec_selectionnes(){

        let formdata = new FormData();
        formdata.append("id_parcours", id_parcours);
        formdata.append("id_niveau", id_niveau);


        let url= "{{ route('liste_ue_ec') }}"
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

            let data = await response.json();
            if(response.ok){
                console.log('Get liste UE EC selectionnes :reponse ok', data);

                return data.ue_ec;
            }else {
                console.error('Get liste UE EC selectionnes :reponse pas ok(else block): ', data);
                return [];

            }
        } catch (error) {
            console.error('Get liste UE EC selectionnes :reponse pas ok(catch block): ',error);
            return [];
        }

    }

    async function rafraichir_ue_ec_selectionnes(){
        let liste_ue_ec = await get_liste_ue_ec_selectionnes();
        let liste_ue_selectionnes = document.getElementById('ue_selectionnes');
        liste_ue_selectionnes.innerHTML='';

        let ue_ec_courant;

        for(let indice = 0; indice<liste_ue_ec.length; indice++){
            ue_ec_courant = liste_ue_ec[indice];

            //élémetent à ajouter
            let li_ajout = document.createElement('li');
            li_ajout.className="list-group-item d-flex justify-content-between align-items-start";

            let div = document.createElement('div');
            div.className ="ms-2 me-auto"

            let div2 = document.createElement('div');
            div2.className="fw-bold";
            div2.textContent = ue_ec_courant.nom_ue;


            let liste_ec = document.createElement('ul');
            liste_ec.className = "list-group";

            let ec_element;
            let ecs = ue_ec_courant.ecs;
            for(let j = 0; j<ecs.length; j++){
                ec_element = document.createElement('li');
                ec_element.className="list-group-item";
                ec_element.setAttribute("data-id_ue_ec", ecs[j].id_ue_ec);
                ec_element.textContent = ecs[j].nom_ec;
                liste_ec.appendChild(ec_element);
            }

            let div3 = document.createElement('div');
            div3.className= "d-flex flex-column";

            let span = document.createElement('span');
            span.className = "badge bg-danger rounded-pill";
            let i = document.createElement('i');
            i.className ="bi bi-trash delButton";
            i.addEventListener('click', supprimerSelection);

            let span2 = document.createElement('span');
            span2.className = "badge bg-success rounded-pill";
            span2.textContent = ue_ec_courant.nom_session_examen;

            let span3 = document.createElement('span');
            span3.className = "badge bg-primary rounded-pill";
            span3.textContent = "coef " + ue_ec_courant.coef;

            span.appendChild(i);
            li_ajout.appendChild(div);
            div.appendChild(div2);
            div.appendChild(liste_ec);

            div3.appendChild(span);
            div3.appendChild(span2);
            div3.appendChild(span3);

            li_ajout.appendChild(div3);

            liste_ue_selectionnes.appendChild(li_ajout);


        }

    }

    async function rafraichir_ec(){
        let liste_ue = document.getElementById('liste_ec');
        let ecs = await get_liste_ec();
        //console.log("UES:    "+ues);
        if(ecs.length>0){
            liste_ec.innerHTML = '';
            let ec;
            let ec_element;
            let input;
            let text;
            for(let i=0; i<ecs.length; i++){
                ec = ecs[i];

                ec_element=document.createElement('li');
                ec_element.className = "list-group-item";

                input = document.createElement('input');
                input.className = "form-check-input me-1";
                input.type = "checkbox"
                input.value = ec.nom_element_constitutif + "-" + ec.id_element_constitutif;
                input.name="ec";
                ec_element.appendChild(input);

                text = document.createTextNode(ec.nom_element_constitutif);
                ec_element.appendChild(text);

                liste_ec.appendChild(ec_element);

            }
        }
    }

    async function get_liste_ec(){
        let url= "{{ route('liste_ec') }}"
        try {
            let response = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Cookie': document.cookie
            },
            credentials: 'include'
            });
            let data = await response.json();
            if(response.ok){
                console.log('Get liste EC :reponse ok');
                console.log(data);
                return data.ecs;
            }else {
                response.json().then(data=>{
                    console.log('Get liste EC :reponse pas ok');
                    console.log(data);
                    return [];
                });

            }
        } catch (error) {
            console.error(error);
        }

    }

    async function rafraichir_ue(){
        let liste_ue = document.getElementById('liste_ue');
        let ues = await get_liste_ue();
        console.log("UES:    "+ues);
        if(ues.length>0){
            liste_ue.innerHTML = '';
            let ue;
            let ue_element;
            let input;
            let text;
            for(let i=0; i<ues.length; i++){
                ue = ues[i];
                /*<li class="list-group-item">
                    <input class="form-check-input me-1" type="radio" value="{{ $ue->nom_unite_enseignement }}-{{ $ue->id_unite_enseignement }}" name="ue">
                    {{ $ue->nom_unite_enseignement }}
                </li>*/

                ue_element=document.createElement('li');
                ue_element.className = "list-group-item";

                input = document.createElement('input');
                input.className = "form-check-input me-1";
                input.type = "radio"
                input.value = ue.nom_unite_enseignement + "-" + ue.id_unite_enseignement;
                input.name="ue";
                ue_element.appendChild(input);

                text = document.createTextNode(ue.nom_unite_enseignement);
                ue_element.appendChild(text);

                liste_ue.appendChild(ue_element);

            }
        }
    }

    async function get_liste_ue(){
        let url= "{{ route('liste_ue') }}"
        try {
            let response = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Cookie': document.cookie
            },
            credentials: 'include'
            });
            let data = await response.json();
            if(response.ok){
                console.log('Get liste UE :reponse ok');
                console.log(data);
                return data.ues;
            }else {
                response.json().then(data=>{
                    console.log('Get liste UE :reponse pas ok');
                    console.log(data);
                    return [];
                });

            }
        } catch (error) {
            console.error(error);
        }

    }


</script>

@endsection
