<html>
    <head>
        <style>
            .section {
                padding: 20px;
            }
            .row {
                display: flex;
                flex-wrap: wrap;
                margin: 0 -15px; /* pour simuler le comportement des colonnes */
            }
            .col-lg-4, .col-lg-8, .col-lg-12, .col-lg-11, .col-lg-9, .col-lg-2, .col-lg-1 {
                padding: 0 15px;
            }
            .col-lg-4 {
                flex: 0 0 33.33%; /* 4/12 */
                max-width: 33.33%;
                height: 150px;
            }
            .col-lg-8 {
                flex: 0 0 66.67%; /* 8/12 */
                max-width: 66.67%;
                height: 150px;
            }
            .col-lg-12 {
                flex: 0 0 100%; /* 12/12 */
                max-width: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                height: 15vh;
            }
            .col-lg-11 {
                flex: 0 0 91.67%; /* 11/12 */
                max-width: 91.67%;
            }
            .col-lg-9 {
                flex: 0 0 75%; /* 9/12 */
                max-width: 75%;
            }
            .col-lg-2 {
                flex: 0 0 16.67%; /* 2/12 */
                max-width: 16.67%;
            }
            .col-lg-1 {
                flex: 0 0 8.33%; /* 1/12 */
                max-width: 8.33%;
            }
            h1 {
                font-family: 'Comic Sans MS', cursive;
            }
            .content {
                padding-left: 10vw;
                font-family: 'Times New Roman', serif;
            }
            .note {
                font-style: italic;
            }
        </style>

    </head>

<body>

    <section class="section">
        {{-- en-tête --}}
        <div class="row">
            <div class="col-lg-4">
                <img src="assets/images/logo.png" alt="logo_facmed" style="height: 100%;">
            </div>
            <div class="col-lg-8" style="padding-left: 8%;">
                <img src="assets/images/en-tete.png" alt="en-tête" style="height: 100%;">
            </div>
        </div>

        {{-- titre --}}
        <div class="row">
            <div class="col-lg-12">
                <h1>CERTIFICAT DE SCOLARITE</h1>
            </div>
        </div>

        {{-- contenu --}}
        <div class="content">
            <div class="row">
                <div class="col-lg-1"></div>
                <div class="col-lg-11">Je soussigné, Doyen de la Faculté de Médecine d'Antananarivo, certifie que:</div>
            </div>
            <br><br>
            <div class="row">
                <div class="col-lg-2"><strong>M.</strong></div>
                <div class="col-lg-9"><strong>: RAKOTONDRAINIBE Renato Michel</strong></div>
            </div>

            <div class="row">
                <div class="col-lg-2"><strong>Fils (Fille) de</strong></div>
                <div class="col-lg-9">: RAKOTONDRAINIBE Sergio Michel</div>
            </div>

            <div class="row">
                <div class="col-lg-2"><strong>et de</strong></div>
                <div class="col-lg-9">: RANDIMBIARIVONY Fanjaniaina</div>
            </div>

            <div class="row">
                <div class="col-lg-2"><strong>Né (e) le</strong></div>
                <div class="col-lg-9">: 25 août 2001 <strong>à </strong> Avaradoha</div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    est régulièrement inscrit en <strong>PACES</strong> <span style="margin: 0 20px;">&nbsp;</span> - <span style="margin: 0 20px;">&nbsp;</span>
                    <strong>N° matricule: </strong> 31347
                </div>
            </div>

            <div class="row">
                <div class="col-lg-2"><strong>Mention</strong></div>
                <div class="col-lg-9">
                    : <strong>PHARMACIE</strong>
                    <span style="margin: 0 20px;">&nbsp;</span>
                    <strong>Parcours: </strong>
                    <strong>PHARMACIE</strong>
                </div>
            </div>
            <br><br>
            <div class="row">
                <div class="col-lg-1"></div>
                <div class="col-lg-11">
                    à la Faculté de Médecine d'Antananarivo. Année Universitaire <strong>2024-2025</strong>
                </div>
            </div>
            <br><br><br>
            <div class="row">
                <div class="col-lg-8"></div>
                <div class="col-lg-4">
                    Fait à Antananarivo, le
                </div>
            </div>
            <br><br><br>
            <div class="row">
                <div class="col-lg-4">
                    <div class="note">
                        <u>NB</u>: Il ne peut être délivré qu’un seul exemplaire. Il appartient à l’intéressé d’en faire des copies et les faire « certifiées conformes » à l’original.
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
