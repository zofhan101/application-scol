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

        @font-face {
            font-family: 'comic sans ms';
            src: url('{{ asset('assets/fonts/comic-sans-ms-font-family/COMIC.TTF') }}') format('truetype');
        }


        body{
            height: 20cm;
            width: 25cm;
            padding: 0;
            margin: 0;

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
            font-family: 'comic sans ms';
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

        {{-- titre --}}
        <div class="row" id="titre">
                <h1 style="font-family: Comic Sans MS">CERTIFICAT DE SCOLARITE</h1>
        </div>

        {{-- <div class="row espace"></div> --}}

        {{-- contenu --}}
        <div class="row" id="contenu"  >
            <div class="row">
             <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>Je soussigné, Doyen de la Faculté de Médecine d'Antananarivo, certifie que:
            </div>

            <div class="row espace"></div>

            <div class ="row">
                <div class="col-titre"><b>M.</b></div>
                <div class="col-contenu"><b>: Nato </b></div>
            </div>

            <div class ="row">
                <div class="col-titre"><b>Fils (Fille) de</b></div>
                <div class="col-contenu">: Dada </div>
            </div>

            <div class ="row">
                <div class="col-titre"><b>et de</b></div>
                <div class="col-contenu">: Neny</div>
            </div>

            <div class ="row">
                <div class="col-titre"><b>Né (e) le</b></div>

                <div class="col-contenu">: 25 aout 2001    <b> &nbsp;&nbsp;&nbsp;&nbsp; à &nbsp;&nbsp;&nbsp;&nbsp;</b> {{ $inscription->lieu_naissance }} </div>
            </div>
            <div class="row">
                    est régulièrement inscrit(e) en &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b> PACES </b> <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> -  <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                    <b>Matricule: </b> 31347
            </div>

            <div class="row">
                <div class="col-titre"><b>Mention</b></div>
                <div class="col-contenu">
                    : <b>Médecine Humaine</b>
                    <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>

                </div>
            </div>

            <div class="row espace"></div>

            <div class="row">
                <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                    à la Faculté de Médecine d'Antananarivo. Année Universitaire <b>{{ $inscription->intitule }}</b>.
            </div>

            <div class="row espace"></div>

            <div class="row">
                <div class="col-8">
                    <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                </div>
                <div class="col-4">
                    Fait à Antananarivo, le
                </div>
            </div>

            <div class="row espace"></div>
            <div class="row espace"></div>
            <div class="row espace"></div>
            <div class="row espace"></div>

        </div>

        <div class="row" id="footer">
            <div class="col-5">
                <i>
                    <u>NB</u>: Il ne peut être délivré qu’un seul exemplaire. Il appartient à l’intéressé d’en faire des copies et les faire « certifiées conformes » à l’original

                </i>
            </div>
        </div>


    </section>


</body>

</html>

