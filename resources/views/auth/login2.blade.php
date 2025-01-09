<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Se connecter</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<!-- MATERIAL DESIGN ICONIC FONT -->
		<link rel="stylesheet" href="fonts/material-design-iconic-font/css/material-design-iconic-font.min.css">

		<!-- STYLE CSS -->
		<link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
	</head>

	<body>

		<div class="wrapper" style="background-image: url({{ asset('assets/images/facmed2.png') }});" >
			<div class="inner">
				<div class="image-holder" >
					<img src="{{ asset('assets/images/facmed1.png') }}" alt="vue_globale_esplanade_et_piscine" >
				</div>
				<form action="{{ route('login') }}" method = "POST">
                    @csrf
                    <div class="logo">
					    <img src="{{ asset('assets/images/logo.png') }}" height="100%" alt="logo_fac_med">
				    </div>



						<div class="credentials">
                            <!-- Email Address -->
					    <div class="form-group credentials-el">
					    	<input type="text" name="email" placeholder="Votre adresse e-mail" class="form-control">
					    </div>
							<x-input-error :messages="$errors->get('email')" class="error-message" />


                        <!-- Password -->
					    <div class="form-group credentials-el">
					    	<input type="password" name="password" placeholder="Mot de passe" class="form-control">
					    	<i class="zmdi zmdi-lock"></i>
							<x-input-error :messages="$errors->get('password')" class="mt-2" />
					    </div>

                    </div>

                    <div style="display: flex; " class="boutons">
					    <button style='display:flex; justify-content:center; align-items:center;'>
								<a href="{{ route('register') }}">Créer un compte</a>

					    </button>
								<button style='display:flex; justify-content:center; align-items:center;' type="submit">Connexion
					    </button>
                    </div>
				</form>

			</div>
		</div>

	</body>
</html>
