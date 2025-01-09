@extends('app.app_layout')
@section('title', 'Import des sélectionnés')

@section('content')
<div class="pagetitle">
    <h1>Import de la liste des selectionnés</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accueil')}}">Accueil</a></li>
        <li class="breadcrumb-item">Inscriptions</li>
        <li class="breadcrumb-item active">Import des sélectionnés</li>
      </ol>
    </nav>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-6">

            <div class="card">
              <div class="card-body">
                <h5 class="card-title">
                    @isset($success)
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                       {{ $success }}
                       <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                     </div>
                   @endisset

                    <a href="down_modele_selectionnes">
                        <button type="button" class="btn btn-secondary"><i class="bi bi-file-arrow-down"></i> Télécharger le modèle Excel</button>
                    </a>
                </h5>

                <!-- Vertical Form -->
                <form class="row g-3" action="import_selectionnes" method="POST" enctype="multipart/form-data">
                    @csrf

                  <div class="col-12">
                    <label for="inputNanme4" class="form-label">Mention ou parcours</label>
                    @error('parcours')
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ $message }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @enderror
                    <select class="form-select" aria-label="Default select example" name="parcours">
                        @foreach($parcours as $parcour)
                            <option value="{{ $parcour->id_parcours }}">{{ $parcour->nom_parcours }}</option>
                        @endforeach
                    </select>
                  </div>
                    <div class="col-12">

                    <label for="inputEmail4" class="form-label">Fichier Excel</label>
                    <input type="file" class="form-control" id="inputEmail4" name="fichier_excel">
                    @error('fichier_excel')
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            @php
                                $errors = explode("###",$message);
                                //var_dump($errors);
                            @endphp
                            @foreach($errors as $error)
                                @php
                                    $points = explode(">>>",$error)
                                //var_dump($points);
                                @endphp
                                @foreach($points as $point)
                                    {{ $point }}<br>
                                @endforeach
                            @endforeach

                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @enderror
                    </div>
                  <div class="text-center">
                    <button type="submit" class="btn btn-primary">Importer</button>
                    <button type="reset" class="btn btn-secondary">Reset</button>
                  </div>
                </form><!-- Vertical Form -->


              </div>
            </div>

        </div>
        <div class="col-lg-12">
            @isset($doublons)
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        DES DOUBLONS ONT ETE DETECTES:<br>
                        Seule leur première occurence a été sauvegardée. <br>
                        Les données suivantes existent déjà dans la base de donnée pour cette année universitaire et la mention selectionnée ou sont écrites en double dans le fichier excel.
                        <br>
                        <table>
                            <tr>
                                <th>Numéro bacc</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                            </tr>
                            @foreach($doublons as $doublon)
                                <tr>
                                    <td>{{ $doublon->num_bacc }}</td>
                                    <td>{{ $doublon->nom }}</td>
                                    <td>{{ $doublon->prenoms }}</td>
                                </tr>
                            @endforeach
                            </table>


                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endisset
        </div>
    </div>
</section>
@endsection
