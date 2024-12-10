@extends('app.app_layout')
@section('title', 'Résultats d\évaluation')

@section('content')
@php
    $etu1 = $datas[0][0];
    $resultats = $datas[0];
    $historiques = $datas[1];

    //var_dump($etu1);

@endphp

<div class="pagetitle text-center">
    <h1>INTERFACE DE DELIBERATION {{ $etu1->intitule }}</h1>
    <h1>{{ $etu1->parcours }} - {{ $etu1->niveau }}</h1>
</div>


<section class="section">
    <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">

              <!-- Default Table -->
              <table class="table">
                <thead>
                  <tr>
                    <th rowspan="3" class="alignement-vertical-centre">Total</th>
                    <th rowspan="3" class="alignement-vertical-centre">Total Coefficient</th>
                    <th rowspan="3" class="alignement-vertical-centre">Moyenne de passage</th>
                    <th rowspan="3" class="alignement-vertical-centre">Moyenne</th>
                    <th rowspan="3" class="alignement-vertical-centre">Nombre d'U.E.</th>
                    <th rowspan="3" class="alignement-vertical-centre">U.E. à valider</th>
                    <th rowspan="3" class="alignement-vertical-centre">U.E. validées</th>
                    <th rowspan="3" class="alignement-vertical-centre">Nombre note eliminatoire</th>
                    <th rowspan="3" class="alignement-vertical-centre " >Statut courant</th>
                    <th rowspan="3" class="alignement-vertical-centre " >Statut à l'A.U. suivante</th>
                    <th rowspan="3" class="alignement-vertical-centre " >Niveau à l'A.U. suivante</th>
                    <th rowspan="3" class="alignement-vertical-centre demarcation" >DECISION</th>




                    @foreach($etu1->evals as $eval)
                        @php
                            $nbr_col = 0;
                            foreach($eval->ues as $ue){
                                $nbr_col += count($ue->ecs)+3;
                            }
                        @endphp
                        <th colspan="{{ $nbr_col }}" class ="alignement-vertical-centre">{{ $eval->nom_session_examen }}</th>
                        <th class="demarcation"></th>
                    @endforeach
                  </tr>

                  <tr>
                    @foreach($etu1->evals as $eval)
                        @foreach($eval->ues as $ue)
                                <th colspan="{{ count($ue->ecs)+3 }}" >{{ $ue->nom_ue }}</th>
                        @endforeach
                        <th class="demarcation"></th>
                    @endforeach
                  </tr>

                  <tr>
                    @foreach($etu1->evals as $eval)
                        @foreach($eval->ues as $ue)
                            @foreach ($ue->ecs as $ec)
                                <th>{{ $ec->nom_ec }}</th>
                            @endforeach
                            <th>Note U.E.</th>
                            <th>Coefficient</th>
                            <th>Validation</th>
                        @endforeach
                        <th class="demarcation"></th>
                    @endforeach
                  </tr>

                </thead>
                <tbody>
                    @foreach($resultats as $resultat)
                        <tr>
                            <td>{{ round($resultat->total, 2) }}</td>
                            <td>{{ round($resultat->total_coefficient, 2) }}</td>
                            <td>{{ round($resultat->moyenne_passage, 2) }}</td>
                            <td>{{ round($resultat->moyenne, 2) }}</td>
                            <td>{{ $resultat->nombre_ue }}</td>
                            <td>{{ $resultat->nombre_ue_a_valider }}</td>
                            <td>{{ $resultat->nombre_ue_validees }}</td>
                            <td>{{ $resultat->nombre_note_eliminatoire }}</td>
                            <td>{{ $resultat->statut }}</td>
                            <td>{{ $resultat->statut_au_suivante }}</td>
                            <td>{{ $resultat->nom_niveau_suivant }}</td>
                            <td class="demarcation">
                                <button class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="{{ '#historique_'.$resultat->id_etudiant }}">ADMETTRE</button>
                            </td>


                            @foreach ($resultat->evals as $eval)

                                @foreach($eval->ues as $ue)
                                    @foreach ($ue->ecs as $ec)
                                        <td> {{ $ec->note_ec }}</td>
                                    @endforeach
                                    <td> {{ $ue->note_ue }} </td>
                                    <td> {{ $ue->coefficient }} </td>
                                    <td> {{ $ue->validation }} </td>
                                @endforeach
                                <td class="demarcation"></td>



                            @endforeach


                        </tr>

                        {{-- modal informations redoublements --}}

                        <div class="modal fade" id="{{ 'historique_'.$resultat->id_etudiant }}" tabindex="-1">
                          <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title" id="titre_confirmation" >Confirmer l'admission de l'étudiant sélectionné ?</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              </div>
                              <div class="modal-body">
                                <div class="card">
                                  <div class="card-body">
                                       <div>
                                        @php
                                            $historique = $historiques[$resultat->id_etudiant] ?? [];
                                        @endphp

                                            <h5>Historique de redoublements et de triplements</h5>
                                            @foreach ($historique as $item)
                                                <div>
                                                    <span>{{ $item->intitule }}</span>
                                                    <span>{{ $item->nom_niveau }}</span>
                                                    <span>{{ $item->statut_au_suivante }}</span>
                                                </div>
                                            @endforeach
                                       </div>
                                  </div>
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">annuler</button>
                                <button type="button" class="btn btn-danger bouton-admission" data-bs-dismiss="modal" data-id-etudiant={{ $resultat->id_etudiant }}>ADMETTRE</button>
                            </div>
                            </div>
                          </div>
                        </div>


                    @endforeach
                </tbody>
              </table>
              <!-- End Default Table Example -->
            </div>
          </div>


        </div>
    </div>
</section>

<script>
    boutons_admission = document.getElementsByClassName('bouton-admission');
    let bouton;
    for(i=0; i<boutons_admission.length; i++){
        bouton = boutons_admission[i];
        bouton.addEventListener('click', function(event){
            id_etudiant = bouton.getAttribute('data-id-etudiant');
            admettre_etudiant(id_etudiant)
        });
    }

    async function admettre_etudiant(id_etudiant){
        // admission d'un étudiant
        let url = "{{route('notes.admettre_etudiant')}}";

        id_parcours = {{ $etu1->id_parcours }};
        id_niveau = {{ $etu1->id_niveau }}
        rang_niveau = {{ $etu1->rang_niveau }}

        let formdata = new FormData();
        formdata.append("id_parcours", id_parcours);
        formdata.append("id_niveau", id_niveau);
        formdata.append("rang_niveau", rang_niveau);
        formdata.append("id_etudiant", id_etudiant);


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

            if(response.ok){
                console.log(data.message);
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

                console.error('ERREUR  à l admission de l étudiant: ', data);
                alert('ERREUR à l admission de l étudiant');

            }

        } catch (error) {
            console.error('ERREUR à la récupération des niveaux: ', error);
            alert('ERREUR à la récupération des niveaux');
        }
    }

</script>

@endsection
