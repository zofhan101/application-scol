@php
    $verbe;
    if($au_en_cours->intitule === $inscription->intitule)
        $verbe = 'est';
    else
        $verbe = "a été";

    $annee = date('Y');
    $annee_c = (int)$annee - 2000;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <style>
         @font-face {
            font-family: 'times new roman';
            src: url('{{ asset('assets/fonts/times new roman.ttf') }}') format('truetype');
        }


        body{
            height: 35cm;
            width: 25cm;
            padding: 0;
            margin: 0;
            font-size: 16pt;
            font-family: 'times new roman';

        }
        .row{
            width: 100%;
            float:left;
        }
        #logo{

            width: 5cm;
            height: 4cm;
            float: left;
            padding-left: 2%;
        }

        #en-tete{
        width: 13cm;
        height: 6cm;
        float: left;
        padding-left: 8%;

        }

        #titre{
            text-align: center;
            font-family: 'times new roman';
            font-size: 16pt;
        }

        .espace{
            height: 0.5cm;
            float: left;
        }

        #contenu{

            font-family:'times new roman';
            font-size: 16pt;
            padding-left: 5%;
        }

        .col-titre{
            width: 15%;
            float: left;
        }
        .col-contenu{
            width: 80%;
            float: left;
        }

        .col-4{
            width: 30%;
            float: left;
        }

        .col-8{
            width: 60%;
            float: left;
        }

        .col-5{
            width: 50%;
            float: left;
        }

        #footer{
            font-family: 'times new roman';
            font-size: 12pt;
            padding-left: 5%;
        }

    </style>


</head>
<body>

    <section class="section">
        {{-- en-tête --}}
        <div class="row">
            <div  id="logo" >
                <img src="{{ asset('assets/images/logo.png') }}" alt="logo_facmed" height="100%">
            </div>
            <div id="en-tete" >
                <img src="{{ asset('assets/images/en-tete.png') }}" height="100%" alt="en-tête">
            </div>
        </div>
        <div class="row espace"></div>
        <div class="row">
            <div class="col-8"><span>&nbsp;</span></div>
            <div class="col-4">
                Antananarivo, le
            </div>

        </div>

        <div class="row">
            <div class="col-4">
                <b>N°___________/{{ $annee_c }}/FAC.MED/SCOL</b>
            </div>
        </div>

        <div class="row espace"></div>
        <div class="row espace"></div>

        <div class="row">
            <div class="col-8"><span>&nbsp;</span></div>
            <div class="col-4">
                Le Doyen,
            </div>
        </div>

        <div class="row espace"></div>
        <div class="row espace"></div>

        <div class="row" id="titre">
            ATTESTATION
        </div>

        <div class="row espace"></div>
        <div class="row espace"></div>
        <div class="row espace"></div>
        <div class="row espace"></div>
        {{-- contenu --}}
        <div class="row" id="contenu"  >
            <div class="row">
             <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>Je soussigné, Doyen de la Faculté de Médecine d'Antananarivo, atteste que l'étudiant(e):
            </div>

            <div class="row espace"></div>

            <div class ="row">
                <div class="col-titre"><span>&nbsp;</span></div>
                <div class="col-contenu"><b> {{ $inscription->nom }} {{ $inscription->prenoms }}</b></div>
            </div>


            <div class ="row">
                <div class="col-titre"><span>&nbsp;</span></div>
                @php
                    setlocale(LC_TIME, 'fr_FR.utf8');
                    date_default_timezone_set('Europe/Paris');

                    $date = new DateTime($inscription->date_naissance);
                    $formatted_date_naissance = strftime('%d %B %Y', $date->getTimestamp());



                    //$formattedDate_naissance = $date->format('j F Y');
                @endphp
                <div class="col-contenu"> <b>Né(e) le {{ $formatted_date_naissance }} &nbsp;&nbsp;&nbsp;&nbsp; à &nbsp;&nbsp;&nbsp;&nbsp; {{ $inscription->lieu_naissance }} </b></div>
            </div>
            <div class="row espace"></div>
            <div class="row">
                    {{ $verbe }} inscrit(e) en {{ $inscription->nom_niveau_long }}
                    @if($inscription->nom_niveau !== null)({{ $inscription->nom_niveau }}), @endif
                    <b>Mention {{ $inscription->nom_mention }}
                    @if($inscription->nom_mention !== $inscription->nom_parcours), {{  $inscription->nom_parcours }}@endif </b>
                    sous le numéro matricule <b> {{ $inscription->im }} </b>
                    au titre de l'Année Universitaire {{ $inscription->intitule }}.
            </div>
            <div class="row espace"></div>

            @if($inscription->date_annulation !== null)
                <div class="row">
                   Néanmoins, il a été fait une annulation de ladite inscription demande de l'intéressé(e) et il(elle) n'a donc pas passé les examens correspondants à l'année universitaire susmentionnée.
                </div>
                <div class="row espace"></div>
            @endif

            <div class="row">
                <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                La présente attestation est établie, à la demande de l’intéressé(e), pour faire valoir et servir  ce que de droit.
            </div>


        </div>


    </section>


</body>

</html>

