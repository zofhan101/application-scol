<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
              <i class="bi bi-book-half"></i><span>Tableau de bord</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
          </li><!-- End Components Nav -->


      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#examens" data-bs-toggle="collapse" href="#">
          <i class="bi bi-book-half"></i><span>Saisie des notes</span><i class="bi bi-chevron-down ms-auto"></i>
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
          <i class="bi bi-book-half"></i><span>Sasie des en-têtes</span><i class="bi bi-chevron-down ms-auto"></i>
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
          <i class="bi bi-book-half"></i><span>Résultats des évaluations individuelles</span><i class="bi bi-chevron-down ms-auto"></i>
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
          <i class="bi bi-book-half"></i><span>Résultats sur l'année universitaire</span><i class="bi bi-chevron-down ms-auto"></i>
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
          <i class="bi bi-book-half"></i><span>Délibération</span><i class="bi bi-chevron-down ms-auto"></i>
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
    </ul>

  </aside>
