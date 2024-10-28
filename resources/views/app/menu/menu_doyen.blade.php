<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
              <i class="bi bi-book-half"></i><span>Tableau de bord</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
          </li><!-- End Components Nav -->


      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#examens" data-bs-toggle="collapse" href="#">
          <i class="bi bi-book-half"></i><span>Examen</span><i class="bi bi-chevron-down ms-auto"></i>
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
    </ul>

  </aside>
