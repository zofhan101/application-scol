@extends('app.app_layout')
@section('title', 'Mise à jour des informations étudiant')

@section('content')

<section class="section">
    <div class="row">
      <div class="col-lg-12">

        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Génération des codes-barre des feuilles de copie par élément constitutif</h5>

            <!-- Default Accordion -->
            <div class="accordion" id="accordionExample">
                @foreach($liste_ue_ec as $evaluation)
                    {{-- evaluations --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-ev-{{ $evaluation->id_session_examen }}" aria-expanded="true" aria-controls="collapse-ev-{{ $evaluation->id_session_examen }}">
                                {{ $evaluation->nom_session_examen }}
                            </button>
                        </h2>
                        <div id="collapse-ev-{{ $evaluation->id_session_examen }}" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <div class="accordion" id="accordionExample">
                                    @foreach($evaluation->mentions as $mention)
                                        {{-- mentions --}}
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="headingOne">
                                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-me-{{ $mention->id_mention }}" aria-expanded="true" aria-controls="collapse-me-{{ $mention->id_mention }}">
                                                    {{ $mention->nom_mention }}
                                                </button>
                                            </h2>
                                            <div id="collapse-me-{{ $mention->id_mention }}" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                                <div class="accordion-body">
                                                    <div class="accordion" id="accordionExample">
                                                        @foreach($mention->parcours as $parcours)
                                                            {{-- mentions --}}
                                                            <div class="accordion-item">
                                                                <h2 class="accordion-header" id="headingOne">
                                                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-pa-{{ $parcours->id_parcours }}" aria-expanded="true" aria-controls="collapse-pa-{{ $parcours->id_parcours }}">
                                                                        {{ $parcours->nom_parcours }}
                                                                    </button>
                                                                </h2>
                                                                <div id="collapse-pa-{{ $parcours->id_parcours }}" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                                                    <div class="accordion-body">
                                                                        <div class="accordion" id="accordionExample">
                                                                            @foreach($parcours->niveaux as $niveau)
                                                                                {{-- mentions --}}
                                                                                <div class="accordion-item">
                                                                                    <h2 class="accordion-header" id="headingOne">
                                                                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-ni-{{ $niveau->id_niveau }}" aria-expanded="true" aria-controls="collapse-ni-{{ $niveau->id_niveau }}">
                                                                                            {{ $niveau->nom_niveau }}
                                                                                        </button>
                                                                                    </h2>
                                                                                    <div id="collapse-ni-{{ $niveau->id_niveau }}" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                                                                        <div class="accordion-body">
                                                                                            <div class="accordion" id="accordionExample">
                                                                                                @foreach($niveau->ues as $ue)
                                                                                                    {{-- mentions --}}
                                                                                                    <div class="accordion-item">
                                                                                                        <h2 class="accordion-header" id="headingOne">
                                                                                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-ue-{{ $ue->id_ue }}" aria-expanded="true" aria-controls="collapse-ue-{{ $ue->id_ue }}">
                                                                                                                {{ $ue->nom_ue }}
                                                                                                            </button>
                                                                                                        </h2>
                                                                                                        <div id="collapse-ue-{{ $ue->id_ue }}" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                                                                                            <div class="accordion-body">
                                                                                                                <ul class="list-group">
                                                                                                                    @foreach ($ue->ecs as $ec)
                                                                                                                        <li class="list-group-item d-flex justify-content-between align-items-start">
                                                                                                                            <div class="ms-2 me-auto">
                                                                                                                              <div class="fw-bold">{{ $ec->nom_ec }}</div>
                                                                                                                            </div>
                                                                                                                             <form action="{{ route('down_barcode') }}" method="POST">
                                                                                                                                @csrf
                                                                                                                                <input type="hidden" name="id_ue_ec" value ="{{ $ec->id_ue_ec }}">
                                                                                                                                <button type="submit" class="btn btn-success"><i  class="bi bi-arrow-down-circle"></i></button>
                                                                                                                            </form>

                                                                                                                        </li>
                                                                                                                    @endforeach

                                                                                                                </ul>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                @endforeach
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div><!-- End Default Accordion Example -->

          </div>
        </div>

      </div>
    </div>
</section>
@endsection
