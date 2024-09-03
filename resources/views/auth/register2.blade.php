<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Créer un compte</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
		<!-- MATERIAL DESIGN ICONIC FONT -->
		<link rel="stylesheet" href="fonts/material-design-iconic-font/css/material-design-iconic-font.min.css">

		<!-- STYLE CSS -->
		<link rel="stylesheet" href="{{ asset('assets/css/register.css') }}">
	</head>

	<body>
	
		<div class="wrapper" style="background-image: url('/assets/images/facmed2.png');" >
			<div class="inner">
				<div class="image-holder" >
					<img src="/assets/images/facmed1.png" alt="vue_globale_esplanade_et_piscine" >
				</div>
                
				<form id="registerForm" method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="logo">
					    <img src="/assets/images/logo.png" height="100%" alt="logo_fac_med">
				    </div>
                    
                    <div class= "titre">
                        <h3>Création de profil</h3>
                    </div>
					
                    
                    <div class="credentials">
                            <!-- Name -->
					    <div class="form-group credentials-el">
					    	<input type="text" placeholder="Votre nom" name="name" class="form-control">
							<x-input-error :messages="$errors->get('name')" class="mt-2" />
					    </div>

                            <!-- Email Address -->
					    <div class="form-group credentials-el">
					    	<input type="text" placeholder="Votre adresse e-mail" name="email" class="form-control">
							<x-input-error :messages="$errors->get('email')" class="mt-2" />
					    </div>
						
						<!-- Profil -->
						<div class="form-group credentials-el">
							<div>
								<span>Type de profil </span>
								<select name="role_id">
								
    								@foreach($roles as $role)
        								<option value="{{ $role->id }}">{{ $role->nom_role }}</option>
    								@endforeach
								</select>
							</div>
							
						</div>

                        <!-- Password -->					
					    <div class="form-group credentials-el">
					    	<input type="password" placeholder="Choisissez un mot de passe" name="password" class="form-control">
					    	<i class="zmdi zmdi-lock"></i>
							<x-input-error :messages="$errors->get('password')" class="mt-2" />
					    </div>

                          <!-- Password  confirmation -->					
					    <div class="form-group credentials-el">
					    	<input type="password" placeholder="Confirmez votre mot de passe" name="password_confirmation" class="form-control">
					    	<i class="zmdi zmdi-lock"></i>
							<x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
					    </div>
    
                    </div>
                     

                    <div style="display: flex" class="boutons">
					    <button>
					    	<i class="zmdi zmdi-arrow-right"></i>
                            <a href="{{ route('login') }}">Se connecter</a>
					    </button>
                        <button id="openModal" type="submit">Créer mon compte
					    	<i class="zmdi zmdi-arrow-right"></i>
					    </button>
                    </div>
				</form>
                
			</div>
		</div>

		<!-- MODAL POUR VALIDATION ADMIN -->
		<div id="myModal" class="modal">
    		<div class="modal-content">
        		<span class="close">&times;</span>

				<div class= "titre">
                    <h3>Identifiants de l'administrateur</h3>
                </div>
        		<form id="myForm">
				@csrf
				<div class="credentials">
                            <!-- Email Address -->
					    <div class="form-group credentials-el">
					    	<input type="text" placeholder="Adresse e-mail" name="email" class="form-control">
					    </div>
						

                        <!-- Password -->					
					    <div class="form-group credentials-el">
					    	<input type="password" placeholder="Mot de passe" name="password" class="form-control">
					    	<i class="zmdi zmdi-lock"></i>
					    </div>

    
                </div>
            		
					<button  type="submit">Valider
					    	<i class="zmdi zmdi-arrow-right"></i>
					</button>
        		</form>
    		</div>
		</div>

		<script>

				var registerForm = document.getElementById("registerForm");
				registerForm.addEventListener("submit", function(event) {
				
				      event.preventDefault();
				
				
				})

				document.getElementById('openModal').addEventListener('click', function() {
				    document.getElementById('myModal').style.display = "block";
				});

				document.getElementsByClassName('close')[0].addEventListener('click', function() {
				    document.getElementById('myModal').style.display = "none";
				});

				window.onclick = function(event) {
				    if (event.target == document.getElementById('myModal')) {
				        document.getElementById('myModal').style.display = "none";
				    }
				};

				document.getElementById('myForm').addEventListener('submit', function(e) {
				    e.preventDefault(); 
				
				    const formData = new FormData(this);
				    fetch("{{ route('authAdmin') }} ", {
				        method: 'POST',
				        body: formData
				    })
				    .then(response => response.json())
				    .then(data => {
				        console.log('Authentification:', data);
				        document.getElementById('myModal').style.display = "none";
						if(data.estAuthentifie === true){
							document.getElementById("registerForm").submit();
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
	</body>
</html>