<aside id="sidebar" class="sidebar">
  <ul class="sidebar-nav" id="sidebar-nav">

    <li class="nav-item">
      <a class="nav-link" href="index.html">
        <i class="bi bi-graph-up"></i>
        <span>Tableau de bord</span>
      </a>
    </li><!-- End Dashboard Nav -->

    <li class="nav-item">
      <a class="nav-link" id="refresh-views-link" href="javascript:void(0);">
        <i class="bi bi-arrow-clockwise" id="grid-icon"></i>
        <img src="{{ asset('assets/images/loading.gif') }}" alt="Loading..." class="d-none" id="loading-spinner" style="width: 20px; height: 20px; margin-right: 2vh;">
        <i class="bi bi-check-circle text-success d-none" id="success-icon"></i>
        <span>Mettre à jour la liste des inscrits et des matières</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#component-users" data-bs-toggle="collapse" href="#">
        <i class="bi bi-person-fill"></i><span>Gestion des utilisateurs</span><i class="bi bi-chevron-down ms-auto"></i>
      </a>      
      <ul id="component-users" class="nav-content collapse" data-bs-parent="#sidebar-nav">
        <li>
          <a href="{{ route('listeUsers') }}">
            <i class="bi bi-circle"></i><span>Liste des utilisateurs</span>
          </a>
        </li>
      </ul>
    </li>

    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#components-au" data-bs-toggle="collapse" href="#">
        <i class="bi bi-calendar-plus"></i><span>Année universitaire</span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="components-au" class="nav-content collapse" data-bs-parent="#sidebar-nav">
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
      <a class="nav-link collapsed" data-bs-target="#components-ue" data-bs-toggle="collapse" href="#">
        <i class="bi bi-journal-bookmark-fill"></i><span>Unités d'enseignement</span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      
      <ul id="components-ue" class="nav-content collapse" data-bs-parent="#sidebar-nav">
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

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const refreshLink = document.getElementById('refresh-views-link');
    const spinner = document.getElementById('loading-spinner');
    const gridIcon = document.getElementById('grid-icon');
    const successIcon = document.getElementById('success-icon');

    refreshLink.addEventListener('click', async function () {
        // Show the GIF spinner and hide the grid and success icons
        spinner.classList.remove('d-none');
        successIcon.classList.add('d-none');
        gridIcon.classList.add('d-none');

        try {
            // Call the API to refresh views
            const response = await fetch('{{ route('refreshViews') }}');
            if (!response.ok) {
                throw new Error('Une erreur est survenue lors de la mise à jour.');
            }
            const data = await response.json();

    

            // Hide the spinner and show the success icon
            spinner.classList.add('d-none');
            successIcon.classList.remove('d-none');
        } catch (error) {
            // Show an error alert
            alert(error.message);

            // Hide the spinner and keep the success icon hidden
            spinner.classList.add('d-none');
            gridIcon.classList.remove('d-none');
        }
    });
});

</script>