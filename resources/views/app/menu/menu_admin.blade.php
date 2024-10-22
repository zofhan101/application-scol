<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link " href="index.html">
          <i class="bi bi-grid"></i>
          <span>Tableau de bord</span>
        </a>
      </li><!-- End Dashboard Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-menu-button-wide"></i><span>Gestion des utilisateurs</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="components-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('listeUsers') }}">
              <i class="bi bi-circle"></i><span>Liste des utilisateurs</span>
            </a>
          </li>

        </ul>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-calendar-plus"></i><span>Année universitaire</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="components-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('au_form') }}">
              <i class="bi bi-circle"></i><span>Ouvrir une nouvelle A.U.</span>
            </a>
          </li>

          <li>
            <a href="{{ route('au_en_cours') }}">
              <i class="bi bi-circle"></i><span>A.U. en cours</span>
            </a>
          </li>

          <li>
            <a href="{{ route('create_exam_form') }}">
              <i class="bi bi-circle"></i><span>Définition examens de l'A.U.</span>
            </a>
          </li>

        </ul>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-menu-button-wide"></i><span>Unités d'enseignement</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="components-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('crud_ue') }}">
              <i class="bi bi-circle"></i><span>Définition des U.E. par année</span>
            </a>
          </li>

          <li>
            <a href="{{ route('codes_barres') }}">
              <i class="bi bi-circle"></i><span>Codes-barres pour les copies d'examen</span>
            </a>
          </li>

        </ul>
      </li>


    </ul>

  </aside><!-- End Sidebar-->
