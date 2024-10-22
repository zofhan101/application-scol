<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-book-half"></i><span>Inscriptions</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="components-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
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

        </ul>
      </li><!-- End Components Nav -->


      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-people-fill"></i><span>Etudiants</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="components-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('maj_etu_search_form') }}">
              <i class="bi bi-circle"></i><span>Mise à jour des informations</span>
            </a>
          </li>
        </ul>
      </li><!-- End Components Nav -->



    </ul>

  </aside><!-- End Sidebar-->
