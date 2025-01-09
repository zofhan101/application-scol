@extends('app.app_layout')
@section('title', 'Téléchargement des listes des exclus')

@section('content')
<div class="pagetitle">
    <h1>tableau de bord</h1>
</div>

<section class="section">
<form action="#"  class="row g-3">
    @csrf
    <div class="col-lg-3 col-md-3">
        <div class="card info-card">
            <div class="card-body">
                <h5 class="card-title">année universitaire</h5>
                <select class="form-select" name="au" id="au">
                @foreach($au as $unit)
                            <option value="{{$unit->id_au}}"  >{{$unit->intitule}}</option>
                 @endforeach  
                </select>
            </div>
        </div>
    </div>

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
    <div class="col-lg-3 col-md-3">
        <div class="card info-card">
            <div class="card-body">
                <h5 class="card-title">matieres</h5>
                <select class="form-select" name="matiere" id="matiere">

                </select>
            </div>
        </div>
</form>
</section>
<section  class="section row g-3" >
<div class="col-xxl-6 col-md-6">
              <div class="card info-card sales-card">
                <div class="card-body">
                  <h5 class="card-title">inscrits<span id="annee"> </span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-people fs-1"></i>
                    </div>
                    <div class="ps-3">
                      <span class="text-success fs-1 pt-1 fw-bold" id="nombre">  </span> <span class="text-muted fs-3 pt-2 ps-1" id="personnes_inscrites">Personnes inscrites</span>
                    </div>
                  </div>
                </div>
              </div>
</div>

<div class="col-xxl-6 col-md-6">
              <div class="card info-card sales-card">
                <div class="card-body">
                  <h5 class="card-title">moyenne de classe<span id="anneeMoyenne"> </span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-bar-chart fs-1"></i>
                    </div>
                    <div class="ps-3">
                      <span class="text-success fs-1 pt-1 fw-bold" id="moyenne">  </span> <span class="text-muted fs-3 pt-2 ps-1" id="personnes_inscrites">/20</span>
                    </div>
                  </div>
                </div>
              </div>
</div>
</section>


</div>

<section class="row g-3">
              <!-- DONUT  -->
              <div class="card col-6">
            <div class="card-body pb-0">
              <h5 class="card-title">nombre d'ue validés | non validés | eliminatoires <span id="anneeDonut"></span></h5>

              <div id="trafficChart" style="min-height: 400px;" class="echart"></div>

              <script>

              </script>

            </div>
          </div><!-- FIN DONUT -->



          <div class="col-6">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title" >etudiants admis | redoublants | triplants | exclus  <span id="anneeBaton"></span></h5>

                  <!-- Line Chart -->
                  <div id="reportsChart"></div>

                  <script>
                    
                      
                  
                  </script>
                  <!-- End Line Chart -->

                </div>

              </div>
            </div><!-- End Reports -->
</section>

<!-- ----------------------------******************--------------------------------- -->


<script>
    var au = document.getElementById('au')
    var niveau = document.getElementById('niveau')
    var matiere = document.getElementById('matiere')



