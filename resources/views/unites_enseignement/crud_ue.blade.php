@extends('app.admin_app_layout')
@section('title', 'Définition des unités d\'enseigenement')

@section('content')

    <section class="section">
        @if($errors->get('iderror'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-octagon me-1"></i>
                    {{ $errors }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
        @endif
    <div class="card">
            <div class="card-body">

              <!-- Table with stripped rows -->
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th scope="col">Nom</th>
                    <th scope="col">Adresse mail</th>
                    <th scope="col">Type de profil</th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>
                <tbody>
                @foreach($users as $i=>$user)
                  <tr>
                    <th scope="row">{{ $user->name }}</th>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role->nom_role }}</td>
                    <td>
                      <form method="POST" action="{{ route('delUser') }}" id="del-form-{{ $i }}">
                        @csrf
                        <input type="hidden" name="id_user" value="{{ $user->id }}">
                        <i  class="bi bi-trash delButton" style="color:red;" data-bs-toggle="modal" data-bs-target="#authModalListUsers" data-num="{{ $i }}"></i>

                      </form>
                    </td>
                  </tr>
                @endforeach
                </tbody>
              </table>
              <!-- End Table with stripped rows -->

            </div>

          <!-- Modal pour authentification -->
          <div class="card" id="modal">
            <div class="card-body">

              <!-- Vertically centered Modal -->

              <div class="modal fade" id="authModalListUsers" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Veuillez vous authentifier</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <div class="card">
                        <div class="card-body">


                          <!-- General Form Elements -->
                          <form id="DelUserValidationAdminForm">
                            <div class="row mb-3">
                              <label for="inputEmail" class="col-sm-4 col-form-label">Email</label>
                              <div class="col-sm-8">
                                <input name = "email" type="email" class="form-control">
                              </div>
                            </div>
                            <div class="row mb-3">
                              <label for="inputPassword"  class="col-sm-4 col-form-label">Mot de passe</label>
                              <div class="col-sm-8">
                                <input name="password" type="password" class="form-control">
                              </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-danger" onClick>Supprimer l'utilisateur</button>
                            </div>

                          </form><!-- End General Form Elements -->

                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div><!-- End Vertically centered Modal-->

            </div>
          </div>

    </section>
    <script>
        var num;
        delButtons = document.getElementsByClassName('delButton');
        let delButton;
        for(let i=0;i<delButtons.length; i++){
          delButton = delButtons[i];
          delButton.addEventListener('click',function(event){
              //console.log('clic');
              source = event.target;
              num = source.getAttribute('data-num');
              //console.log(num);
          });
        }


      	document.getElementById('DelUserValidationAdminForm').addEventListener('submit', function(e) {
				    e.preventDefault();

				    const formData = new FormData(this);
				    fetch("{{ route('authAdmin') }} ", {
				        method: 'POST',
				        body: formData
				    })
				    .then(response => response.json())
				    .then(data => {
				        console.log('Authentification:', data);

						if(data.estAuthentifie === true){
                            let idform = "del-form-"+num;
                            console.log(idform);
							document.getElementById(idform).submit();
                            //window.location.reload();
						}
						else{
							alert('Les identifiants administrateur erronés');
						}
				    })
				    .catch((error) => {
				        console.error('Erreur:', error);
				    });
				});
    </script>


@endsection
