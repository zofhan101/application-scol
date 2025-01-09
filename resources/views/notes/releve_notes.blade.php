@php
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
            height: 29.7cm;
            width: 21cm;
            padding: 0;
            margin: 0;
            font-family: 'times new roman';

        }
        section{
            overflow: hidden;
            page-break-after: always;
        }
        .row{
            width: 100%;
            float:left;
        }
        #logo{

            width: 2cm;
            height: 3cm;
            float: left;
            padding-left: 2%;
        }

        #en-tete{
        width: 7cm;
        height: 4cm;
        float: left;
        padding-left: 12%;

        }

        #titre{
            text-align: center;
            font-family: 'times new roman';
            font-size: 10pt;
            font-weight: bold;
        }

        .espace{
            height: 0.5cm;
            float: left;
        }

        #contenu{

            font-family:'times new roman';
            padding-left: 5%;
        }

        #identite{
            border: 2px solid black;
            width: 17cm;
            height: 3Cm;
            font-size: 10pt;


        }

        #notes{
            padding-left: 0.75cm;
        }

        #recap{
            padding-left: 0.75cm;
        }

        .separateur{
            border: 2px solid black;
            width: 23cm;
        }

        .text-align-center{
            text-align: center;
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
    @foreach ($resultats as $resultat )
        <section>
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

            <div class="row">
                <div class="col-8"><span>&nbsp;</span></div>
                <div class="col-4">
                    Le Doyen,
                </div>
            </div>

            <div class="row espace"></div>

            <div class="row" id="titre">
                RELEVE DE NOTES
            </div>

            <div class="row espace"></div>


            {{-- contenu --}}
            <div class="row" id="contenu"  >
                <div id="identite">
                    <div class="col-5">
                        <div class="row">ANNÉE UNIVERSITAIRE: {{ $resultat[0]->intitule }}</div>
                        <div class="row">NOM: {{ $resultat[0]->nom_etudiant }}</div>
                        <div class="row">PRENOMS: {{ $resultat[0]->prenoms }}</div>
                        @php
                            setlocale(LC_TIME, 'fr_FR.utf8');
                            date_default_timezone_set('Europe/Paris');

                            $date = new DateTime($resultat[0]->date_naissance);
                            $formatted_date_naissance = strftime('%d %B %Y', $date->getTimestamp());
                        @endphp

                        <div class="row">DATE DE NAISSANCE: {{ $formatted_date_naissance }} </div>
                        <div class="row">LIEU DE NAISSANCE: {{ $resultat[0]->lieu_naissance }} </div>
                        <div class="row">N° Matricule: {{ $resultat[0]->im }}</div>
                    </div>
                    <div class="col-5 text-align-center">
                        <div class="row">CLASSE: {{ $resultat[0]->nom_niveau }}</div>
                        <div class="row"> {{ strtoupper($mention->nom_mention) }}</div>
                        @if( $mention->nom_mention != $resultat[0]->nom_parcours)
                            <div class="row">PARCOURS {{ strtoupper($resultat[0]->nom_parcours) }}</div>
                        @endif
                    </div>
                </div>

                <div class="row espace"></div>
                <div class="row espace"></div>

                <div id="notes">
                    @foreach ($resultat as $ligne )
                         <div class="row">
                             <div class="col-8">{{ strtoupper($ligne->nom_unite_enseignement) }}</div>
                             <div class="col-4">{{ round($ligne->note_ue, 2) }} / {{ round($ligne->note_max, 2) }}</div>
                         </div>
                    @endforeach

                </div>

                <div class="row espace"></div>
                <div class="row separateur"></div>
                <div class="row espace"></div>

                <div id="recap">
                    <div class="col-8">
                        <div class="row"> TOTAL: {{ round($resultat[0]->total,2 )}} / {{ round($resultat[0]->total_max, 2) }}</div>
                        <div class="row">MOYENNE: {{ round($resultat[0]->moyenne, 2) }} / {{ round($resultat[0]->note_max, 2) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="row">DECISION: {{ strtoupper($resultat[0]->decision) }}</div>
                    </div>
                </div>

            </div>


        </section>

    @endforeach

</body>

</html>