// -------------------------**********************---------------------------------




    async function getAdmis(){
      
      let annee = document.getElementById('au')
      let niveaux = document.getElementById('niveau')
      let parcours = document.getElementById('parcours')

      let selectedNiveau = niveaux.value;
      let selectedParcours = parcours.value
      let selectedAnnee = annee.value;
      
      console.log("niveau :  ", selectedNiveau)
      console.log("parcours :  " , selectedParcours)
      console.log("annee : " , selectedAnnee)

      let formdata = new FormData();
      formdata.append("id_parcours" , selectedParcours)
      formdata.append("id_niveau" , selectedNiveau)
      formdata.append("id_au", selectedAnnee)

      let url = "{{ route('stats.admis') }}"

      try{
        console.log("stats admis")
            let response = await fetch(url, {
                method: 'POST',
                body: formdata,
                headers: {
                    'Accept': 'application/json',
                    'Cookie': document.cookie,
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            let data  = await response.json();
            console.log(data);

        let passant = data[0];  
        let redoublant = data[1]; 
        let triplant = data[2];
        let exclu = data[3]; 

        // Réinitialisation du conteneur du graphe
        let chartElement = document.querySelector("#reportsChart");
        if (chartElement) {
            chartElement.innerHTML = "";
        }

        // Création du graphique
        new ApexCharts(chartElement, {
            series: [
                { name: 'passants', data: [passant] },
                { name: 'redoublants', data: [redoublant] },
                { name: 'triplants', data: [triplant] },
                { name: 'exclus', data: [exclu] }
            ],
            chart: {
                height: 370,
                type: 'bar',
                toolbar: { show: false }
            },
            colors: ['#4154f1', '#2eca6a', '#ff771d', '#f542a4'],
            fill: { opacity: 1 },
            dataLabels: { enabled: false },
            xaxis: {
                categories: ["Statistiques actuelles"] // Une seule catégorie
            }
        }).render();

                      // FIN GRAPHE BATON
      }
      catch(err){
        console.error('Erreur à la récupération des données de donut ', err);
      }

    }


  // -------------------------**********************--------------------------------- 




    async function getValide(){

      
        let annee = document.getElementById('au')
        let niveaux = document.getElementById('niveau')
        let parcours = document.getElementById('parcours')
        let ue = document.getElementById('matiere')

        let selectedAnnee = annee.value;
        let selectedNiveau = niveaux.value;
        let selectedParcours = parcours.value
        let selectedMatiere = ue.value

        let formdata = new FormData();
        formdata.append("id_au", selectedAnnee)
        formdata.append("id_parcours" , selectedParcours)
        formdata.append("id_niveau" , selectedNiveau)
        formdata.append("id_ue" , selectedMatiere)

        let url = "{{ route('stats.valide') }}"


        try{
            console.log("nouveau donut")
            let response = await fetch(url, {
                method: 'POST',
                body: formdata,
                headers: {
                    'Accept': 'application/json',
                    'Cookie': document.cookie,
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            let data  = await response.json();
            console.log(data);

            // DONUT DE STATS DE MATIERES 

          let chartDom = document.querySelector("#trafficChart");
            let myChart = echarts.init(chartDom);

            let option = {
                tooltip: {
                    trigger: 'item'
                },
                legend: {
                    top: '5%',
                    left: 'center'
                },
                series: [
                    {
                        name: 'Statistiques',
                        type: 'pie',
                        radius: ['40%', '70%'],
                        avoidLabelOverlap: false,
                        label: {
                            show: false,
                            position: 'center'
                        },
                        emphasis: {
                            label: {
                                show: true,
                                fontSize: '18',
                                fontWeight: 'bold'
                            }
                        },
                        labelLine: {
                            show: false
                        },
                        data: [
                            { value: data[0], name: 'Validé' },
                            { value: data[1], name: 'Éliminatoire' },
                            { value: data[2], name: 'Non validé' }
                        ]
                    }
                ]
            };

        // Appliquer l'option au graphique
        myChart.setOption(option);

                // FIN DE DONUT
          }
          catch(err){
            console.error('Erreur à la récupération des données de donut ', err);
          }      
    }

    // -----------------------------*******************---------------------------------------------

    async function getNombreinscrits(){
        let yearBaton = document.getElementById('anneeBaton')
        let yearDonut = document.getElementById('anneeDonut')
        let year = document.getElementById('annee')
        let yearMoyenne = document.getElementById('anneeMoyenne')
        let annee = document.getElementById('au')
        let niveaux = document.getElementById('niveau')
        let parcours = document.getElementById('parcours')

        let selectedAnnee = annee.value;
        let selectedNiveau = niveaux.value;
        let selectedParcours = parcours.value

        // changer les spans de chaque stats en fonction de l'année selectionnée
        const optionSelectionnee = annee.options[annee.selectedIndex];
        const intitule = optionSelectionnee.textContent;
        year.textContent = "  |  " + intitule
        yearMoyenne.textContent = "  |  " + intitule
        yearDonut.textContent = "  |  " + intitule
        yearBaton.textContent = "  |  " + intitule
        // envoi des données
        let formdata = new FormData();
        formdata.append("id_au", selectedAnnee)
        formdata.append("id_parcours" , selectedParcours)
        formdata.append("id_niveau" , selectedNiveau)

        let url = "{{ route('stats.nombreInscrits') }}"
        try{
            console.log("nouvelle stats")
            let response = await fetch(url, {
                method: 'POST',
                body: formdata,
                headers: {
                    'Accept': 'application/json',
                    'Cookie': document.cookie,
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            let jsonResponse = await response.json()
            console.log(jsonResponse)

            // prise et affichage des resultats
            let nombreInscrits = jsonResponse[0]
            let moyenneG = jsonResponse[1]
            moyenneG = Math.round(moyenneG * 100)/100
            let moyenne = document.getElementById('moyenne');
            let nombre = document.getElementById('nombre');
            nombre.textContent = nombreInscrits;
            moyenne.textContent = moyenneG;


            // affichage de la liste des matieres dans le champ matiere
            let matieres = document.getElementById('matiere')
            if (response.ok) {
                 matieres.innerHTML = '';
                for (let matiere of jsonResponse[2]) {
                    let newOption = document.createElement('option');
                    newOption.value = matiere.id_unite_enseignement;
                    newOption.textContent = matiere.nom_unite_enseignement;
                    matieres.appendChild(newOption);
                }
            } else {
                console.error('Erreur à la récupération des matieres: ',);
            }


        }
        catch(err){
            console.error('Erreur à la récupération des niveaux: ', err);
        }
    }


 // -----------------------------*******************---------------------------------------------
    async function nombreInscritsMoyenneDonut(){
      await getNombreinscrits();
      await getValide();
      await  getAdmis();
    }
    
     // -----------------------------*******************---------------------------------------------
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
            } else {
                console.error('Erreur à la récupération des niveaux: ', data);
            }
        } catch (error) {
            console.error('Erreur à la récupération des niveaux: ', error);
        }
}


  // -----------------------------*******************---------------------------------------------

  async function niveauAdmis(){
    await get_niveau();
    await getAdmis()
  }

  // -----------------------------*******************---------------------------------------------



  async function getNiveauDonut(){
      await get_niveau();
      await getValide();


    }

// -----------------------------*******************---------------------------------------------
async function init() {
    await get_niveau();
    await nombreInscritsMoyenneDonut()
}

// Appeler la fonction `init` pour initialiser l'affichage
init();

// Ajouter les événements
matiere.addEventListener('change', getValide);
au.addEventListener('change', nombreInscritsMoyenneDonut);
parcours.addEventListener('change', niveauAdmis);
niveau.addEventListener('change', nombreInscritsMoyenneDonut);

</script>
@endsection