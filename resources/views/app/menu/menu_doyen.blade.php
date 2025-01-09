<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
              <i class="bi bi-graph-up"></i><span>Tableau de bord</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
          </li><!-- End Components Nav -->


      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#examens" data-bs-toggle="collapse" href="#">
          <i class="bi bi-pencil-square"></i><span>Saisie des notes</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="examens" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('controle_saisie_note') }}">
              <i class="bi bi-circle"></i><span>Controle de la saisie des notes</span>
            </a>
          </li>

          <li>
            <a href="{{ route('interface_saisie_notes') }}">
              <i class="bi bi-circle"></i><span>Interface de Saisie des notes</span>
            </a>
          </li>

          <li>
            <a href="{{ route('controle_verification_note') }}">
              <i class="bi bi-circle"></i><span>Controle de la vérification des notes</span>
            </a>
          </li>

          <li>
            <a href="{{ route('interface_verification_notes') }}">
              <i class="bi bi-circle"></i><span>Interface de vérification des notes</span>
            </a>
          </li>

        </ul>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#entetes" data-bs-toggle="collapse" href="#">
          <i class="bi bi-justify"></i><span>Sasie des en-têtes</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="entetes" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('controle_saisie_entete') }}">
              <i class="bi bi-circle"></i><span>Controle de la saisie des en-têtes</span>
            </a>
          </li>

          <li>
            <a href="{{ route('interface_saisie_entete') }}">
              <i class="bi bi-circle"></i><span>Interface de Saisie des en-têtes</span>
            </a>
          </li>

          <li>
            <a href="{{ route('controle_verification_entete') }}">
              <i class="bi bi-circle"></i><span>Controle de la vérification des en-têtes</span>
            </a>
          </li>

          <li>
            <a href="{{ route('interface_verification_entete') }}">
              <i class="bi bi-circle"></i><span>Interface de vérifcation des en-têtes</span>
            </a>
          </li>

        </ul>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#resultats" data-bs-toggle="collapse" href="#">
          <i class="bi bi-bar-chart"></i><span>Résultats des évaluations individuelles</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="resultats" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('notes.generer_resultats.page') }}">
              <i class="bi bi-circle"></i><span>Générer les résultats d'évaluation</span>
            </a>
          </li>

          <li>
            <a href="{{ route('notes.get_resultats.page') }}">
              <i class="bi bi-circle"></i><span>Consulter les résultats d'évaluation</span>
            </a>
          </li>

          <li>
            <a href="{{ route('notes.down_resultats_specifique.page') }}">
              <i class="bi bi-circle"></i><span>Télécharger des résultats d'examen</span>
            </a>
          </li>




        </ul>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#resultats_au" data-bs-toggle="collapse" href="#">
          <i class="bi bi-bar-chart"></i><span>Résultats sur l'année universitaire</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="resultats_au" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('notes.generer_resultats_au.page') }}">
              <i class="bi bi-circle"></i><span>Générer les résultats des évaluations avant le repêchage</span>
            </a>
          </li>

          <li>
            <a href="{{ route('notes.get_resultats_avant_repechage.page') }}">
              <i class="bi bi-circle"></i><span>Consulter les résultats des evaluations avant le repechage</span>
            </a>
          </li>

          <li>
            <a href="{{ route('notes.get_liste_repechage.page') }}">
              <i class="bi bi-circle"></i><span>Consulter la liste de repechage</span>
            </a>
          </li>

          <li>
            <a href="{{ route('notes.down_liste_repechage.form') }}">
              <i class="bi bi-circle"></i><span>Télécharger les listes de repêchage</span>
            </a>
          </li>

          <li>
            <a href="{{ route('notes.down_liste_appel.form') }}">
              <i class="bi bi-circle"></i><span>Télécharger les listes d'appel au repêchage</span>
            </a>
          </li>

          <li>
            <a href="{{ route('notes.get_resultats_avant_deliberation.form') }}">
              <i class="bi bi-circle"></i><span>Consulter les résultats avant délibération</span>
            </a>
          </li>


        </ul>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#deliberation" data-bs-toggle="collapse" href="#">
          <i class="bi bi-file-earmark-check"></i><span>Délibération</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="deliberation" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('notes.deliberation.controle') }}">
              <i class="bi bi-circle"></i><span>Controle des délibérations</span>
            </a>
          </li>

          <li>
            <a href="{{ route('notes.interface_deliberation.form') }}">
              <i class="bi bi-circle"></i><span>Interface de délibération</span>
            </a>
          </li>




        </ul>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#res_def" data-bs-toggle="collapse" href="#">
          <i class="bi bi-bar-chart"></i><span>Résultats définitifs</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="res_def" class="nav-content collapse " data-bs-parent="#sidebar-nav">
            <li>
                <a href="{{ route('notes.preparer_resultats_definitifs.form') }}">
                  <i class="bi bi-circle"></i><span>Préparer les résultats_définitifs</span>
                </a>
            </li>

          <li>
            <a href="{{ route('notes.resultats_definitifs_form') }}">
              <i class="bi bi-circle"></i><span>Consulter les résultats_définitifs</span>
            </a>
          </li>

          <li>
            <a href="{{ route('notes.get_liste_admis.form') }}">
              <i class="bi bi-circle"></i><span>Télécharger la liste des admis</span>
            </a>
          </li>

          <li>
            <a href="{{ route('notes.get_listes_redoublants.form') }}">
              <i class="bi bi-circle"></i><span>Télécharger la liste des redoublants</span>
            </a>
          </li>

          <li>
            <a href="{{ route('notes.get_listes_triplants.form') }}">
              <i class="bi bi-circle"></i><span>Télécharger la liste des triplants</span>
            </a>
          </li>

          <li>
            <a href="{{ route('notes.get_listes_exclus.form') }}">
              <i class="bi bi-circle"></i><span>Télécharger la liste des exclus</span>
            </a>
          </li>





        </ul>
      </li>
    </ul>

  </aside>
