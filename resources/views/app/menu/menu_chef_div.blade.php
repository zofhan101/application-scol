<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#inscriptions" data-bs-toggle="collapse" href="#">
          <i class="bi bi-book-half"></i><span>Inscriptions</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="inscriptions" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('check_admission') }}">
              <i class="bi bi-circle"></i><span>Inscription en PACES</span>
            </a>
          </li>

          <li>
            <a href="{{ route('transfert_etu_form') }}">
              <i class="bi bi-circle"></i><span>Inscription d'un étudiant transféré</span>
            </a>
          </li>

          <li>
            <a href="{{ route('get_prochaine_inscription.form') }}">
              <i class="bi bi-circle"></i><span>Réinscription</span>
            </a>
          </li>


        </ul>
      </li><!-- End Components Nav -->


      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#etudiants" data-bs-toggle="collapse" href="#">
          <i class="bi bi-people-fill"></i><span>Etudiants</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="etudiants" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('maj_etu_search_form') }}">
              <i class="bi bi-circle"></i><span>Mise à jour des informations</span>
            </a>
          </li>
        </ul>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#resultats_examen" data-bs-toggle="collapse" href="#">
          <i class="bi bi-people-fill"></i><span>Examens</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="resultats_examen" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('interface_saisie_notes') }}">
              <i class="bi bi-circle"></i><span>Interface de saisie des notes</span>
            </a>
          </li>

          <li>
            <a href="{{ route('interface_verification_notes') }}">
              <i class="bi bi-circle"></i><span>Interface de vérification des notes</span>
            </a>
          </li>

          <li>
            <a href="{{ route('notes.enregistrement_stage') }}">
              <i class="bi bi-circle"></i><span>Saisie des notes de stage</span>
            </a>
          </li>

        </ul>
      </li><!-- End Components Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#entetes" data-bs-toggle="collapse" href="#">
          <i class="bi bi-book-half"></i><span>Sasie des en-têtes</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="entetes" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('interface_saisie_entete') }}">
              <i class="bi bi-circle"></i><span>Interface de Saisie des en-têtes</span>
            </a>
          </li>

          <li>
            <a href="{{ route('interface_verification_entete') }}">
              <i class="bi bi-circle"></i><span>Interface de vérification des en-têtes</span>
            </a>
          </li>

        </ul>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#res_def" data-bs-toggle="collapse" href="#">
          <i class="bi bi-book-half"></i><span>Résultats définitifs</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="res_def" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('notes.resultats_definitifs_form') }}">
              <i class="bi bi-circle"></i><span>Consulter les résultats_définitifs</span>
            </a>
          </li>

          <li>
            <a href="{{ route('notes.get_releve_notes.form') }}">
              <i class="bi bi-circle"></i><span>Obtenir des relevés de notes</span>
            </a>
          </li>


        </ul>
      </li>





    </ul>

  </aside><!-- End Sidebar-->
