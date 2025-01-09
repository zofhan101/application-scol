<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#inscriptions" data-bs-toggle="collapse" href="#">
          <i class="bi bi-book-half"></i><span>Inscriptions</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="inscriptions" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('import_selectionnes_page') }}">
              <i class="bi bi-circle"></i><span>Import liste sélectionnés</span>
            </a>
          </li>

          <li>
            <a href="{{ route('check_admission') }}">
              <i class="bi bi-circle"></i><span>Inscription en PACES</span>
            </a>
          </li>


          <li>
            <a href="{{ route('annuler_inscription_form') }}">
              <i class="bi bi-circle"></i><span>Annulation d'inscription</span>
            </a>
          </li>

          <li>
            <a href="{{ route('transfert_etu_form') }}">
              <i class="bi bi-circle"></i><span>Inscription d'un étudiant transféré</span>
            </a>
          </li>

          <li>
            <a href="{{ route('liste_inscrits_form') }}">
              <i class="bi bi-circle"></i><span>Liste des inscrits</span>
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
        <a class="nav-link collapsed" data-bs-target="#documents" data-bs-toggle="collapse" href="#">
          <i class="bi bi-people-fill"></i><span>Documents sur l'inscription</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="documents" class="nav-content collapse " data-bs-parent="#sidebar-nav">
            <li>
                <a href="{{ route('check_inscription_form') }}">
                  <i class="bi bi-circle"></i><span>Cerificat de scolarité</span>
                </a>
              </li>

              <li>
                <a href="{{ route('check_inscription_form_attestation') }}">
                  <i class="bi bi-circle"></i><span>Attestation d'inscription</span>
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
      </li><!-- End Components Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#examen" data-bs-toggle="collapse" href="#">
          <i class="bi bi-book-half"></i><span>Saisie des notes</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="examen" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('interface_saisie_notes') }}">
              <i class="bi bi-circle"></i><span>Interface de Saisie des notes</span>
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
        <a class="nav-link collapsed" data-bs-target="#resultats" data-bs-toggle="collapse" href="#">
          <i class="bi bi-book-half"></i><span>Résultats des examens</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="resultats" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('notes.get_resultats.page') }}">
              <i class="bi bi-circle"></i><span>Consulter les résultats d'examen</span>
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

          <li>
            <a href="{{ route('notes.get_releve_notes.form') }}">
              <i class="bi bi-circle"></i><span>Obtenir des relevés de notes</span>
            </a>
          </li>

          <li>
            <a href="{{ route('notes.down_resultats_ent.form') }}">
              <i class="bi bi-circle"></i><span>Télécharger le CSV pour l'export vers ENT</span>
            </a>
          </li>

          <li>
            <a href="{{ route('notes.import_resultats_paces.form') }}">
              <i class="bi bi-circle"></i><span>Importer les résultats du concours PACES</span>
            </a>
          </li>









        </ul>
      </li>





    </ul>

  </aside><!-- End Sidebar-->
