@extends('app.app_layout')
@section('title', 'enregistrement notes stage')

@section('content')


<section class="section">

        <div class="pagetitle" id="pagetitle">
            <h1>enregistrement des notes de stage et de TP</h1>
        </div>

        <div class="pagetitle" id = "pagetitle2">
            <h1>enregistrement des notes de stage et TP</h1>

            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle me-1"></i>
                Veuillez sélectionner les parcours, niveau, UE et EC à vérifier
            </div>
        </div>

    <div class="row">
       <form method = "post"class="row g-3" action= "#" id = "form">
        @csrf
                <div class="col-lg-3 col-md-3">
                        <div class="card info-card">
                            <div class="card-body">
                                <h5 class="card-title">Mention ou parcours</h5>
                                <select class="form-select"  name="parcours" id="parcours">
                                        @foreach($parcours as $unit)
                                            <option value="{{$unit->id_parcours}}"  >{{$unit->nom_parcours}}</option>
                                        @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-3">
                        <div class="card info-card">
                            <div class="card-body">
                                <h5 class="card-title">Niveau</h5>
                                <select class="form-select" name="niveau" id="niveau">
            
                                </select>
                            </div>
                        </div>
                    </div>



                    <div class="col-lg-6 col-md-3">
                        <div class="card info-card">
                            <div class="card-body">
                                <h5 class="card-title">Ue-Ec-Session</h5>
                                <select class="form-select" name="id_ue_ec" id="ue">
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-3">
                        <div class="card info-card">
                            <div class="card-body">
                                <h5 class="card-title">Im</h5>
                                <input type="text" class="form-control" name="im" id = "im">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-3">
                        <div class="card info-card">
                            <div class="card-body">
                                <h5 class="card-title">Note</h5>
                                <input type="text" class="form-control" name="note" id = "note">
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <button class="w-100 btn btn-primary" id="submit_button" >Enregistrer</button>
                    </div>

       </form>

    </div>
</section>

<script>
     document.getElementById('form').addEventListener('submit' , function(e){
        e.preventDefault();
     })
    var submitButton = document.getElementById('submit_button');
    var id_parcours;
    var parcours = document.getElementById('parcours');
    var niveau = document.getElementById('niveau')


    async function saveData(){
    let url = "{{ route('notes.enregistrement') }}";
    let form = document.getElementById('form')    
    
    let formdata = new FormData(form);
    

    try {
        let response = await fetch(url, {
            method: 'POST',
            body: formdata,
            headers: {
                'Accept': 'application/json',
                'Cookie': document.cookie,
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        let data = await response.json();   
        if (response.ok) {
            console.log(data)
        } else {
            console.error('Erreur lors de l enregistrement ', data);
        }
    } catch (error) {
        console.error('Erreur lors de l enregistrement ', error);
    }



    }
    async function get_niveau() {
    console.log("changement de parcours detecté");
    let url = "{{route('get_niveaux_parcours')}}";
    id_parcours = parcours.value;

    let formdata = new FormData();
    formdata.append("id_parcours", id_parcours);

    try {
        let response = await fetch(url, {
            method: 'POST',
            body: formdata,
            headers: {
                'Accept': 'application/json',
                'Cookie': document.cookie,
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        let data = await response.json();
        console.log(data);

        let niveaux = document.getElementById('niveau');
        if (response.ok) {
            niveaux.innerHTML = '';
            for (let niveau of data) {
                let newOption = document.createElement('option');
                newOption.value = niveau.id_niveau;
                newOption.textContent = niveau.nom_niveau;
                niveaux.appendChild(newOption);
            }

            // Appeler automatiquement la mise à jour des ue-ec-session
            if (data.length > 0) {
                get_ueEcSession();
            }
        } else {
            console.error('Erreur à la récupération des niveaux: ', data);
        }
    } catch (error) {
        console.error('Erreur à la récupération des niveaux: ', error);
    }
}

async function get_ueEcSession() {
    console.log("prise de la liste");
    let champNiveau = document.getElementById('niveau');
    let id_niveau = champNiveau.value;

    if (!id_niveau) {
        console.error("Aucun niveau sélectionné. get_ueEcSession ne peut pas s'exécuter.");
        return;
    }

    let id_parcours = parcours.value;
    console.log("id parcours = " + id_parcours + ", id niveau = " + id_niveau);

    let url = "{{route('notes.get_ueEcSession')}}";
    let formdata = new FormData();
    formdata.append("id_parcours", id_parcours);
    formdata.append("id_niveau", id_niveau);

    try {
        let response = await fetch(url, {
            method: 'POST',
            body: formdata,
            headers: {
                'Accept': 'application/json',
                'Cookie': document.cookie,
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        let data = await response.json();
        console.log(data);

        let ueEcSession = document.getElementById('ue');
        if (response.ok) {
            ueEcSession.innerHTML = '';
            for (let ue of data) {
                let newOption = document.createElement('option');
                newOption.value = ue.id_ue_ec;
                newOption.textContent = ue.nom_unite_enseignement + "-" + ue.nom_element_constitutif + "-" + ue.nom_session_examen;
                ueEcSession.appendChild(newOption);
            }
        } else {
            console.error('Erreur à la récupération des ue-ec-session: ', data);
        }
    } catch (error) {
        console.error('Erreur à la récupération des ue-ec-session: ', error);
    }
}
    get_niveau();
    parcours.addEventListener('change', get_niveau);
    niveau.addEventListener('change' ,get_ueEcSession);
    submitButton.addEventListener('click' ,saveData)
</script>
@endsection