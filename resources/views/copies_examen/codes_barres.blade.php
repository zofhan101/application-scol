<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        body{
            height: 32cm;
            width: 25cm;

        }
        .row{
            width: 100%;
            float:left;
            margin: 10px;
        }

        .col-6{
            width: 45%;
            float: left;
            padding: 5px
        }
    </style>
</head>
<body>
        <h1>
            @php
                if($ue_ec->nom_mention == $ue_ec->nom_parcours)
                    $mention = "Mention ".$ue_ec->nom_mention." ";
                else
                    $mention = "Mention ".$ue_ec->nom_mention." Parcours ".$ue_ec->nom_parcours." ";
            @endphp
            Codes-barre pour le codage des copies d'examen - {{ $ue_ec->nom_session_examen }} - Année universitaire {{ $ue_ec->intitule }} {{ $mention }} - {{ $ue_ec->nom_niveau }} - {{ $ue_ec->nom_unite_enseignement }} - {{ $ue_ec->nom_element_constitutif }} ({{ $ue_ec->id_ue_ec }})
        </h1>
        @for($i = 1; $i<= $ue_ec->nbr_inscrits; $i++)
            <div class="row">
                <div class="col-6">
                    @php
                        echo DNS1D::getBarcodeSVG( $ue_ec->id_ue_ec.'-'.$i, 'C128',2,35,'black', true)
                    @endphp
                </div>

                <div class="col-6">
                   @php
                        echo DNS1D::getBarcodeSVG( $ue_ec->id_ue_ec.'-'.$i, 'C128',2,35,'black', true)
                    @endphp
                </div>
            </div>
        @endfor
</body>
</html>

