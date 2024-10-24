<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>S'authentifier</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">


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
				<form action="{{ route('authentification_contradictoire.controller') }}" method = "POST">
                    @csrf
                    <div class="logo">
					    <img src="{{ asset('assets/images/logo.png') }}" height="100%" alt="logo_fac_med">
				    </div>

                    <div class="pagetitle">
                        <h5>L'accord d'un tiers responsable est nécessaire pour effectuer cette action</h5>
                        @error('connexion')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror
                    </div>

                    <div class="credentials">
                            <!-- Email Address -->
					    <div class="form-group credentials-el">
					    	<input type="text" name="email" placeholder="Votre adresse e-mail" class="form-control">
							<x-input-error :messages="$errors->get('email')" class="mt-2" />
					    </div>

                        <!-- Password -->
					    <div class="form-group credentials-el">
					    	<input type="password" name="password" placeholder="Mot de passe" class="form-control">
					    	<i class="zmdi zmdi-lock"></i>
							<x-input-error :messages="$errors->get('password')" class="mt-2" />
					    </div>

                    </div>


                    <div style="display: flex" class="boutons">
                        <button type="submit" class="w-100">S'authentifier
					    	<i class="zmdi zmdi-arrow-right"></i>
					    </button>
                    </div>
				</form>

			</div>
		</div>

        <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('assets/js/main.js') }}"></script>
	</body>
</html>
