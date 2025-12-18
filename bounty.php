


<!-- cache at: 2025-12-03 10:26:01 -->
	<!DOCTYPE html>
	<html lang="fr">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Bounty Chat, chat gratuit et sans inscription.</title>
		<meta name="description" content="Bounty Chat est un chat sans inscription permettant de discuter librement avec d'autres utilisateurs. Simple, rapide, et anonyme. Rejoignez la conversation sans aucune contrainte !">
		<meta name="keywords" content="chat sans inscription, Bounty Chat, discuter anonymement, chat en ligne, conversation en direct, chat gratuit, discussion instantanée, tchat sans compte, communauté en ligne">
		<link rel="icon" href="/assets/img/logo.svg" type="image/svg+xml">
		
		
		<style type="text/css">@font-face {font-family:Outfit;font-style:normal;font-weight:100 900;src:url(/cf-fonts/v/outfit/5.0.11/latin/wght/normal.woff2);unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+2074,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD;font-display:swap;}@font-face {font-family:Outfit;font-style:normal;font-weight:100 900;src:url(/cf-fonts/v/outfit/5.0.11/latin-ext/wght/normal.woff2);unicode-range:U+0100-02AF,U+0304,U+0308,U+0329,U+1E00-1E9F,U+1EF2-1EFF,U+2020,U+20A0-20AB,U+20AD-20CF,U+2113,U+2C60-2C7F,U+A720-A7FF;font-display:swap;}@font-face {font-family:Parisienne;font-style:normal;font-weight:400;src:url(/cf-fonts/s/parisienne/5.0.11/latin/400/normal.woff2);unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+2074,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD;font-display:swap;}@font-face {font-family:Parisienne;font-style:normal;font-weight:400;src:url(/cf-fonts/s/parisienne/5.0.11/latin-ext/400/normal.woff2);unicode-range:U+0100-02AF,U+0304,U+0308,U+0329,U+1E00-1E9F,U+1EF2-1EFF,U+2020,U+20A0-20AB,U+20AD-20CF,U+2113,U+2C60-2C7F,U+A720-A7FF;font-display:swap;}</style>
		<link rel="manifest" href="/manifest.json">
		<link rel="stylesheet" href="/assets/css/bootstrap.min.css">
		<link rel="stylesheet" href="/assets/css/bootstrap-toaster.min.css">
		<link rel="stylesheet" href="/assets/css/main.css">
		<link rel="stylesheet" href="/assets/css/jquery-ui.css">
		<style>

			#panel_login input,
			#panel_login select {
				background: var(--panel-input-bg);
				color: var(--panel-input-color);
			}

			#panel_login button {
				/* background: black; */ /* You can add a variable here if needed */
			}

			#gif-search {
				padding: 8px;
				border: 1px solid var(--gif-border);
				width: 100%;
			}

			#giphy-results img {
				border-radius: 8px;
				transition: transform 0.2s;
			}

			#giphy-results img:hover {
				transform: scale(1.1);
			}

			.arrow {
				position: absolute;
				top: 50%;
				transform: translateY(-50%);
				background-color: var(--arrow-bg);
				color: var(--arrow-color);
				font-size: 24px;
				border: none;
				padding: 10px;
				cursor: pointer;
				user-select: none;
				z-index: 1000;
			}

			#prev-image {
				left: 10px;
			}

			#next-image {
				right: 10px;
			}

			#image-modal {
				position: fixed;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				background: var(--modal-bg);
				display: flex;
				justify-content: center;
				align-items: center;
			}

			@media (min-width: 768px) {
				#installPWA {
					display: none !important;
				}
			}
		</style>
		<script src="/assets/js/jquery-3.7.1.min.js" type="43c79d86114da1905a54a368-text/javascript"></script>
		<script src="/assets/js/main.js" type="43c79d86114da1905a54a368-text/javascript"></script>

	</head>
	<body>
	<div id="main">
		<style>

	.header {
		position: relative;
	}

	/*	.header::before,
		.header::after {
			content: "";
			background-image: url('/assets/img/noix.png');
			background-size: contain;
			background-repeat: no-repeat;
			width: 100px; !* Adjust the size as needed *!
			height: 100px; !* Adjust the size as needed *!
			position: absolute;
			top: 0;
			display: none; !* Hidden by default *!
		}*/

	.header::before {
		left: 0; /* Position top-left */
	}

	.header::after {
		right: 0; /* Position top-right */
	}

	/* Only show on desktop (min-width: 1024px) */
	@media (min-width: 1024px) {
		.header::before,
		.header::after {
			display: block; /* Show images on desktop */
		}
	}

	[data-state="login"] img {
		max-width: 100%;
	}

	[data-state="login"] .container {
		width: 100%;
		max-width: 1200px;
		height: 100dvh;
		display: flex;
		flex-direction: column;
		justify-content: center;
	}

	[data-state="login"] .header {
		text-align: center;
		margin-bottom: 0;
	}

	[data-state="login"] .header h1 {
		font-weight: bold;
		color: #5a2e19;
	}

	[data-state="login"] .header p {
		font-weight: bold;
		color: #5a2e19;
		margin-top: -10px;
	}

	[data-state="login"] .form-container form {
		background-color: #f5f5f5;
		padding: 20px;
		border-radius: 5px;
	}

	[data-state="login"] .form-container .btn {
		background-color: #5a2e19;
		color: white;
		width: 100%;
	}

	[data-state="login"] .ad-space {
		border: 1px solid black;
		min-height: 200px;
		text-align: center;
		line-height: 200px;
		margin-bottom: 20px;
	}

	[data-state="login"] .btn-brown {
		background: #5a2e19;
		color: white;
		margin-top: 1em;
	}

	[data-state="login"] .city-item {
		cursor: pointer;
		padding: 5px;
	}

	[data-state="login"] .city-item:hover {
		background: #EEE;
	}

	p.title_site {
		font-size: 1.8em
	}
	#seo {
		flex-grow: 1;
		overflow-y: auto;
	}

	@media (max-width: 720px) {
		div[data-state="login"] {
			font-size: 0.8em;
		}

		[data-state="login"] .ad-space {
			display: none;
		}

		p.title_site {
			margin: 0;
			font-size: 1.3em
		}
		#installPWA {
			display: none;
			margin: 1em;
			border: none;
			background: var(--color-primaire-1);
			color:white;
			padding: 0.5em;
		}
	}
	.gps-btn {
		position: absolute;
		right: 10px;
		top: 50%;
		transform: translateY(-50%);
		border: none;
		background: transparent;
		font-size: 1.2rem;
		cursor: pointer;
		color: #666;
		padding: 0;
		line-height: 1;
	}
	.gps-btn:hover {
		color: #000;
	}
	/* Prevent input text from going under the 📍 */
	#zip {
		padding-right: 2.2rem;
	}

</style>

<div data-state="login">
	<div class="container">
		<div class="header">
			<div><img src="/assets/img/bounty2.svg" alt="Bounty chat" title="LE TCHAT AVEC INSCRIPTION" height="120"></div>
			<p class="title_site">LE TCHAT AVEC INSCRIPTION </p>
			<div class="d-flex justify-content-center"><button id="installPWA" class="flex-grow-1" >Install Application 🥥Bounty Chat🥥</button></div>
		</div>
		<div id="adult-warning" class="align-items-center alert alert-warning alert-dismissible text-center " role="alert" style="display: none;">
			<b>Attention  :</b> Ce site est réservé aux adultes. Veuillez confirmer que vous avez 18 ans.<br>Bounty chat abandonne l'anonymat.			<button type="button" class="btn-close close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>



		<div class="row justify-content-center align-items-center">
			<div id="panel_login" class="col-md-6">
				<div data-state="guest">
					<form id="form_guest_login" class="width100">
						<input type="hidden" name="a" value="loginGuestOrRegister">
						<div class="mb-3 d-flex justify-content-between">
							<div class="d-flex flex align-items-center justify-content-center">
								<input type="radio" id="role_guest" name="role" checked value="guest">
								<label for="role_guest">Chat sans inscription</label>
							</div>

							<div class="d-flex flex align-items-center justify-content-center">
								<input type="radio" id="role_user" name="role" value="user">
								<label for="role_user">Créer compte certifié</label>
							</div>
						</div>
						<div id="creation_container"></div>

						<div class="mb-1">
							<label for="username" class="form-label">Pseudonyme</label>
							<input type="text" class="form-control" id="username" name="username" placeholder="Pseudonyme" required pattern="[A-Za-z0-9]{3,25}">
						</div>

						<div class="row mb-1" style="height: auto;">
							<div class="col-6 col-md-4">
								<label for="age" class="form-label">Age</label>
								<input type="number" class="form-control" id="age" name="age" placeholder="Age" min="18" maxlength="99" required>
							</div>
							<div class="col-6 col-md-4">
								<label for="country" class="form-label">Pays</label>
								<select class="form-control" id="country" name="country" required>
									<option value="fr" selected>France</option>
									<option value="be">Belgique</option>
									<option value="ch">Suisse</option>
									<option value="lu">Luxembourg</option>
								</select>
							</div>
							<div class="col-12 col-md-4">
								<label for="zip" class="form-label">
									Code postal								</label>
								<div class="input-group position-relative">
									<input type="number" class="form-control pe-5" id="zip" name="zip" placeholder="Code postal" required>
									<button type="button" id="btn-gps" class="gps-btn" aria-label="Use GPS">📍</button>
								</div>
								<small id="gps-status" class="text-muted d-none"></small>
								<div id="cities"></div>
								<input type="hidden" name="city_id" id="city_id">
							</div>
						</div>

						<div class="mb-1">
							<div class="d-flex justify-content-around">
								<div>
									<input type="radio" id="male" name="gender" value="male" checked>
									<label for="male">
										<svg width="16" height="16" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M13 28C17.9706 28 22 23.9706 22 19C22 14.0294 17.9706 10 13 10C8.02944 10 4 14.0294 4 19C4 23.9706 8.02944 28 13 28Z" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
											<path d="M19.3625 12.6375L27 5" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
											<path d="M21 5H27V11" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
										Homme									</label>
								</div>
								<div>
									<input type="radio" id="female" name="gender" value="female">
									<label for="female">
										<svg width="16" height="16" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M16 21C20.9706 21 25 16.9706 25 12C25 7.02944 20.9706 3 16 3C11.0294 3 7 7.02944 7 12C7 16.9706 11.0294 21 16 21Z" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
											<path d="M16 21V30" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
											<path d="M11 26H21" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
										Femme									</label>
								</div>
								<div>
									<input type="radio" id="trans" name="gender" value="trans">
									<label for="trans">
										<svg width="16" height="16" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M15 21C19.1421 21 22.5 17.6421 22.5 13.5C22.5 9.35786 19.1421 6 15 6C10.8579 6 7.5 9.35786 7.5 13.5C7.5 17.6421 10.8579 21 15 21Z" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
											<path d="M15 21V29" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
											<path d="M11 25.5H19" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
											<path d="M21.5 3H26V7.5" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
											<path d="M20.55 8.45L26 3" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>

										Trans/Trav									</label>
								</div>
								<div>
									<input type="radio" id="couple" name="gender" value="couple">
									<label for="couple">
										<svg width="16" height="16" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M11 20C14.5899 20 17.5 17.0899 17.5 13.5C17.5 9.91015 14.5899 7 11 7C7.41015 7 4.5 9.91015 4.5 13.5C4.5 17.0899 7.41015 20 11 20Z" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
											<path d="M19.425 7.2375C19.9996 7.08166 20.5922 7.00181 21.1875 7C22.9115 7 24.5648 7.68482 25.7837 8.90381C27.0027 10.1228 27.6875 11.7761 27.6875 13.5C27.6875 15.2239 27.0027 16.8772 25.7837 18.0962C24.5648 19.3152 22.9115 20 21.1875 20" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
											<path d="M2 24.675C3.01493 23.2307 4.36255 22.0519 5.92901 21.2381C7.49547 20.4243 9.23477 19.9995 11 19.9995C12.7652 19.9995 14.5045 20.4243 16.071 21.2381C17.6375 22.0519 18.9851 23.2307 20 24.675" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
											<path d="M21.1875 20C22.9529 19.9989 24.6925 20.4232 26.2592 21.237C27.8258 22.0508 29.1733 23.2301 30.1875 24.675" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
										Couple									</label>
								</div>
							</div>

						</div>
						<button id="login_btn" type="button" class="btn btn-brown form-control ">
							<svg width="16" height="16" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M8.95 18L4 22V6C4 5.73478 4.10536 5.48043 4.29289 5.29289C4.48043 5.10536 4.73478 5 5 5H21C21.2652 5 21.5196 5.10536 21.7071 5.29289C21.8946 5.48043 22 5.73478 22 6V17C22 17.2652 21.8946 17.5196 21.7071 17.7071C21.5196 17.8946 21.2652 18 21 18H8.95Z" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M10 18V23C10 23.2652 10.1054 23.5196 10.2929 23.7071C10.4804 23.8946 10.7348 24 11 24H23.05L28 28V12C28 11.7348 27.8946 11.4804 27.7071 11.2929C27.5196 11.1054 27.2652 11 27 11H22" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>

							CHATTEZ !						</button>
						<div class="text-center">
							<button type="button" class="btn" onclick="if (!window.__cfRLUnblockHandlers) return false; setState('panel_login', 'login')" data-cf-modified-43c79d86114da1905a54a368-="">Déjà un compte ?</button>
															<button id="google_connect_btn" type="button" class="btn" onclick="if (!window.__cfRLUnblockHandlers) return false; window.location = '/google-connect';" data-cf-modified-43c79d86114da1905a54a368-="">
									<svg width="20" height="20" viewBox="0 0 533.5 544.3" xmlns="http://www.w3.org/2000/svg">
										<path fill="#4285f4" d="M533.5 278.4c0-17.7-1.6-35.1-4.6-51.8H272v97.5h146.9c-6.3 34.1-25 62.9-53.5 82.2v68h86.4c50.6-46.6 81.7-115.3 81.7-196z"/>
										<path fill="#34a853" d="M272 544.3c72.8 0 133.8-24.1 178.5-65.4l-86.4-68c-24 16.1-54.7 25.6-92.1 25.6-70.7 0-130.5-47.7-151.9-111.8H30v70.2c44.3 87.5 135 149.4 242 149.4z"/>
										<path fill="#fbbc04" d="M120.1 324.7c-10.1-30-10.1-62.2 0-92.2V162.3H30c-41.8 82.5-41.8 181.8 0 264.3l90.1-70.2z"/>
										<path fill="#ea4335" d="M272 107.7c39.6 0 75.1 13.6 103.1 40.3l77.4-77.4C405.8 24.1 344.8 0 272 0 165 0 74.3 61.9 30 149.3l90.1 70.2C141.5 155.4 201.3 107.7 272 107.7z"/>
									</svg>
									<span>Se connecter avec Google</span>
								</button>
													</div>
					</form>
				</div>
				<div data-state="login">

					<form id="form_user_login">
						<input type="hidden" name="a" value="login">
						<div class="mb-1">
							<label for="email_login" class="form-label">Votre email</label>
							<input type="email" class="form-control" id="email_login" name="email" placeholder="Votre email" required autocomplete="true">
						</div>
						<div class="mb-1">
							<label for="password_login" class="form-label">Mot de passe</label>
							<input type="password" class="form-control" id="password_login" name="password" placeholder="Mot de passe" required autocomplete="current-password">
						</div>

						<div>
							<button id="user_login_btn" type="button" class="btn btn-brown form-control">
								<svg width="16" height="16" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M8.95 18L4 22V6C4 5.73478 4.10536 5.48043 4.29289 5.29289C4.48043 5.10536 4.73478 5 5 5H21C21.2652 5 21.5196 5.10536 21.7071 5.29289C21.8946 5.48043 22 5.73478 22 6V17C22 17.2652 21.8946 17.5196 21.7071 17.7071C21.5196 17.8946 21.2652 18 21 18H8.95Z" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M10 18V23C10 23.2652 10.1054 23.5196 10.2929 23.7071C10.4804 23.8946 10.7348 24 11 24H23.05L28 28V12C28 11.7348 27.8946 11.4804 27.7071 11.2929C27.5196 11.1054 27.2652 11 27 11H22" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
								CHATTEZ !</button>
							<div class="d-flex justify-content-center">
								<button type="button" class="btn" onclick="if (!window.__cfRLUnblockHandlers) return false; setState('panel_login', 'forgotten', 'slide')" data-cf-modified-43c79d86114da1905a54a368-="">Mot de passe perdu ?</button>
							</div>
							<div class="d-flex justify-content-center fs-5 pb-3">
								<a href="javascript:setState('panel_login', 'guest')">Retour</a>
							</div>
						</div>
					</form>
				</div>
				<div data-state="forgotten">
					<form id="form_forgotten_email">
						<div class="mb-1">
							<label for="email_forgotten" class="form-label">Votre email</label>
							<input type="email" class="form-control" id="email_forgotten" name="email" placeholder="Votre email" required >
						</div>
						<input type="hidden" name="a" value="forgotten">
						<button id="forgotten_btn" type="button" class="btn btn-brown form-control ">Envoyez mot de passe !</button>
						<a href="javascript:setState('panel_login', 'login')">Retour</a>
					</form>
				</div>
			</div>
			
		</div>




		<footer class="d-flex gap-3 justify-content-center">
	<a href="/cgu" target="_blank">CGU</a>
	<a href="/confidentialite" target="_blank">Confidentialité</a>
	<a href="/blog">Blog</a>
	<a href="/contact" target="_blank">Contact</a>
	<a href="https://www.facebook.com/profile.php?id=61564144615768" target="_blank" aria-label="Compte facebook">
		<svg width="24" height="24" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M16 28C22.6274 28 28 22.6274 28 16C28 9.37258 22.6274 4 16 4C9.37258 4 4 9.37258 4 16C4 22.6274 9.37258 28 16 28Z" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
			<path d="M21 11H19C18.6056 10.9984 18.2147 11.0748 17.85 11.225C17.4853 11.3752 17.1539 11.5961 16.875 11.875C16.5961 12.1539 16.3752 12.4853 16.225 12.85C16.0748 13.2147 15.9983 13.6056 16 14V28" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
			<path d="M12 18H20" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>

	</a>
	<a href="https://x.com/Bountychat1" target="_blank" aria-label="Compte Twitter">
		<svg width="24" height="24" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M16 11C16 8.24997 18.3125 5.96247 21.0625 5.99997C22.0256 6.01108 22.9649 6.30011 23.7676 6.83231C24.5704 7.36451 25.2023 8.11722 25.5875 8.99997H30L25.9625 13.0375C25.7019 17.0932 23.9066 20.8974 20.9415 23.6768C17.9764 26.4562 14.0641 28.002 10 28C6 28 5 26.5 5 26.5C5 26.5 9 25 11 22C11 22 3 18 5 6.99997C5 6.99997 10 12 16 13V11Z" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
	</a>
	<a href="https://bounty.chat/blog/bounty-chat-notre-engagement-pour-la-protection-des-mineurs-en-ligne/">Protection mineurs</a>
</footer>		<div id="seo" class="mt-5">

	<h4>Bienvenue sur Bounty ! Votre espace de tchat en ligne libre et convivial </h4>
	<h5>Une plateforme de tchat gratuit pensée pour vous</h5>
	Bounty révolutionne le monde des conversations en ligne en proposant une expérience de tchat en ligne unique et innovante. Notre plateforme a été conçue pour répondre à toutes vos attentes en matière de
	communication instantanée. Que vous soyez à la recherche de nouvelles amitiés, d'échanges culturels ou simplement de moments de détente, Bounty est l'endroit idéal pour vous connecter avec des personnes
	partageant vos centres d'intérêt.
	<h5>La liberté d'un tchat gratuit avec inscription</h5>
	Fini les processus d'inscription fastidieux ! Bounty vous propose un tchat gratuit sans inscription pour une expérience fluide et immédiate. En quelques secondes, accédez à notre tchat direct et commencez à
	échanger avec des milliers d'utilisateurs. Cette simplicité d'accès fait de Bounty le choix privilégié pour ceux qui souhaitent profiter d'un tchat libre sans contraintes.
	<h5>Une expérience de tchat  sécurisée</h5>
	Votre vie privée est notre priorité. Sur Bounty, profitez d'un tchat anonyme qui garantit la confidentialité de vos échanges. Notre système de sécurité avancé protège vos conversations tout en vous
	permettant de vous exprimer librement. Choisissez votre pseudonyme et commencez à tchatter sans compromettre votre identité.
	<h5>Des fonctionnalités innovantes pour des échanges enrichis</h5>
	Tchat Cam pour des conversations plus authentiques
	Enrichissez vos discussions grâce à notre fonction tchat cam de dernière génération. Optez pour des conversations vidéo fluides et de haute qualité pour des échanges plus personnels et vivants. Notre
	technologie optimisée garantit une expérience sans latence, idéale pour des conversations naturelles et spontanées.
	Salons thématiques personnalisés
	Bounty propose une variété de salons de chat en ligne adaptés à tous les goûts et centres d'intérêt. Musique, cinéma, sport, culture, ou simplement discussions décontractées - trouvez votre communauté
	idéale parmi nos nombreux espaces de discussion.
	<h5>Une communauté vivante et respectueuse</h5>
	Notre tchat en ligne se distingue par sa communauté accueillante et bienveillante. Les modérateurs de Bounty veillent 24h/24 au respect des règles de convivialité pour garantir des échanges agréables et
	enrichissants. Que vous soyez nouveau ou utilisateur régulier, vous trouverez toujours une oreille attentive et des conversations stimulantes.
	<h5>Accessible sur tous vos appareils</h5>
	Restez connecté où que vous soyez ! Bounty s'adapte parfaitement à tous vos appareils. Notre tchat direct est optimisé pour les smartphones, tablettes et ordinateurs, vous permettant de poursuivre vos
	conversations en toute circonstance.
	<h5>Rejoignez l'aventure Bounty dès maintenant !</h5>
	Ne manquez pas l'opportunité de faire partie de la communauté de tchat la plus dynamique du web. Bounty vous offre un espace de tchat gratuit où convivialité rime avec liberté. Lancez-vous dans l'aventure
	et découvrez le plaisir des conversations authentiques dans un environnement moderne et sécurisé.
	Connectez-vous dès maintenant et découvrez pourquoi des milliers d'utilisateurs ont déjà choisi Bounty comme leur plateforme de tchat en ligne préférée !


</div>	</div>

	<script type="43c79d86114da1905a54a368-text/javascript">
		if (localStorage.getItem('registered')) {
			setState('panel_login', 'login');
		}
		$('input[name="role"]').change(function () {
			if ($(this).val() === 'guest') {
				$('#creation_container').empty();
			} else if ($(this).val() === 'user') {
				$('#creation_container').append(`
				<div id="email_password_container" class="row">
					<div class="col-6">
						<label for="email" class="form-label">${tra['my_email_label']}</label>
						<input type="email" class="form-control" id="email" name="email" placeholder="email" required>
					</div>
					<div class="col-6">
						<label for="password" class="form-label">${tra['my_password_label']}</label>
						<input type="password" class="form-control" id="password" name="password" placeholder="${tra['my_password_label']}" required minlength="6">
					</div>
				</div>
`);
			}
		});
		$zip = $('#zip');
		$country = $('#country');
		$('#username').val(localStorage.getItem('username'));
		$('#age').val(localStorage.getItem('age'));
		if (typeof localStorage.getItem('zip') == 'string') {
			$zip.attr('type', 'text');
			$zip.val(localStorage.getItem('zip'));
		}
		$('#city_id').val(localStorage.getItem('city_id'));
		const savedCountry = localStorage.getItem('country');
		if (savedCountry) {
			$country.val(savedCountry);
		}
		$(`input[name="gender"][value="${localStorage.getItem('gender')}"]`).prop('checked', true);

		$('#email_login').val(localStorage.getItem('email'));
		$('#password_login').val(localStorage.getItem('password'));

		$country.on('change', ()=> {
			$zip.val('');
		});

		$zip.on('input', function () {
			let zip = $(this).val();
			let country = $('#country').val();
			let expectedLength = {
				fr: 5,
				be: 4,
				lu: 4,
				ch: 4
			}[country] || 5;
			if (zip.length === expectedLength && /^\d+$/.test(zip)) {
				$.post('/ajax', { a: 'getCity', zip: zip, country: country }, (res) => {
					res = JSON.parse(res);
					$('#cities').empty();
					if (res.length === 1) {
						$('#city_id').val(res[0].id);
						$zip.attr('type', 'text');
						$zip.val(capitalizeFirstLetter(res[0].city));
					} else if (res.length > 1) {
						res.forEach((city) => {
							$('#cities').append(`<div class="city-item" data-id="${city.id}">${capitalizeFirstLetter(city.city)}</div>`);
						});
					}
				});
			}
		});

		$(document).on('click', '.city-item', function () {
			let cityId = $(this).data('id');
			$('#city_id').val(cityId);
			$zip.attr('type', 'text');
			$zip.val($(this).text());
			$('#cities').empty();
		});

		$('#login_btn').on('click', async () => {
			// ---------- local helper functions ----------
			async function waitForTurnstile(timeout = 8000, interval = 100) {
				const start = Date.now();
				while (!window.turnstile || typeof window.turnstile.render !== 'function') {
					if (Date.now() - start > timeout)
						throw new Error('turnstile_load_timeout');
					await new Promise(r => setTimeout(r, interval));
				}
			}

			async function getTurnstileToken() {
				// Render if not already rendered
				if (!window._tsGuestId) {
					window._tsGuestId = turnstile.render('#ts-login', {
						sitekey: '0x4AAAAAAB8G8hDycDh-KKzx',
						size: 'invisible',
						action: 'guest_login',
						theme: 'auto'
					});
				}

				// Execute invisible challenge and wait for token
				return await new Promise((resolve, reject) => {
					turnstile.execute(window._tsGuestId, {
						async: true,
						callback: token => resolve(token),
						'error-callback': () => reject(new Error('turnstile_error')),
						'timeout-callback': () => reject(new Error('turnstile_timeout'))
					});
				});
			}
			// ---------- end helpers ----------

			const $btn = $('#login_btn');
			if ($btn.prop('disabled')) return;
			$btn.prop('disabled', true);
			const originalText = $btn.text();
			$btn.html(`${originalText} <span class="spinner"></span>`);
			const $city_id = $('#city_id');
			if (!$city_id.val()) {
				bootbox.alert(tra['valid_zip_code_error']);
				resetButton();
				return;
			}
			localStorage.setItem('username', $('#username').val());
			localStorage.setItem('age', $('#age').val());
			localStorage.setItem('zip', $zip.val());
			localStorage.setItem('country', $country.val());
			localStorage.setItem('city_id', $city_id.val());
			localStorage.removeItem('registered');
			localStorage.setItem('gender', $('input[name="gender"]:checked').val());
			if (!localStorage.getItem('user_id')) {
				localStorage.setItem('user_id', Date.now() + Math.floor(Math.random() * 9000 + 1000));
			}

			if (form_guest_login.checkValidity()) {
				let body = new FormData(form_guest_login);
				body.append('user_id', localStorage.getItem('user_id'));
				body.append('description', localStorage.getItem('description') || '');
				let useTurnstile = false;
				if (useTurnstile) {
					const tsToken = await getTurnstileToken();
					body.append('cf-turnstile-response', tsToken); // REQUIRED
					body.append('recaptcha_action', 'guest_login'); // optional
				}

				try {
					let response = await fetch('/ajax', { method: 'POST', body });
					let result = await response.json();
					if (result.message) {
						bootbox.alert(result.message);
					}
					if (result.error) {
					} else {
						setState('main', 'chat', false);
						setState('chat', 'rooms', true);
					}
				} catch (e) {
					bootbox.alert("Server error. Please try again.");
				}
			} else {
				form_guest_login.reportValidity();
			}

			resetButton();

			function resetButton() {
				$btn.prop('disabled', false).html(originalText);
			}
		});

		$('#forgotten_btn').click(async () => {
			const btn = $('#forgotten_btn');

			if (form_forgotten_email.checkValidity()) {
				btn.prop('disabled', true).text('Please wait...');

				let body = new FormData(form_forgotten_email);
				try {
					let response = await fetch('/ajax', { method: 'POST', body });
					let result = await response.json();
					bootbox.alert(result.message);
				} catch (err) {
					console.error(err);
					bootbox.alert('An error occurred. Please try again.');
				} finally {
					btn.prop('disabled', false).text('Send'); // restore text
				}
			} else {
				form_forgotten_email.reportValidity();
			}
		});

		$('#user_login_btn').click(async () => {
			const $btn = $('#user_login_btn');
			if ($btn.prop('disabled')) return;

			const originalText = $btn.text();
			$btn.prop('disabled', true).html(`${originalText} <span class="spinner"></span>`);

			if (form_user_login.checkValidity()) {
				let body = new FormData(form_user_login);
				localStorage.setItem('email', $('#email_login').val());
				localStorage.setItem('password', $('#password_login').val());

				try {
					let response = await fetch('/ajax', { method: 'POST', body });
					let result = await response.json();
					if (!result.error) {
						localStorage.setItem('registered', 1);
						setState('main', 'chat', false);
						setState('chat', 'rooms', true);
					} else {
						bootbox.alert(result.message);
					}
				} catch (err) {
					bootbox.alert("Server error. Please try again.");
				}
			} else {
				form_forgotten_email.reportValidity();
			}

			$btn.prop('disabled', false).html(originalText);
		});

		$(document).ready(() => {
			if (!localStorage.getItem('warningDismissed')) {
				$('#adult-warning').show();
			}
			$('#adult-warning .close').click(function () {
				$('#adult-warning').hide();
				localStorage.setItem('warningDismissed', 'true');
			});
			if (window.location.search.includes('register')) {
				const btn = document.querySelector('#role_user');
				if (btn) {
					btn.click();
				}
			}
		});
	</script>
	<script type="43c79d86114da1905a54a368-text/javascript">
		// --- GPS autofill (📍 inside ZIP input) --------------------------------------
		(function () {
			const $gpsBtn = $('#btn-gps');
			const $status = $('#gps-status');

			function setStatus(msg, isErr = false) {
				if (!msg) { $status.addClass('d-none').text(''); return; }
				$status.toggleClass('d-none', false)
					.toggleClass('text-danger', !!isErr)
					.text(msg);
			}

			if ($gpsBtn.length) {
				$gpsBtn.on('click', function () {
					if (!('geolocation' in navigator)) {
						setStatus(tra['Geolocation not supported'] || 'Geolocation not supported', true);
						return;
					}

					$gpsBtn.prop('disabled', true).css('opacity', 0.6);
					setStatus(tra['Getting your location...'] || 'Getting your location...');

					navigator.geolocation.getCurrentPosition(async (pos) => {
						const { latitude, longitude } = pos.coords;
						setStatus((tra['Resolving address...'] || 'Resolving address...') + ` (${latitude.toFixed(4)}, ${longitude.toFixed(4)})`);

						try {
							const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(latitude)}&lon=${encodeURIComponent(longitude)}&addressdetails=1`;
							const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
							if (!resp.ok) throw new Error('HTTP ' + resp.status);

							const data = await resp.json();
							const addr = data.address || {};
							// Some countries return postcode like '75001'; others may include spaces — keep digits only for your ZIP logic
							let postcode = (addr.postcode || '').toString().trim();
							const numericZip = postcode.replace(/\D+/g, ''); // keep digits for your length/number check
							const countryCode = (addr.country_code || '').toLowerCase();

							// Only set the country if it's in your allowed list
							const allowed = ['fr', 'be', 'ch', 'lu'];
							if (allowed.includes(countryCode)) {
								$country.val(countryCode).trigger('change'); // your code clears ZIP on country change
							}

							if (numericZip) {
								// Ensure the ZIP input is numeric so your handler can run,
								// then set the digits ZIP and trigger the existing handler.
								$zip.attr('type', 'number');
								$zip.val(numericZip).trigger('input'); // your input handler calls /ajax?a=getCity
								setStatus((tra['localisation:'] || 'localisation:') + ` ${numericZip} ${countryCode ? countryCode.toUpperCase() : ''}`);
								// Optional visual feedback on the 📍 button
								$gpsBtn.css('opacity', 1).animate({opacity: 1}, 150);
							} else {
								setStatus(tra['Code postal non trouvé'] || 'Code postal non trouvé', true);
							}
						} catch (e) {
							console.error(e);
							setStatus(tra['Reverse geocoding failed'] || 'Reverse geocoding failed', true);
						} finally {
							$gpsBtn.prop('disabled', false).css('opacity', 1);
						}
					}, (err) => {
						setStatus(err && err.message ? err.message : (tra['Location error'] || 'Location error'), true);
						$gpsBtn.prop('disabled', false).css('opacity', 1);
					}, {
						enableHighAccuracy: true,
						timeout: 10000,
						maximumAge: 30000
					});
				});
			}
		})();

	</script>
</div>
		<style>
	html {
		overflow: hidden;
	}
	html, body {
		height: 100%;
		margin: 0;
		letter-spacing: 0;
	}
	b, strong {
		font-weight: bold;
	}
	[data-state="chat"] {
		height: 100%;
	}
	.container-fluid,
	.row {
		height: 100%;
	}
	.main-content {
		background-color: var(--background-light);
		flex-direction: column;
		height: 100dvh;
		overflow: auto;
	}
	.sidebar {
		background-color: var(--background-sidebar);
		color: white;
		height: 100%;
		display: flex;
		flex-direction: column;
		justify-content: space-between;
	}
	.ad-space {
		background-color: var(--background-ad);
		border: 2px solid var(--border-ad);
		text-align: center;
		padding: 20px;
		color: var(--text-ad);
	}
	.featured-rooms {
		background-color: var(--background-light);
	}
	.room-card {
		background-color: var(--color-primaire-1);
		border-radius: 10px;
		height: 150px;
		width: 150px;
		padding: 0;
	}
	.room-card img {
		width: 100%;
		height: 100%;
		border-radius: 10px;
		cursor: pointer;
	}
	.search-box {
		margin-top: 1em;
		border-radius: 8px;
		background-color: var(--search-bg);
		padding: 5px 15px;
		display: flex;
		align-items: center;
		width: 100%;
		border: 1px solid var(--search-border);
		opacity: 0.5;
	}
	.search-box i {
		color: var(--color-primaire-1);
	}
	.bootbox-accept {
		background-color: var(--color-primaire-1);
	}
	.search-box input {
		border: none;
		background-color: transparent;
		box-shadow: none;
		flex: 1;
		padding: 0;
	}
	.search-box input:focus {
		background-color: transparent;
		outline: none;
		box-shadow: none;
	}
	.search-box input::placeholder {
		color: var(--search-placeholder);
	}
	.search-box button {
		background-color: transparent;
		border: none;
		color: var(--search-button-color);
	}
	#usersList, #usersPrivateList {
		height: 100%;
		will-change: transform;
		contain: layout paint;
		transform: translateZ(0);
	}
	#userListContainer .userDetails {
		align-items: center;
		display: flex;
		gap: 0.4em;
		margin: 0.5em 0.2em;
		font-size: 0.85em;
		cursor: pointer;
		position: relative;
	}
	#userListContainer .usersList {
		position: relative;
		padding: 0.1em;
	}
	#userListContainer .usersList.private_male::before {
		content: url("data:image/svg+xml,...");
		position: absolute;
		right: 0.5em;
		top: 0.2em;
		width: 16px;
		height: 16px;
		opacity: var(--private-icon-opacity);
	}
	#userListContainer .usersList.private_male.blinking::before {
		animation: blink 1s ease-in-out 3;
		animation-fill-mode: forwards;
	}
	#userListContainer .usersList:hover {
		background-color: var(--highlight-hover);
	}
	#userTabs button {
		font-size: 0.9em;
		padding: 0;
	}
	#userTabsContent button.nav-link {
		font-size: 0.8em;
	}
	#privateTabs {
		display: none;
	}
	#privateTabs .nav-link{
		color:white;
	}

	#userTabs button.nav-link,
	#usersPrivateList button.nav-link,
	#userTabs button.nav-link.active,
	#usersPrivateList button.nav-link.active,
	#userTabsContent button.nav-link.active
	{
		color: white;
		border: none;
		box-sizing: border-box;
	}
	#userTabs button.nav-link.active,
	#usersPrivateList button.nav-link.active,
	#userTabsContent button.nav-link.active
	{
		font-weight: bold;
		border-bottom: 5px solid var(--tab-active-color)!important;
		color: var(--tab-active-color);
	}
	#userTabs button.nav-link:hover,
	#userTabs button.nav-link:focus {
		border: none;
	}
	#userTabs button.active {
		border-bottom: 1px solid var(--color-primaire-1)!important;
	}
	#roomList .roomItem,
	#userRoomList .roomItem {
		display: flex;
		padding: 0.5em;
		gap: 0.3em;
		cursor: pointer;
	}
	#roomListAdult .roomItem.adult {
		font-style: italic;
	}
	#roomList .roomItem:hover,
	#userRoomList .roomItem:hover {
		background: var(--room-item-hover-bg) !important;
		color: var(--room-item-hover-text);
	}
	#roomList .roomItem:nth-child(odd),
	#userRoomList .roomItem:nth-child(odd) {
		background-color: transparent;
	}
	#roomList .roomItem:nth-child(even),
	#userRoomList .roomItem:nth-child(even) {
		background-color: var(--room-item-even-bg);
	}
	#roomList,
	#userRoomList {
		overflow-y: auto;
	}
	#roomListStarred {
		flex-shrink: 0;
		width: 100%;
		overflow: auto;
		flex-wrap: nowrap;
		height: 164px;
	}




	.gender_icon {
		width: 10px;
		height: 10px;
		border-radius: 10px;
		position: absolute;
		border: 2px solid white;
		top: 0;
		left: 40px;
	}
	.gender_icon.male {
		background: var(--gender-male);
	}
	.gender_icon.female {
		background: var(--gender-female);
	}

	#userListContainer span.male,
	#chat-header span.male,
	#profileUserContainer span.male,
	#typing span.male,
	#public_messages .in_my_region .male {
		color: var(--gender-male);
		font-weight: 400;
	}

	#userListContainer span.female,
	#chat-header span.female,
	#profileUserContainer span.female,
	#typing span.female,
	#public_messages .in_my_region .female {
		color: var(--gender-female);
		font-weight: 400;
	}

	#userListContainer span.couple,
	#chat-header span.couple,
	#profileUserContainer span.couple,
	#typing span.couple,
	#public_messages .in_my_region .couple {
		color: var(--gender-female);
		font-weight: 400;
	}

	#userListContainer span.trans,
	#chat-header span.trans,
	#profileUserContainer span.trans,
	#typing span.trans,
	#public_messages .in_my_region .trans {
		color: var(--gender-trans);
		font-weight: 400;
	}

	#menu_actions a {
		width: 33%;
		margin: 0 !important;
	}

	#menu_actions .item {
		padding: 10px;
		border-radius: 5px;
		height: 65px;
		justify-content: center;
	}

	#menu_actions .item.selected svg path {
		stroke: var(--selection-color);
	}

	.close-user-btn {
		margin-left: auto;
		margin-right: 0.25em;
	}
	.close-user-btn:hover {
		color: var(--close-hover);
	}

	#sidebar {
		padding: 0;
	}

	#userTabsContent {
		font-size: 1.10em;
		overflow-y: hidden !important;
	}

	h1, h2, h3, h4, h5 {
		color: var(--heading-color);
	}

	.avatarContainer {
		border-radius: 1rem;
		width: var(--avatar-size);
		height: var(--avatar-size);
		display: flex;
		align-items: center;
		justify-content: center;
		font-weight: 600;
		font-size: 14px;
		min-width: var(--avatar-size);
		position: relative;
	}

	.avatarContainer.male {
		background: var(--gender-male-avatar);
	}
	.avatarContainer.female {
		background: lightpink;
	}
	.avatarContainer.couple {
		background: linear-gradient(to bottom, var(--gender-couple-border-top), var(--gender-couple-border-bottom));
	}
	.avatarContainer.trans {
		background: var(--gender-trans);
	}

	#userListContainer img.avatar,
	.message img.avatar {
		min-width: 2.5rem;
		width: var(--avatar-size);
		height: var(--avatar-size);
		border-radius: var(--avatar-border-radius);
		padding: var(--avatar-padding);
	}

	img.avatar.male {
		border: 5px solid var(--gender-male-avatar);
	}
	img.avatar.female {
		border: 5px solid lightpink;
	}
	img.avatar.trans {
		border: 5px solid var(--gender-trans);
	}
	img.avatar.couple {
		border: 5px solid;
		border-color: var(--gender-couple-border-top) var(--gender-couple-border-bottom)
		var(--gender-couple-border-top) var(--gender-couple-border-bottom);
	}

	#profile_user_btn,
	#report_user_btn,
	#ban_user_btn {
		cursor: pointer;
	}

	#chat-header svg {
		width: 40px;
		height: 40px;
		margin: 0.5em;
	}

	.chat-header img.avatar48 {
		min-width: 2.5rem;
		width: var(--avatar-size);
		height: var(--avatar-size);
		border-radius: var(--avatar-border-radius);
		padding: var(--avatar-padding);
	}

	.avatar32 {
		width: 32px;
		height: 32px;
	}


	#private-send-btn, #public-send-btn {
		background: var(--btn-bg);
	}

	#chat {
		margin: 0;
		padding: 0;
	}

	#clearInput {
		background: transparent;
		padding: 0 9px;
	}

	#clearInput:focus {
		background-color: transparent;
		outline: none;
		box-shadow: none;
		color: white;
	}

	#numberUsers, #numberUsersRoom, #numberFriends {
		background-color: var(--btn-bg);
		padding: 0 8px;
		border-radius: 3px;
		font-weight: bold;
		font-size: 0.8em;
	}

	#numberUsers:empty, #numberUsersRoom:empty {
		display: none;
	}

	span.mention {
		font-weight: bold;
		padding: 3px;
		border: none;
		border-radius: 5px;
		background: var(--mention-bg);
		color: var(--mention-color);
		margin: 0.5em;
		cursor: pointer;
	}

	#userTabsContent div.blocked {
		opacity: 0.3;
	}

	.role {
		border-radius: 50%;
		border: 2px solid white;
		margin-left: 0.2em;
		padding: 1px;
		font-size: 0.9em;
	}
	.role.user {
		background-color: var(--role-user-bg);
	}
	.role.admin {
		background-color: var(--role-admin-bg);
	}
	.role.moderator,
	.role.mdoderator {
		background-color: var(--role-moderator-bg);
	}
	.role.guest {
		display: none;
	}

	.adminTools button {
		border: none;
		background-color: var(--btn-bg);
		border-radius: 5px;
	}

	.adminTools button:hover {
		background-color: var(--btn-hover-bg);
		color: var(--btn-hover-color);
	}

	.deleteMessage {
		cursor: pointer;
	}
	.deleteMessage:hover {
		color: var(--btn-hover-bg);
	}

	.number_unread_private_messages,
	.number_unread_public_messages {
		background: var(--notification-bg);
		padding: 6px;
		font-weight: bold;
		border-radius: 50%;
		color: var(--notification-text);
		font-size: 0.8em;
	}

	#number_unread_private_messages, #number_unread_public_messages{
		position: absolute;
		left: 50%;
		top: 5px;
	}

	.number_unread_public_messages:empty,
	.number_unread_private_messages:empty,
	#number_friends:empty{
		display: none;
	}

	.number_unread_private_messages {
		width: 24px;
		height: 24px;
		justify-content: center;
	}

	#number_unread_private_messages_top,
	#number_friends{
		display: inline-block;
		background: var(--notification-bg);
		padding: 6px;
		font-weight: bold;
		border-radius: 50%;
		color: var(--notification-text);
		font-size: 0.8em;
		width: 24px;
		height: 24px;
	}

	#private_menu_btn.disabled {
		pointer-events: none;
		opacity: 0.2;
	}

	div.bell {
		position: absolute;
		font-size: 0.8em;
		color: var(--btn-hover-bg);
		z-index: 1;
		animation: ring 1.5s ease-in-out infinite;
		transform-origin: top center;
		display: inline-block;
	}

	@keyframes ring {
		0% { transform: rotate(0); }
		10% { transform: rotate(15deg); }
		20% { transform: rotate(-15deg); }
		30% { transform: rotate(10deg); }
		40% { transform: rotate(-10deg); }
		50% { transform: rotate(5deg); }
		60% { transform: rotate(-5deg); }
		100% { transform: rotate(0); }
	}

	.hidden-user {
		display: none !important;
	}

	.navbar-toggler {
		position: absolute;
		top: 10px;
		right: 10px;
		z-index: 1050;
	}

	#user-popup {
		position: absolute;
		background-color: var(--popup-bg);
		border: 1px solid var(--popup-border);
		max-height: 250px;
		overflow-y: auto;
		width: 300px;
		display: none;
		border-radius: 5px;
		-webkit-overflow-scrolling: touch;
	}

	.user-popup-item {
		padding: 10px;
		cursor: pointer;
		display: flex;
		align-items: center;
		gap: 1em;
	}
	.user-popup-item:hover {
		background-color: var(--popup-hover-bg);
	}
	.user-popup-item img {
		border-radius: 50%;
		margin-right: 10px;
	}

	#closeUserList {
		display: flex;
		flex-direction: row-reverse;
		margin: 2px;
	}

	#closePopupBtn {
		cursor: pointer;
		margin: 5px;
	}

	#closeusersList {
		cursor: pointer;
		display: flex;
		flex-direction: row-reverse;
		position: absolute;
		right: 0;
	}

	#privateActionContainer {
		margin-left: auto;
		cursor: pointer;
		position: relative;
	}

	#rooms_starred_container {
		display: none;
	}

	#privateHeaderDescription {
		max-width: calc(100% - 80px);
		overflow: hidden;
	}

	#privateHeaderDescription > small {
		font-size: 0.75em;
	}

	.nav-item {
		margin-right: 0;
	}

	.close-video-btn {
		background: transparent;
		border: none;
		color: var(--close-btn-color);
		font-size: 1.5em;
		cursor: pointer;
		padding: 0;
		line-height: 1;
	}

	.close-video-btn:hover {
		color: var(--close-btn-hover);
	}

	img.gif {
		width: var(--gif-size);
		border-radius: 3px;
		cursor: pointer;
	}

	img.gif.big {
		width: var(--gif-size-big);
	}

	#filterContainer {
		user-select: none;
	}

	/* Hide the default checkbox */
	.custom-checkbox input[type="checkbox"] {
		position: absolute;
		opacity: 0;
		cursor: pointer;
	}

	/* Custom checkbox container */
	.custom-checkbox {
		position: relative;
		padding-left: 20px;
		cursor: pointer;
		user-select: none;
		margin-right: 20px;
		margin-bottom: 10px;
	}

	/* Custom visual checkbox */
	.custom-checkbox .checkmark {
		position: absolute;
		top: 0;
		left: -4px;
		height: 20px;
		width: 20px;
		border-radius: 5px;
		border: 1px solid var(--checkbox-border);
	}

	/* Checked background */
	.custom-checkbox input:checked ~ .checkmark {
		background-color: var(--color-primaire-1);
	}

	/* Tick inside the checkbox */
	.custom-checkbox .checkmark:after {
		content: "";
		position: absolute;
		display: none;
	}

	/* Show tick when checked */
	.custom-checkbox input:checked ~ .checkmark:after {
		display: block;
	}

	/* Tick style */
	.custom-checkbox .checkmark:after {
		left: 7px;
		top: 3px;
		width: 5px;
		height: 10px;
		border: solid var(--checkbox-tick);
		border-width: 0 2px 2px 0;
		transform: rotate(45deg);
	}

	.recorded {
		color: var(--btn-hover-bg);
		cursor: pointer;
		margin: 0.5em;
		font-size: 1.5em;
	}

	.typing::after {
		content: '...';
		animation: blink 1s infinite;
		position: absolute;
		top: 0;
		right: 0;
	}

	#messagesContainer div.message.typing::after {
		position: relative;
	}

	@keyframes blink {
		0%, 100% { opacity: 0; }
		50% { opacity: 1; }
	}
	.live-dot {
		position: absolute;
		top: 2px;
		right: 2px;
		width: 8px;
		height: 8px;
		background-color: red;
		border-radius: 50%;
		animation: blink 1s infinite;
		box-shadow: 0 0 4px red;
	}

	.shadow {
		border-radius: 0.625rem;
		background: var(--shadow-bg);
		box-shadow: 0 4px 10px 0 var(--shadow-color);
		backdrop-filter: blur(15px);
	}

	.small {
		color: var(--small-text);
		font-weight: normal;
	}

	.big {
		color: var(--color-primaire-1);
	}

	#headerInfoUser {
		margin-top: -2em;
		width: fit-content;
		display: flex;
		flex-wrap: wrap;
	}

	.nav-link:focus-visible {
		box-shadow: none;
	}

	.nav-tabs .nav-link {
		border: none;
	}
	#private_warning {
		position: sticky;
		top: 0;
		z-index: 1;
		background: var(--selection-color);
		text-align: center;
		color: var(--my-room-text);
		cursor: pointer;
		margin: 0.5em;
	}

	.quiz-leaderboard {
		background: rgba(0, 0, 0, 0.6);
		color: #fff;
		padding: 12px 16px;
		margin: 10px 0;
		border-radius: 8px;
		font-size: 14px;
		line-height: 1.4;
		box-shadow: 0 0 6px rgba(0, 0, 0, 0.3);
	}

	.quiz-leaderboard.day {
		border-left: 5px solid gold;
	}
	.quiz-leaderboard.week {
		border-left: 5px solid dodgerblue;
	}
	.quiz-leaderboard ul {
		margin: 8px 0 0;
		padding-left: 1.2em;
	}
	.quiz-leaderboard li {
		margin-bottom: 2px;
	}
	.spinner {
		display: inline-block;
		width: 16px;
		height: 16px;
		border: 2px solid rgba(255, 255, 255, 0.3);
		border-top: 2px solid #fff;
		border-radius: 50%;
		animation: spin 0.6s linear infinite;
		margin-left: 8px;
		vertical-align: middle;
	}

	@keyframes spin {
		to {
			transform: rotate(360deg);
		}
	}
	@media (min-width: 992px) {
		#users_menu_btn {
			display: none;
		}
	}

	@media (max-width: 992px) {
		#headerInfoUser {
			margin-top: -1em;
		}
		#chat-header svg {
			width: var(--chat-header-svg-size);
			height: var(--chat-header-svg-size);
		}
		#chat-header > div > div.width100.ms-2.d-flex.justify-content-center.gap-2.flex-column > div.d-flex.align-items-center > div.d-flex.flex-column {
			max-width: calc(100% - 165px);
		}
		#chat-header .d-flex .flex-column h4 {
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
			margin: 0;
		}
		#privateHeaderDescription {
			max-width: calc(100% - 30px);
			margin-left: -47px;
		}
		button.emoji-btn {
			display: none;
		}
		svg.svg_menu {
			width: 28px;
			height: 28px;
		}
		span.mention {
			font-size: 0.7em;
			display: inline-block;
			max-width: 60px;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
		}
		#usersList, #usersPrivateList {
			height: calc(100dvh - 210px);
			overflow: scroll;
		}
		#usersList.mobileFiltered {
			height: calc(100dvh - 500px);
		}
		.main-content,
		.chat-container,
		div[data-state="rooms"] {
			height: calc(100dvh - 70px) !important;
		}
		.search-box {
			margin-top: 0.5em;
		}
		#privateActionContainer {
			top: 1.7em;
		}
		#menuContainer {
			position: fixed;
			bottom: 0;
			background: var(--color-primaire-1);
			width: 100%;
			left: 0;
			margin: 0 !important;
			padding: 0 !important;
			z-index: 1;
		}
	}

	#webcam-container {
		position: fixed;
		top: 50px;
		left: 50px;
		height: 120px;
		z-index: 1000;
		box-shadow: 0 4px 8px var(--shadow-color);
	}

	#webcam-video {
		width: 100%;
		height: 100%;
		border-radius: 8px;
	}

	#videoSelect, #audioSelect {
		width: 100%;
	}

	@media (max-width: 992px) {
		#webcam-container {
			height: 100px;
			top: 20px;
			left: 20px;
		}
	}

	#settings-icon {
		cursor: pointer;
		font-size: 20px;
		z-index: 1001;
		color: white;
		margin: 0 6px;
	}

	#settings-modal {
		display: none;
		position: fixed;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
		background: var(--popup-bg);
		padding: 20px;
		border: 2px solid var(--popup-border);
		z-index: 1002;
		width: 300px;
		height: 250px;
	}

	.ui-slider-handle {
		width: var(--slider-handle-size);
		height: var(--slider-handle-size);
	}

	.ui-slider-horizontal {
		height: var(--slider-track-height) !important;
	}

	.webcam-btn:hover {
		color: red;
	}

	.close-btn {
		background-color: transparent;
		border: none;
		color: red;
		font-size: 20px;
		cursor: pointer;
		z-index: 1;
	}

	.panel-header .close-btn {
		top: 5px;
		right: 5px;
	}

	video {
		width: 100%;
		height: 100%;
		background-color: var(--video-bg);
		object-fit: cover;
		cursor: move;
	}

	.video-panel {
		position: fixed;
		width: 320px;
		height: 240px;
		border-radius: 5px;
		overflow: hidden;
		background-color: var(--video-bg);
		z-index: 1;
	}

	.panel-header {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		background-color: var(--video-header-bg);
		color: white;
		padding: 5px 10px;
		font-size: 14px;
		display: flex;
		justify-content: space-between;
		align-items: center;
		z-index: 1;
		cursor: move;
	}

	.panel-header .username {
		font-weight: bold;
	}

	.panel-header .button-group {
		display: flex;
	}

	.panel-header .mute-btn {
		margin-right: 20px;
	}

	.panel-header button {
		background: none;
		border: none;
		color: white;
		cursor: pointer;
		font-size: 18px;
		padding: 0 5px;
	}

	.panel-header button:hover {
		color: var(--video-hover);
	}

	.panel-header button:active {
		transform: scale(0.9);
	}

	#filterGendersContainer {
		font-size: 0.85em;
	}

	#menu_actions {
		text-align: center;
		line-height: 1;
	}

	.mention {
		border: var(--mention-border-width) var(--mention-border-style);
	}

	#soundCanvas {
		position: absolute;
		bottom: 0;
		left: 0;
		width: 100%;
		height: 50px;
	}

	#menu_actions i.fa-2x {
		font-size: 1.5em;
	}

	span.private_dep {
		font-weight: bold;
		cursor: pointer;
	}

	#usersPrivateList i.fa-circle-xmark.fa-2x {
		font-size: 1.5em;
	}

	#messagesContainer div.offline {
		background: var(--user-offline-color);
		width: 10px;
		height: 10px;
		border-radius: 50%;
		position: absolute;
		top: 35px;
		left: 36px;
		border: 2px solid white;
	}

	#messagesContainer div.online {
		background: var(--user-online-color);
		width: 10px;
		height: 10px;
		border-radius: 50%;
		position: absolute;
		top: 35px;
		left: 36px;
		border: 2px solid white;
	}

	#messagesContainer div.ago {
		font-size: var(--mobile-header-font-size);
		font-style: italic;
	}

	#messagesContainer .message-content {
		font-size: 1.1em;
		word-break: break-word;
	}
	#messagesContainer .messageDate {
		display: flex;
		align-items: center;
	}
	#messagesContainer div.sent .messageDate {
		flex-direction: row-reverse;
	}

	#messagesContainer .message i {
		font-size: 0.8em;
		padding: 0 0.5em;
		font-style: normal;
	}

	#headerWebcam {
		display: flex;
		width: 100%;
		justify-content: space-between;
		position: absolute;
		align-items: center;
		z-index: 1;
	}

	#webcam_label {
		color: var(--label-text-color);
		text-shadow:
				1px 1px 0 var(--text-shadow-dark),
				-1px 1px 0 var(--text-shadow-dark),
				1px -1px 0 var(--text-shadow-dark),
				-1px -1px 0 var(--text-shadow-dark);
		position: absolute;
		font-size: 0.9em;
		width: 100%;
		text-align: center;
		bottom: 0;
		z-index: 1;
	}

	#usersPrivateList, #friendsList {
		position: relative;
		overflow: auto;
		height: calc(100dvh - var(--user-private-list-height-offset));
	}

	.status {
		width: 10px;
		height: 10px;
		position: absolute;
		top: 45px;
		left: 53px;
		border-radius: 50%;
		border: 2px solid var(--status-border);
	}
	.status.online {
		background: var(--status-online-bg);
	}
	.status.offline {
		background: var(--status-offline-bg);
	}

	.toggle-checkbox {
		display: none;
	}

	.toggle-label {
		cursor: pointer;
		position: relative;
		color: var(--label-text-color);
	}

	#sound-icon,
	#webcam-icon {
		display: inline;
	}
	#sound-icon-muted,
	#webcam-icon-muted {
		display: none;
	}
	.toggle-checkbox:checked + .toggle-label #sound-icon {
		display: none;
	}
	.toggle-checkbox:checked + .toggle-label #sound-icon-muted {
		display: inline;
	}
	.toggle-checkbox:checked + .toggle-label #webcam-icon {
		display: none;
	}
	.toggle-checkbox:checked + .toggle-label #webcam-icon-muted {
		display: inline;
	}

	#webcam-video {
		filter: none;
	}
	.toggle-checkbox#toggle-webcam:checked ~ #webcam-video {
		display: none;
	}

	input[type="range"].form-range {
		-webkit-appearance: none;
		width: 100%;
		height: 3px;
		background: linear-gradient(to right, var(--slider-primary) 100%, var(--slider-secondary) 0%);
		border-radius: 1.5px;
		outline: none;
		position: relative;
	}

	/* WebKit */
	input[type="range"].form-range::-webkit-slider-runnable-track {
		background: transparent;
	}
	input[type="range"].form-range::-webkit-slider-thumb {
		-webkit-appearance: none;
		width: 12px;
		height: 12px;
		background: var(--slider-primary);
		border-radius: 50%;
		cursor: pointer;
		margin-top: -4.5px;
	}

	/* Firefox */
	input[type="range"].form-range::-moz-range-track {
		background: transparent;
	}
	input[type="range"].form-range::-moz-range-thumb {
		width: 12px;
		height: 12px;
		background: var(--slider-primary);
		border-radius: 50%;
		cursor: pointer;
	}

	/* Edge/IE */
	input[type="range"].form-range::-ms-track {
		background: transparent;
		border-color: transparent;
		color: transparent;
	}
	input[type="range"].form-range::-ms-fill-lower {
		background: var(--slider-primary);
	}
	input[type="range"].form-range::-ms-fill-upper {
		background: var(--slider-secondary);
	}
	input[type="range"].form-range::-ms-thumb {
		width: 12px;
		height: 12px;
		background: var(--slider-primary);
		border-radius: 50%;
		cursor: pointer;
	}

	/* jQuery UI Sliders */
	#ageRange.ui-slider,
	#distanceRange.ui-slider {
		height: 3px !important;
		background: var(--slider-secondary);
		border-radius: 1.5px;
		position: relative;
		border: none;
	}
	#ageRange .ui-slider-range,
	#distanceRange .ui-slider-range {
		background: var(--slider-primary);
		height: 100%;
		border-radius: 1.5px;
	}
	#ageRange .ui-slider-handle,
	#distanceRange .ui-slider-handle {
		width: 12px;
		height: 12px;
		background: var(--slider-primary);
		border-radius: 50%;
		cursor: pointer;
		border: none;
	}

	#typing {
		font-size: var(--typing-font-size);
		font-style: var(--typing-style);
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		width: 100%;
		display: block;
	}

	#menuPrivateUser {
		position: relative;
		display: flex;
		background: var(--color-primaire-1);
		border-radius: 2em;
	}
	#menuPrivateUser .dropdown-item {
		color: white;
	}
	#menuPrivateUser .dropdown-item:focus,
	#menuPrivateUser .dropdown-item:hover {
		background-color: var(--color-primaire-1);
	}

	#menuPrivateUser::after {
		content: "";
		position: absolute;
		width: 0;
		height: 0;
		border: var(--menu-bubble-arrow-size) solid transparent;
		border-top-color: var(--color-primaire-1);
		bottom: -17px;
		left: 50%;
		transform: translateX(-50%);
	}

	#menuPrivateUser.bubble-left::after {
		left: 10%;
		transform: translateX(0);
	}


	#menuPrivateUser.bubble-right::after {
		left: 90%;
		transform: translateX(-100%);
	}

	#userListContainer div.adminTools {
		position: absolute;
		bottom: -9px;
		right: 0;
	}

	#userListContainer div.band {
		width: 0.5em;
		height: 3em;
	}

	#userListContainer div.usersList.selected div.band {
		background: var(--selection-color);
	}

	#filterUser::placeholder {
		color: var(--filter-placeholder);
	}

	#filterIcon.rotated {
		transform: rotate(180deg);
		transition: transform 0.3s ease;
	}

	#profile_user_btn,
	#ban_user_btn,
	#report_user_btn {
		fill: var(--color-primaire-1);
	}

	#ban_user_btn.red svg {
		fill: red;
	}

	#friend_user_btn {
		cursor: pointer;
	}

	/* #report_user_btn {
		fill: var(--selection-color);
	} */

	#messagesContainer .youtube-icon {
		cursor: var(--youtube-icon-cursor);
	}

	.reactions {
		position: relative;
		cursor: pointer;
		display: none;
	}

	#messagesContainer .message.received:hover .reactions {
		display: inline-block;
	}

	.selected-reaction {
		font-size: 1.2em;
	}

	.reaction-options {
		display: none;
		position: absolute;
		top: 100%;
		left: 0;
		background: var(--reaction-bg);
		border: 1px solid var(--reaction-border);
		padding: 3px;
		border-radius: 5px;
		z-index: 10;
		gap: 2px;
		box-shadow: 0 2px 6px var(--reaction-shadow);
	}

	.reactions.open .reaction-options {
		display: flex;
	}

	.reaction-options.flip-left {
		right: 0;
		left: auto;
	}

	.reaction-options.flip-right {
		left: 0;
	}

	.reaction-options span {
		padding: 5px;
		font-size: 1em;
		display: inline-block;
		cursor: pointer;
	}

	.reaction-options span:hover {
		background-color: var(--reaction-hover-bg);
		border-radius: 50%;
	}

	#public_messages .reactionsReceived {
		position: absolute;
		bottom: -10px;
		font-size: 1em;
		cursor: pointer;
	}

	#userListContainer span.friend {
		color: var(--friend-color);
	}

	.friends_checkmark {
		display: inline-block;
		width: 16px;
		height: 16px;
		position: relative;
	}

	.friends_checkmark::after {
		content: "★";
		font-size: 32px;
		color: var(--friend-star-color);
		position: absolute;
		left: -8px;
		top: -16px;
		transition: color 0.2s ease-in-out;
	}

	input[type="checkbox"]:checked + .friends_checkmark::after {
		color: var(--friend-star-checked);
	}

	#usersList > div {
		will-change: transform;
		contain: layout paint;
	}

	#public_messages .in_my_region_container {
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		padding-bottom: 2.3em;
	}

	#numberWatchers {
		background: var(--watcher-bg);
		padding: 0 0.5em;
		border-radius: 100%;
		margin: 0em 0.5em;
		cursor: pointer;
	}

	#public_messages span.reaction svg {
		width: 32px;
		height: 32px;
	}


	#userListContainer {
		overflow-y: auto;
		position: relative;
		height: calc(100dvh - var(--user-list-height-offset));
	}

	#usersList {
		position: relative; /* Important for absolute children */
	}

	.btn-premium {
		background-color: var(--btn-premium-bg);
		color: var(--btn-premium-color);
		font-weight: bold;
		border-radius: 8px;
		padding: 6px;
		display: inline-flex;
		align-items: center;
	}

	#roomTabs {
		display: flex;
		flex-wrap: nowrap;
	}

	.room_icon {
		width: var(--room-icon-size);
		height: var(--room-icon-size);
	}

	#userRoomListContent .myRoom {
		background: var(--my-room-bg) !important;
		color: var(--my-room-text);
		font-weight: bold;
	}
	#quizOverlay {
		display: none; /* Hidden by default */
		position: fixed;
		top: 0;
		left: 0;
		width: 100vw;
		height: 100vh;
		background: rgba(0, 0, 0, 0.6);
		align-items: center;
		justify-content: center;
		z-index: 9999;
	}

	#quizOverlay.active {
		display: flex; /* Show when quiz is active */
	}


	#quizBox {
		background: #fff;
		padding: 30px 40px;
		border-radius: 12px;
		max-width: 500px;
		width: 90%;
		box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
		text-align: center;
	}
	.quiz-notify-container {
		position: fixed;
		top: 10%;
		left: 50%;
		transform: translateX(-50%);
		z-index: 9999;
		display: flex;
		flex-direction: column;
		align-items: center;
		pointer-events: none;
	}

	.quiz-toast {
		background: #1e1e2f;
		color: #fff;
		padding: 15px 20px;
		margin: 8px 0;
		border-radius: 10px;
		box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
		font-size: 18px;
		animation: fadeSlide 4s ease-in-out forwards;
	}

	@keyframes fadeSlide {
		0% {
			opacity: 0;
			transform: translateY(-20px);
		}
		10%, 90% {
			opacity: 1;
			transform: translateY(0);
		}
		100% {
			opacity: 0;
			transform: translateY(20px);
		}
	}
	.bootbox.modal .modal-body .bootbox-body img.photo  {
		cursor: pointer;
	}

</style>
<style>

	#gameContainer {
		background: #fff;
		border-radius: 5px;
		padding: 10px;
		margin: auto;
		position: relative;
		user-select: none;
	}

	#questionNumberContainer {
		color:#f12506;
		margin-left: 25px;
		font-size: 1.18em;
		text-align:center;
	}


	#questionContainer {
		background-color: #e2e2e2;
	}
	#question {
		font-size:1.10em;
		font-weight:bold;
		text-align:center;
		padding: 1em;
	}


	#question img {
		border-radius:5px;
		max-width: 100%;
	}
	button.proposition {
		border:none;
		border-radius:5px;
		color:#000;
		margin:5px 5px;
		transition-duration:0.3s;
		vertical-align: middle;
		box-shadow: 1px 1px 3px #444;
	}
	button.classement {
		border:none;
		border-radius:5px;
		color:#000;
		margin:5px 5px;
		transition-duration:0.3s;
		vertical-align: middle;
	}
	button.classement.dashed {
		border: dashed 2px #ccc;
		background-color:white;
	}

	button.selectedEnabled {
		background-color: #F32605;
	}
	.imageQuestion {
		margin:10px;
	}

	.gridRebus {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		justify-content: center;
		margin: auto;
	}

	.gridRebus img {
		width: 70px;
		padding:5px;
	}

	.width1 {
		width:calc(100% - 10px);
		font-size: 1.2em;
	}
	.width2 {
		width:calc(50% - 10px);
		font-size: 1.2em;
	}
	.width3 {
		width:calc(100% - 10px);
		font-size: 1.2em;
	}
	.width4 {
		width:calc(50% - 10px);
		font-size: 1.2em;
	}
	.width5 {
		width:calc(100% - 10px);
		font-size: 1.2em;
	}
	.width6 {
		width:calc(50% - 10px);
		font-size: 1.5em;
	}
	.width7 {
		width:calc(50% - 10px);
		font-size: 1.5em;
	}
	.width8 {
		width:calc(50% - 10px);
	}
	.widthPorcentage1 {
		width:calc(100% - 10px);
		font-size: 1.2em;
		line-height: inherit;
	}
	.widthPorcentage2 {
		width:calc(50% - 10px);
		font-size: 1.2em;
		line-height: inherit;
	}
	.widthPorcentage3 {
		width:calc(33% - 10px);
		font-size: 1.2em;
		line-height: inherit;
	}
	.widthPorcentage4 {
		width:calc(25% - 10px);
		font-size: 1.2em;
		line-height: inherit;
	}
	.widthPorcentage5 {
		width:calc(20% - 10px);
		font-size: 1.2em;
		line-height: inherit;
	}
	.widthPorcentage6 {
		width:calc(16% - 10px);
		font-size: 1.5em;
		line-height: inherit;
	}
	.widthPorcentage8 {
		width:calc(12.5% - 10px);
		line-height: inherit;
	}
	.boutons.image {
		width: 80%;
		line-height: inherit;
	}

	#validerContainer {
		display:none;
	}
	#validerBtn {
		margin-top:10px;
		font-size:1.6em;
		padding:2px 30px;
		font-weight:bold;
		border-radius:3px;
		border:none;
		color:#FFF;
		transition-duration:0.3s;
		background-color: #f0ad4e;
		width: 100%;
	}

	#lettresGameContainer {
		text-align:center;
		margin-top: 40px;

	}

	#lettresContainer {
		margin: 5px 10px;
		display: flex;
		flex-wrap: nowrap;
	}
	.lettre {
		display: inline-block;
		vertical-align: middle;
		text-align: center;
		text-transform: uppercase;
		width: 80px;
		height: 73px;
		line-height: 73px;
		font-size: 45px;
		font-weight: bold;
		margin: 2px;
		box-shadow: 3px 3px 5px #888;
		cursor: pointer;
		transition: 0.3s;
	}

	.lettre.disabled {
		pointer-events:none;
		opacity:0.5;
		background-color:#000
	}
	.lettreSolution {
		display: inline-block;
		vertical-align: middle;
		text-align: center;
		text-transform: uppercase;
		width: 34px;
		height: 34px;
		line-height: 34px;
		font-size: 20px;
		font-weight: bold;
		margin: 2px;
		box-shadow: 3px 3px 5px #888;
		pointer-events: none;
		background-color: #CCC;
	}

	.lettreSolution.full {
		cursor:pointer;
		background-color: #f12506;
		pointer-events:auto;
	}

	.imageQuestion img {
		border-radius:10px;
	}
	#splash img {
		width: 100%;
		height: auto;
	}

	#pointRouge {
		width:32px;
		height:32px;
		background-color: #FF0000;
		border-radius:60px;
		border:2px solid #000;
		position:absolute;
	}
	#carteContainer {
		width:320px;
		height:320px;
		display:inline-block;
		position:relative;
	}
	#carte {
		cursor:pointer;
	}

	.goodAnswer {
		background-color: #DCEFD4;
		padding: 5px;
	}

	#propositions button.proposition img, #propositions button.classement img {
		display: flex;
		margin: auto;
	}

	#closeQuizBtn {
		position: absolute;
		top: 10px;
		right: 10px;
		background: #dc3545;
		color: white;
		border: none;
		border-radius: 50%;
		width: 32px;
		height: 32px;
		font-size: 20px;
		cursor: pointer;
		z-index: 1000;
	}


	@media (min-width:1025px) {
		button.proposition:hover {
			background-color: #F32605;
			transition-duration:0.3s;
			color:#FFF;
		}
		#validerBtn:hover {
			transition-duration:0.3s;
			background-color: #861100;
			color:#000;
		}
		.lettre:hover {
			transform:scale(1.15, 1.15);
			transition-duration:0.3s;
			opacity:0.8;
		}

	}
	/*ipad*/

	@media only screen and (max-width: 768px) {
		.gridRebus img {
			width: 55px;
		}

		#chatContainer {
			width: 100%;
		}
		#gameContainer {
			width:100%;
			padding:1em;
		}

		#lettresContainer {
			margin: 3px 10px;
			display: block;
			justify-content: center;
		}

		.lettre {
			width: 64px;
			height: 48px;
			line-height: 48px;
			font-size: 35px;
			margin: 2px;
		}

		.welcome {
			font-size:0.8em;
		}
		.goodAnswer {
			font-size:0.7em;
		}

		#lettresGameContainer {
			margin-top: 10px;
		}
		.userItem img.avatar, #chat div.message img.avatar {
			width:16px;
			height:16px;
		}
		.classementBadge {
			height: 16px;
		}
		#illustrationCategorieContainer {
			height:0;
		}

		#lettresContainer {
			margin:0;
		}

		#questionContainer {
			position:relative;
		}
		#progressBarContainer {
			right: -2px;
		}
		#illustrationCategorie {
			width:0;
		}
		.userItem {
			height: 18px;
			line-height: 18px;
			padding-left: 5px;
			font-size: 0.7em;

		}
		.boutons.image {
			width: 100%;
			line-height: inherit;
		}

		div.boutons.image button {
			height:80px;
		}
		#progressBarContainer {
			width:100%;
			position: initial;
			margin-top:5px;
		}
		.width4 {
			height:120px;
		}
		#question img {
			width:120px;
		}
		#imgCadeaux {
			max-height:60px;
		}
	}
	#user-tab {
		display: none;
	}
</style>

<div id="quizNotifications" class="quiz-notify-container"></div>
<div id="quizOverlay" class="overlay">
	<button id="closeQuizBtn" class="close-btn" aria-label="Fermer">×</button>
	<div id="gameContainer">
		<div id="questionContainer">
			<div id="question"></div>
			<div id="rebusContainer"></div>
		</div>
		<div id="propositions"></div>
		<div id="lettresGameContainer">
			<div id="lettresSolutionContainer"></div>
			<div id="lettresContainer"></div>
		</div>
		<div id="validerContainer">
			<button id="validerBtn">Valider</button>
		</div>
	</div>
</div>

<div data-state="chat">
	<div class="container-fluid">
		<div class="row">
			<!-- main -->
			<div id="chat" class="col-lg-9">
				<div class="main-content p-3" data-state="rooms">
					<!--<button class="btn btn-primary w-100 mb-3">+ Créer une room</button>-->
					<!-- Tabs Navigation -->
					<ul class="nav nav-tabs" id="roomTabs" role="tablist">
						<li class="nav-item" role="presentation">
							<button class="nav-link active" id="public-tab" data-bs-toggle="tab" data-bs-target="#publicRooms" type="button" role="tab">
								Liste des rooms							</button>
						</li>
						<li class="nav-item" role="presentation">
							<button class="nav-link" id="user-tab" data-bs-toggle="tab" data-bs-target="#userRooms" type="button" role="tab">
								Rooms des membres							</button>
						</li>
						<li style="margin-left:auto">
							<div id="premiumContainerRoom"></div>
						</li>
					</ul>

					<!-- Tabs Content -->
					<div class="tab-content flex-grow-1 overflow-y-auto" id="roomTabsContent">
						<!-- Public Rooms -->
						<div class="tab-pane fade show active" id="publicRooms" role="tabpanel">
							<div id="roomList">
								<div id="roomListNormal"></div>
								<h5 class="mt-2 mb-2">Rooms adultes</h5>
								<div id="roomListAdult"></div>
							</div>
						</div>

						<!-- User Rooms -->
						<div class="tab-pane fade" id="userRooms" role="tabpanel">
							<div id="userRoomList">
								<div id="userRoomListContent"></div>
							</div>
						</div>
					</div>

					<div id="rooms_starred_container" class="mt-4">
						<h5>Rooms vedette</h5>
						<div id="roomListStarred" class="row gap-3">
						</div>
					</div>
				</div>
				<div class="main-content p-3" data-state="profile">
					<style>
	.profile-section {
		text-align: center;
	}
	#avatarPreview {
		border-radius: 50%;
		width: 64px;
		height: 64px;
	}
	.profile-info {
		margin-top: 20px;
	}
	.profile-info h1 {
		font-size: 24px;
		margin-bottom: 5px;
	}
	.profile-info p {
		color: #6c757d;
	}

	.btn-group {
		margin-top: 20px;
	}

	.footer-links {
		margin-top: 50px;
		text-align: center;
		font-size: 0.8em;
	}

	.footer-links a {
		color: #6c757d;
		text-decoration: none;
		margin: 0 15px;
	}

	.image-wrapper {
		position: relative;
		display: inline-block;
		cursor: pointer;
	}

	.camera-icon {
		position: absolute;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
		font-size: 24px;
		color: white;
		opacity: 0;
		transition: opacity 0.3s ease;
		pointer-events: none; /* Prevent the icon from blocking hover on the image */
	}

	.image-wrapper:hover .camera-icon {
		opacity: 1;
		color: #000;
	}

	.image-wrapper:hover img {
		opacity: 0.2;
	}

	.img-thumbnail {
		width: 100px; /* Adjust size as needed */
		height: 100px; /* Adjust size as needed */
		object-fit: cover;
	}


	.color-option {
		display: inline-block;
		width: 30px;
		height: 30px;
		border-radius: 50%;
		margin-right: 2px;
		cursor: pointer;
		border: 2px solid transparent;
	}
	.color-option:hover {
		border-color: #000000;
	}
	#gallery, #gallery_private {
		display: flex;
		flex-wrap: wrap;
		justify-content: center;
		margin: 1em;
		gap:1em;
	}
	#gallery_private {
		flex-direction: column;
		align-items: center;
		margin: 0.5em;
	}

	#gallery div.gallery_item,
	#gallery_private div.gallery_item  {
		width: 80px;
		height: 80px;
		font-size: 2em;
		cursor: pointer;
		position: relative;
	}
	#gallery .img-gallery,
	#gallery_private .img-gallery
	{
		position: relative;
		width: 100%;
		height: 100%;
		object-fit: cover;
		border-radius: 10px;
	}
	#gallery .delete-image {
		position: absolute;
		cursor: pointer;
		border:none;
		top: 0;
		left: 0;
	}
	#add_gallery {
		display: flex;
		justify-content: center;
		align-items: center;
		border: 2px solid #CCC;
		border-radius: 10px;
	}
	#gallery .add-item:hover .fa-plus {
		transition: transform 0.3s ease;
		transform: rotate(90deg);
	}
	#upload-progress {
		width: 100%;
		height: 10px;
		margin-top: 10px;
		background-color: #f3f3f3;
		border-radius: 5px;
		overflow: hidden;
	}

	.progress-bar {
		width: 100%;
		height: 100%;
	}

	.progress-fill {
		width: 0;
		height: 100%;
		background-color: #4caf50;
		transition: width 0.2s;
	}
	#my_description {
		width: 100%;
		height: 4em;
		padding: 0.4em;
		margin-top: 0.5em;
		border-radius: 0.5em;
	}
	#size_chars {
		padding: 0.3em;
		border-radius: 0.3em;
	}

</style>

<div id="profileContainer" class="container profile-section">
	<div class="image-wrapper">
		<img id="avatarPreview" data-bind="avatar" title="Votre avatar" class="img-thumbnail" src="" alt="">
		<input type="file" class="form-control" id="avatar_file" name="avatar" accept="image/*;capture=camera" style="display: none">
		<div class="camera-icon">
			<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M26 26H6C5.46957 26 4.96086 25.7893 4.58579 25.4142C4.21071 25.0391 4 24.5304 4 24V10C4 9.46957 4.21071 8.96086 4.58579 8.58579C4.96086 8.21071 5.46957 8 6 8H10L12 5H20L22 8H26C26.5304 8 27.0391 8.21071 27.4142 8.58579C27.7893 8.96086 28 9.46957 28 10V24C28 24.5304 27.7893 25.0391 27.4142 25.4142C27.0391 25.7893 26.5304 26 26 26Z" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M16 21C18.4853 21 20.5 18.9853 20.5 16.5C20.5 14.0147 18.4853 12 16 12C13.5147 12 11.5 14.0147 11.5 16.5C11.5 18.9853 13.5147 21 16 21Z" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</div>
	</div>
	<div id="premiumContainer"></div>


	<div class="profile-info">
		<h1 data-bind="username" class="d-inline"></h1>
		<button id="modifyMyData_btn0" class="border-0 bg-transparent">
			<svg width="16" height="16" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M16 20H12V16L24 4L28 8L16 20Z" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M20.5 7.5L24.5 11.5" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M27 16.075V26C27 26.2652 26.8946 26.5196 26.7071 26.7071C26.5196 26.8946 26.2652 27 26 27H6C5.73478 27 5.48043 26.8946 5.29289 26.7071C5.10536 26.5196 5 26.2652 5 26V6C5 5.73478 5.10536 5.48043 5.29289 5.29289C5.48043 5.10536 5.73478 5 6 5H15.925" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</button>
		<p>
			<span data-bind="gender"></span>,
			<span data-bind="age"></span>
			<span data-bind="city" class="font-bold"></span>
			<span data-bind="zip"></span>
			<button id="modifyMyData_btn" class="border-0 bg-transparent">
				<svg width="16" height="16" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M16 20H12V16L24 4L28 8L16 20Z" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M20.5 7.5L24.5 11.5" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M27 16.075V26C27 26.2652 26.8946 26.5196 26.7071 26.7071C26.5196 26.8946 26.2652 27 26 27H6C5.73478 27 5.48043 26.8946 5.29289 26.7071C5.10536 26.5196 5 26.2652 5 26V6C5 5.73478 5.10536 5.48043 5.29289 5.29289C5.48043 5.10536 5.73478 5 6 5H15.925" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
		</p>
		<div id="verify_email"></div>
	</div>

	<div>
		<label>Couleur bulle chat</label>
	</div>
	<div class="color-picker d-flex justify-content-center align-items-center">
					<div title="Light Pink" class="color-option" style="background-color: #FFB6C1;" data-color="#FFB6C1"></div>
					<div title="Peach Puff" class="color-option" style="background-color: #FFDAB9;" data-color="#FFDAB9"></div>
					<div title="Lavender" class="color-option" style="background-color: #E6E6FA;" data-color="#E6E6FA"></div>
					<div title="Lemon Chiffon" class="color-option" style="background-color: #FFFACD;" data-color="#FFFACD"></div>
					<div title="Wheat" class="color-option" style="background-color: #F5DEB3;" data-color="#F5DEB3"></div>
					<div title="Light Green" class="color-option" style="background-color: #D1E7DD;" data-color="#D1E7DD"></div>
					<div title="Light Peach" class="color-option" style="background-color: #FFDFBA;" data-color="#FFDFBA"></div>
					<div title="Light Mint" class="color-option" style="background-color: #BAFFBF;" data-color="#BAFFBF"></div>
					<div title="Light Blue" class="color-option" style="background-color: #BAE1FF;" data-color="#BAE1FF"></div>
					<div title="Light Purple" class="color-option" style="background-color: #D6BAFF;" data-color="#D6BAFF"></div>
			</div>
	<div>
		<textarea name="description" id="my_description" placeholder="A propos de vous..." data-bind="description"></textarea>
	</div>
	<div id="gallery"></div>
	<div class="mt-2">
		<label for="size_chars">Taille caractères</label>
		<select  id="size_chars">
			<option value="0.8">80%</option>
			<option value="0.9">90%</option>
			<option value="1" selected>100%</option>
			<option value="1.1">110%</option>
			<option value="1.2">120%</option>
			<option value="1.3">130%</option>
		</select>
	</div>

	<div class="d-flex justify-content-center align-items-center gap-3">
		<label class="switch">
			<input type="checkbox" id="private_male_checkbox">
			<span class="slider round"></span>
		</label>
		<span id="statusLabel">Bloquer messages privés homme</span>
	</div>

	<div class="container">
		<div class="row justify-content-center">
			<div class="col-md-6">
				<a href="/logout" class="btn btn-warning form-control m-1 text-center">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="24" height="24" style="fill:white">
						<path d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z"/></svg>
					Se déconnecter				</a>
			</div>
			<div class="col-md-6">
				<a id="deleteAccountBtn" class="btn btn-danger form-control m-1 text-center">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" width="24" height="24" style="fill: white">
						<path d="M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 383.8 79.8 304 178.3 304l91.4 0C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7L29.7 512C13.3 512 0 498.7 0 482.3zM471 143c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z"/></svg>
					Supprimer mon compte				</a>
			</div>
			<div id="noticeProfile" class="container mt-4" style="display: none">
				<div class="alert alert-warning" role="alert">
					<h5 class="alert-heading">Attention !</h5>
					<p>Vous êtes connecté en mode <b>invité/guest</b>.Si vous souhaitez lever les restrictions, vous devez être membre certifié.</p><hr>					<p class="mb-0"><a href="https://bounty.chat//?register">Vous pouvez vous enregistrer ici</a></p>
				</div>
			</div>
		</div>
	</div>

</div>

<div class="footer-links">
		<a href="/contact">Contactez-nous</a>
	<a href="/cgu">Conditions d'utilisation</a>
</div>
<script type="43c79d86114da1905a54a368-text/javascript">
	$('[data-bind="avatar"]').click(()=> {
		$('#avatar_file').click();
	});
	$('#avatar_file').on('change', function() {
		const file = this.files[0];
		if (file) {
			const reader = new FileReader();
			reader.onload = function(e) {
				const img = new Image();
				img.onload = function() {
					resizeImage(img, 128, 128).then(function(resizedBlob) {
						const url = URL.createObjectURL(resizedBlob);
						$('#avatarPreview').attr('src', url);
						const formData = new FormData();
						formData.append('a', 'uploadAvatar');
						formData.append('avatar', resizedBlob, 'avatar.png');
						$.ajax({
							url: '/ajax',
							type: 'POST',
							data: formData,
							processData: false,
							contentType: false,
							success: function(res) {
								res = JSON.parse(res);
								if (!res.error) {
									localStorage.setItem('avatar', res.avatar);
									myUser.avatar = res.avatar;
									chat.updateAvatar();
								} else {
									bootbox.alert(res.message)
								}
							},
							error: function(xhr, status, error) {
								console.error('Upload failed:', error);
							}
						});
					}).catch(function(error) {
						console.error('Image resizing failed:', error);
					});
				};
				img.src = e.target.result;
			};
			reader.readAsDataURL(file);
		}
	});
	$('#btn_authentificate').click(()=> {
		bootbox.prompt("Entrez votre adresse email svp", (email) => {
			if (email && checkEmailFormat(email)) {
				$.post('/ajax', { a: 'authentificate', email: email }, (res)=> {
					res = JSON.parse(res);
					console.log(res);
				});
			} else if (email) {
				bootbox.alert("Adresse email invalide. Veuillez entrer une adresse email valide.");
			}
		});
	});
	function applyFontSize(size) {
		$('body').css('font-size', size + 'em');
		localStorage.setItem('fontSize', size);
	}
	const savedFontSize = localStorage.getItem('fontSize');
	if (savedFontSize) {
		applyFontSize(savedFontSize);
		$('#size_chars').val(savedFontSize);
	}

	$('#size_chars').on('change', function() {
		const selectedSize = $(this).val();
		applyFontSize(selectedSize);
	});
</script>				</div>
				<div class="main-content" data-state="room">
					<style>
	body {
		background-color: #f8f9fa;
	}
	h1,h2,h3,h4,h5,h6 {
		letter-spacing: initial;
	}
	.chat-container {
		background-color: #ffffff;
		box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
		overflow: hidden;
		height: 100%;
		position: relative;
	}

	.chat-header {
		font-size: 1.05em;
		gap: 1em;
		font-weight: bold;
		display: flex;
		align-items: center;
		border-bottom: 3px solid #CDCDCD;
		margin: 0 1em;
		padding-top: 1.5em;
	}

	.chat-header img {
		width: 24px;
		height: 24px;
		margin-right: 10px;
	}

	#messagesContainer {
		overflow: auto;
		display: flex;
		flex-grow: 1;
	}
	.public_messages, .private_messages {
		flex-direction: column;
		padding: 0;
		overflow-y: auto;
		flex-grow: 1;
		scrollbar-gutter: stable;
	}

	.public_messages:hover {
		overflow-y: auto;
	}


	.chat-footer {
		padding: 0 3px;
		display: flex;
		align-items: center;
	}

	.chat-footer input {
		border-radius: 20px;
		border: 1px solid #ced4da;
		padding: 8px 15px;
		flex-grow: 1;
		margin-right: 10px;
	}

	.chat-footer button {
		border: none;
		padding: 10px;
		width: 45px;
		background-color: transparent;
	}

	.chat-footer button img {
		width: 20px;
		height: 20px;
	}

	.message-input-group {
		display: flex;
		align-items: center;
		width: 100%;
		margin: 1em 0.5em;

	}

	.message-input-group input {
		flex: 1;
	}



	#public-input, #private-input {
		outline: none;
		height: 3em;
		padding-left: 1em;
		white-space: nowrap;
		overflow-x: auto;
		overflow-y: hidden;
		border-radius: 0.48613rem;
		background: var(--Couleur-primaire-2, #FFFEFA);
		box-shadow: 0 3.111px 7.778px 0 rgba(0, 0, 0, 0.25);
		backdrop-filter: blur(11.666666984558105px);
	}

	#public-input:focus, #private-input:focus {
		outline: none;
		box-shadow: none;
	}

	#public-input.disabled, #private-input.disabled {
		opacity: 0.5;
	}

	.message {
		margin-bottom: 10px;
		font-size: 1em;
		display: flex;
		align-items: end;
		gap: 0.5em;
		border-radius: 15px;
		position: relative;
		padding: 0.5em;
	}

	.message_wrapper {
		display: flex;
		align-items: center;
		gap: 0.5em;
		padding: 0.4em 1em;
		font-size: 0.8em;
		border-radius: 2.10694rem;
		background: rgba(177, 208, 255, 0.72);
		box-shadow: 0 4px 4px 0 rgba(0, 0, 0, 0.25);
		width: fit-content;
	}

	.message span.username {
		font-weight: bold;
		cursor: pointer;
		font-size: 0.8em;
		position: relative;
	}

	.message.sent {
		flex-direction: row-reverse;
	}
	#messagesContainer .message.sent div.online {
		right: 8px;
	}

	.message_wrapper.received {
		background-color: #e9ecef;
	}

	.message_wrapper.private {
		border: 2px dotted #FFF;
	}

	.message_wrapper.sent {
		background-color: #d1e7dd;
	}



	#public-input:empty:before {
		content: attr(data-placeholder);
		color: #888;
		pointer-events: none; /* Prevents the placeholder text from being selectable */
	}

	.image-upload-btn {
		margin-right: 5px; /* Space between the buttons */
	}

	#user-popup img {
		margin-top: 5px; /* Space between the image and other content */
		border-radius: 5px; /* Rounded corners for the image */
	}

	.room-card img {
		transition: transform 0.3s ease;
	}

	.room-card:hover img {
		transform: scale(1.1); /* Increase the scale to 1.1 for a zoom effect */
	}

	div[data-users] {
		font-weight: bold;
		margin-left: auto;
	}

	#public_messages img.photo,
	#public_messages img.video,
	#private_messages img.photo,
	#private_messages img.video
	{
		max-width: 40px;
		max-height: 40px;
		cursor: pointer;
		border-radius: 0.5em;
	}

	/* Add this to your CSS file */
	.recording-animation {
		position: absolute;
		top: 50%;
		left: 50%;
		width: 150px; /* Larger size */
		height: 150px; /* Larger size */
		background-color: red;
		border-radius: 50%;
		transform: translate(-50%, -50%); /* Center the animation */
		animation: pulse 1s infinite;
		z-index: 1; /* Ensure it's behind the microphone icon */
	}

	@keyframes pulse {
		0% {
			transform: translate(-50%, -50%) scale(1);
			opacity: 1;
		}
		50% {
			transform: translate(-50%, -50%) scale(1.5);
			opacity: 0.7;
		}
		100% {
			transform: translate(-50%, -50%) scale(1);
			opacity: 1;
		}
	}

	.record-timer {
		position: absolute;
		top: 50%;
		right: 0;
		transform: translate(-50%, -50%);
		font-size: 14px;
		color: red;
		z-index: 1;
	}
	#webcam_btn {
		cursor: pointer;
		/*display: none;*/
	}

	@media (max-width: 992px) {
		.chat-header {
			margin: 0;
			padding: 0.5em;
		}
		.chat-footer button {
			padding: 5px;
			width: 30px;
		}
		#public-input, #private-input {
			height: 2em;
			line-height: 0.5em;
		}
	}

	#profileUserContainer {
		background: var(--color-primaire-1);
		border-radius: 1em;
		width: 90%;
		display: none;
		justify-content: center;
		align-items: center;
		position: absolute;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
		z-index: 1;
		max-width: 700px;
		flex-direction: column;
		color: white;
		padding: 2em;
	}
	#gallery_private_container {
		max-height: 50dvh;
		overflow: auto;
	}
	#profileUserCloseBtn {
		position: absolute;
		top: 0.5em;
		right: 0.5em;
		cursor: pointer;
	}
	#profileUserContainer div.username {
		font-size: 1.5em;
		font-weight: bold;
	}
	#profileUserContainer img.avatar {
		min-width: 2.5rem;
		width: 6rem;
		height: 6rem;
		border-radius: 2.5rem;
		padding: 3px;
	}
	#profileUserContainer .avatarContainer {
		border-radius: 1.6rem;
		width: 6rem;
		height: 6rem;
	}
	#profileUserContainer .avatarWrapper {
		top:-4em;
		position:absolute
	}
	#profile_user_btn {
		cursor: pointer;
	}



</style>

<div class="chat-container d-flex flex-column">
	<div id="chat-header" class="chat-header">
		<div class="d-flex width100">
			<div data-role="icon" class="d-flex justify-content-center align-items-center"></div>
			<div data-role="name" class="width100 ms-2 d-flex justify-content-center gap-2 flex-column"></div>
			<div>
				<svg id="webcam_btn" viewBox="0 0 41 41" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M20.5 28.1875C26.8685 28.1875 32.0312 23.0248 32.0312 16.6562C32.0312 10.2877 26.8685 5.125 20.5 5.125C14.1315 5.125 8.96875 10.2877 8.96875 16.6562C8.96875 23.0248 14.1315 28.1875 20.5 28.1875Z" stroke="#5D3227" stroke-width="3.84375" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M20.5 21.1406C22.9767 21.1406 24.9844 19.1329 24.9844 16.6562C24.9844 14.1796 22.9767 12.1719 20.5 12.1719C18.0233 12.1719 16.0156 14.1796 16.0156 16.6562C16.0156 19.1329 18.0233 21.1406 20.5 21.1406Z" stroke="#5D3227" stroke-width="3.84375" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M20.5 28.1875V33.3125" stroke="#5D3227" stroke-width="3.84375" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M5.125 33.3125H35.875" stroke="#5D3227" stroke-width="3.84375" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</div>
		</div>
	</div>
	<div id="profileUserContainer">
		<div class="avatar"></div>
		<div id="profileUserCloseBtn">
			<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="white" viewBox="0 0 256 256">
				<path d="M165.66,101.66,139.31,128l26.35,26.34a8,8,0,0,1-11.32,11.32L128,139.31l-26.34,26.35a8,8,0,0,1-11.32-11.32L116.69,128,90.34,101.66a8,8,0,0,1,11.32-11.32L128,116.69l26.34-26.35a8,8,0,0,1,11.32,11.32ZM232,128A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"></path>
			</svg>
		</div>
		<div class="avatarWrapper"></div>
		<div class="username"></div>
		<div id="gallery_private"></div>
		<div id="genderAndAge"></div>
		<div id="cityAndDistance"></div>
		<div id="userDescription"></div>


	</div>

	<div id="messagesContainer">
		<div id="public_messages" class="public_messages" data-state="public"></div>
		<div id="private_messages" class="public_messages" data-state="private"></div>
	</div>
	<div id="typing"></div>

	<div class="chat-footer">
		<div class="message-input-group position-relative">
			<div class="input-buttons top-0 start-0 d-flex">
				<button title="Enregistrer un message audio" id="record_btn" class="btn btn-light position-relative shadow">
					<svg viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M16.7778 6.88892C16.7778 4.74115 15.0367 3.00003 12.8889 3.00003C10.7411 3.00003 9 4.74115 9 6.88892V12.3334C9 14.4811 10.7411 16.2223 12.8889 16.2223C15.0367 16.2223 16.7778 14.4811 16.7778 12.3334V6.88892Z" stroke="#5D3227" stroke-width="2.33333" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M20.6666 12.3333C20.6666 14.3961 19.8472 16.3744 18.3886 17.8331C16.93 19.2917 14.9517 20.1111 12.8889 20.1111C10.8261 20.1111 8.84776 19.2917 7.38914 17.8331C5.93053 16.3744 5.11108 14.3961 5.11108 12.3333" stroke="#5D3227" stroke-width="2.33333" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M12.8889 20.1111V23.2223" stroke="#5D3227" stroke-width="2.33333" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>

					<div id="recording-animation" class="recording-animation" style="display: none;"></div>
				</button>

				<!-- Timer element -->
				<div id="record-timer" class="record-timer" style="display: none;">0:00</div>

				<button title="Emojis" data-bs-toggle="tooltip" class="btn btn-light emoji-btn shadow ">
					<svg  viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M16 28C22.6274 28 28 22.6274 28 16C28 9.37258 22.6274 4 16 4C9.37258 4 4 9.37258 4 16C4 22.6274 9.37258 28 16 28Z" stroke="#5D3227" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M11.5 15.5C12.6046 15.5 13.5 14.6046 13.5 13.5C13.5 12.3954 12.6046 11.5 11.5 11.5C10.3954 11.5 9.5 12.3954 9.5 13.5C9.5 14.6046 10.3954 15.5 11.5 15.5Z" fill="#5D3227"/>
						<path d="M20.5 15.5C21.6046 15.5 22.5 14.6046 22.5 13.5C22.5 12.3954 21.6046 11.5 20.5 11.5C19.3954 11.5 18.5 12.3954 18.5 13.5C18.5 14.6046 19.3954 15.5 20.5 15.5Z" fill="#5D3227"/>
						<path d="M21.2 19C20.6714 19.9107 19.9128 20.6667 19.0002 21.1922C18.0876 21.7176 17.053 21.9942 16 21.9942C14.9469 21.9942 13.9123 21.7176 12.9998 21.1922C12.0872 20.6667 11.3286 19.9107 10.8 19" stroke="#5D3227" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>

				</button>

				<button title="Ajouter photo/image" data-bs-toggle="tooltip" class="btn btn-light image-upload-btn shadow ">
					<svg  viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M20.6667 20.6667H5.1111C4.69854 20.6667 4.30288 20.5028 4.01115 20.2111C3.71943 19.9193 3.55554 19.5237 3.55554 19.1111V8.22223C3.55554 7.80967 3.71943 7.41401 4.01115 7.12229C4.30288 6.83057 4.69854 6.66668 5.1111 6.66668H8.22221L9.77776 4.33334H16L17.5555 6.66668H20.6667C21.0792 6.66668 21.4749 6.83057 21.7666 7.12229C22.0583 7.41401 22.2222 7.80967 22.2222 8.22223V19.1111C22.2222 19.5237 22.0583 19.9193 21.7666 20.2111C21.4749 20.5028 21.0792 20.6667 20.6667 20.6667Z" stroke="#5D3227" stroke-width="2.33333" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M12.8889 16.7778C14.8219 16.7778 16.3889 15.2108 16.3889 13.2778C16.3889 11.3448 14.8219 9.7778 12.8889 9.7778C10.9559 9.7778 9.38892 11.3448 9.38892 13.2778C9.38892 15.2108 10.9559 16.7778 12.8889 16.7778Z" stroke="#5D3227" stroke-width="2.33333" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>

				</button>
				<input type="file" id="image-upload" name="image-upload" accept="image/*, video/mp4" style="display: none;">
			</div>


			<div id="mentionUsernamePlaceholder"></div>
			<div id="public-input"
				 class="form-control d-flex align-items-center pe-0"
				 data-placeholder="Entrez votre message et envoyez avec entrée">

				<!-- Real typing zone -->
				<div id="public-input-editor"
					 class="flex-grow-1 typing-zone"
					 contenteditable="true"
					 style="min-height:1.8em; outline:none; white-space:pre-wrap;"></div>

				<button id="send-btn" type="button"
						class="btn btn-sm ms-auto p-0 pe-0 me-1"
						style="background:transparent; color:var(--scrollbar-thumb);"
						contenteditable="false" tabindex="-1">
					<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
						 viewBox="0 0 24 24" fill="none" stroke="currentColor"
						 stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"
						 focusable="false" aria-hidden="true" style="pointer-events:none;">
						<line x1="22" y1="2" x2="11" y2="13"></line>
						<polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
					</svg>
				</button>
			</div>




			<input type="hidden" id="public-input-hidden" name="public-input-hidden">
			<div id="user-popup" class="user-popup"></div>
		</div>
	</div>
</div>
				</div>

			</div>


			<div id="sidebar" class="col-lg-3 sidebar">
				<div class="ms-2">
					<div class="search-box mx-auto">
						<div class="input-group mx-auto">
							<span class="input-group-text" style="background: transparent;padding: 0">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="24" height="24" fill="currentColor">
  <path d="M505 442.7l-99.7-99.7c28.4-40.2 45.2-89.8 45.2-143C450.5 89.3 361.2 0 256 0S61.5 89.3 61.5 200s89.3 200 200 200c53.2 0 102.8-16.8 143-45.2l99.7 99.7c9.7 9.7 25.4 9.7 35.1 0 9.7-9.8 9.7-25.5 0-35.2zM256 370c-94 0-170-76-170-170S162 30 256 30s170 76 170 170-76 170-170 170z"/>
</svg>

							</span>
							<input id="filterUser" type="text" class="form-control" placeholder="Chercher chatteur">
							<button id="clearInput" type="button" class="btn btn-outline-secondary d-none">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 352 512" width="24" height="24" fill="currentColor">
									<path d="M242.7 256l100.1-100.1c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L197.3 210.7 97.2 110.6c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L152 256 51.9 356.1c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l100.1-100.1 100.1 100.1c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L242.7 256z"/>
								</svg>
							</button>
						</div>
					</div>
				</div>
				<div id="filterContainer" class="text-center mt-2 mb-2 cursor-pointer">

					<svg id="filterIcon" xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 26 26" fill="none">
						<path d="M21.125 9.75L13 17.875L4.875 9.75" stroke="#FFFEFA" stroke-width="2.4375" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					Filtres de recherche				</div>
				<div id="filterGendersContainer" class="ms-2 display-none">
					<div class="d-flex justify-content-center align-items-center mt-4 flex-wrap">
						<label class="custom-checkbox">
							<input type="checkbox" name="gender" value="male" checked>
							<span class="checkmark"></span> Homme						</label>
						<label class="custom-checkbox">
							<input type="checkbox" name="gender" value="female" checked>
							<span class="checkmark"></span> Femme						</label>
						<label class="custom-checkbox">
							<input type="checkbox" name="gender" value="trans" checked>
							<span class="checkmark"></span> Trans						</label>
						<label class="custom-checkbox">
							<input type="checkbox" name="gender" value="couple" checked>
							<span class="checkmark"></span> Couple						</label>
						<label class="custom-checkbox">
							<input type="checkbox" name="role" value="moderator">
							<span class="checkmark"></span> Modérateur						</label>
						<label class="custom-checkbox">
							<input id="filter_webcam_checkbox" type="checkbox" name="webcam" value="1">
							<span class="checkmark"></span> Avec webcam						</label>

					</div>
					<div class="mt-2 mb-2 p-2">
						<label class="form-label">Age entre:
							<span id="ageRangeValue">18 - 99</span> ans						</label>
						<div id="ageRange"></div>
					</div>
					<div class="mt-4 p-2">
						<label for="distanceRange" class="form-label">Distance entre nous:
							<span id="distanceValue">1000</span> km						</label>
						<input type="range" class="form-range" min="50" max="1500" step="50" value="1500" id="distanceRange">
					</div>

					<div class="mt-2 d-flex align-items-center" style="display: none!important;">
						<label class="switch">
							<input type="checkbox" id="filter_webcam">
							<span class="slider round"></span>
						</label>
						<span class="ms-3">Avec webcam</span>
					</div>
				</div>


				<ul id="userTabs" class="nav nav-tabs d-flex justify-content-between ms-2"  >
					<li class="nav-item">
						<button id="aprox-tab" class="nav-link active" type="button"  aria-controls="aprox" >
							<span>
								<svg width="24" height="24" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" class="svg">
									<path d="M16 17C18.2091 17 20 15.2091 20 13C20 10.7909 18.2091 9 16 9C13.7909 9 12 10.7909 12 13C12 15.2091 13.7909 17 16 17Z" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M26 13C26 22 16 29 16 29C16 29 6 22 6 13C6 10.3478 7.05357 7.8043 8.92893 5.92893C10.8043 4.05357 13.3478 3 16 3C18.6522 3 21.1957 4.05357 23.0711 5.92893C24.9464 7.8043 26 10.3478 26 13V13Z" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
								Autour de moi</span> <span id="numberUsers"></span>
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button id="room-tab" class="nav-link" type="button" role="tab" style="display: none" >
							<span>
								<svg width="24" height="24" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M16 28C22.6274 28 28 22.6274 28 16C28 9.37258 22.6274 4 16 4C9.37258 4 4 9.37258 4 16C4 22.6274 9.37258 28 16 28Z" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M4 16H28" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M16 27.6749C18.7614 27.6749 21 22.4479 21 16C21 9.55203 18.7614 4.32495 16 4.32495C13.2386 4.32495 11 9.55203 11 16C11 22.4479 13.2386 27.6749 16 27.6749Z" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
								Dans la room</span> <span id="numberUsersRoom"></span>
						</button>
					</li>
				</ul>

				<div id="private_warning"></div>
				<div id="userTabsContent" class="tab-conintent flex-grow-1 overflow-auto">
					<div class="tab-pane fade show active" id="aprox" role="tabpanel" aria-labelledby="aprox-tab">
						<ul class="nav nav-tabs" id="privateTabs" role="tablist">
							<li class="nav-item" role="presentation">
								<button style="position: relative;" class="nav-link active" id="messages-tab" data-bs-toggle="tab" data-bs-target="#usersPrivateList" type="button" role="tab" aria-controls="usersPrivateList" aria-selected="true">
									<svg width="20px" height="20px" fill="none" stroke="#ffffff" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
										<g stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
											<path d="m11.993 9.2806c-0.8019-0.92603-2.1393-1.1751-3.1442-0.32716-1.0048 0.84796-1.1463 2.2657-0.35721 3.2686 0.4681 0.5949 1.613 1.6606 2.467 2.4292 0.3544 0.3189 0.5316 0.4784 0.7455 0.543 0.183 0.0552 0.3949 0.0552 0.5779 0 0.2139-0.0646 0.3911-0.2241 0.7455-0.543 0.854-0.7686 1.9989-1.8343 2.467-2.4292 0.7891-1.0029 0.6649-2.4295-0.3572-3.2686-1.0222-0.83904-2.3423-0.59887-3.1443 0.32716z" clip-rule="evenodd" fill-rule="evenodd"/>
											<path d="m21 12c0 4.9706-4.0294 9-9 9h-8.9993s1.5592-3.7439 0.93519-4.9992c-0.59906-1.2052-0.93591-2.5636-0.93591-4.0008 0-4.9706 4.0294-9 9-9 4.9706 0 9 4.0294 9 9z"/>
										</g>
									</svg>

									<span>Messages privés <span class="number_unread_private_messages" id="number_unread_private_messages_top"></span></span>
								</button>
							</li>
							<li class="nav-item" role="presentation">
								<button style="position: relative;" class="nav-link" id="friends-tab" data-bs-toggle="tab" data-bs-target="#friendsList" type="button" role="tab" aria-controls="friendsList" aria-selected="false">
									<svg width="20px" height="20px" fill="#ffffff" stroke="#ffffff" preserveAspectRatio="xMidYMid" viewBox="0 -6 44 44" xmlns="http://www.w3.org/2000/svg">
										<path d="m42.001 32h-27.991c-1.102 0-2-0.896-2-1.999v-1.999c0-0.366 0.201-0.702 0.522-0.878l9.786-5.337c-3.278-3.545-3.314-8.56-3.314-8.792l6e-3 -5.993c0-0.056 5e-3 -0.111 0.014-0.165 0.689-4.086 5.2-6.83 8.981-6.83h4e-3c3.779 0 8.289 2.742 8.98 6.827 9e-3 0.055 0.014 0.111 0.014 0.166l3e-3 5.994c0 0.231-0.036 5.246-3.313 8.791l9.786 5.337c0.321 0.176 0.521 0.512 0.521 0.878v2.001c0 1.103-0.897 1.999-1.999 1.999zm-10.475-9.12c-0.293-0.16-0.487-0.455-0.518-0.787-0.03-0.332 0.108-0.657 0.366-0.867 3.597-2.916 3.633-8.178 3.633-8.231l-4e-3 -5.906c-0.562-3-4.12-5.084-6.998-5.084-2.879 1e-3 -6.435 2.086-6.995 5.086l-6e-3 5.906c0 0.051 0.055 5.33 3.632 8.231 0.259 0.21 0.397 0.535 0.366 0.867-0.03 0.332-0.224 0.627-0.517 0.787l-10.475 5.714v1.405h27.989l1e-3 -1.406-10.474-5.715zm-12.879-20.36c-0.883-0.343-1.799-0.523-2.652-0.523-2.879 1e-3 -6.436 2.086-6.996 5.086l-6e-3 5.906c0 0.052 0.054 5.33 3.632 8.231 0.259 0.21 0.397 0.535 0.367 0.867-0.031 0.332-0.225 0.627-0.518 0.787l-10.475 5.714v1.405h6.999c0.552 0 0.999 0.448 0.999 1s-0.447 1-0.999 1h-6.999c-1.102 0-1.999-0.897-1.999-2v-1.999c-0-0.365 0.2-0.702 0.521-0.877l9.786-5.338c-3.277-3.545-3.314-8.56-3.314-8.791l6e-3 -5.994c0-0.055 5e-3 -0.111 0.014-0.165 0.689-4.085 5.2-6.829 8.982-6.829h0.015c1.091-0 2.252 0.227 3.359 0.656 0.516 0.2 0.771 0.779 0.572 1.293-0.201 0.515-0.783 0.771-1.294 0.571z"/>
									</svg>
									Amis <span id="number_friends"></span>
								</button>
							</li>
						</ul>
						<div id="userListContainer">
							<div data-state="usersList" id="usersList" class="flex-grow-1 overflow-auto flex-column"></div>

							<div data-state="usersPrivateList">
								<!-- Tabs navigation -->


								<!-- Tabs content -->
								<div class="tab-content mt-3">
									<div class="tab-pane fade show active" id="usersPrivateList" role="tabpanel" aria-labelledby="messages-tab">
										<!-- Private messages go here -->
									</div>
									<div class="tab-pane fade" id="friendsList" role="tabpanel" aria-labelledby="friends-tab">
										<!-- Friends go here -->
									</div>
								</div>
							</div>

						</div>
					</div>
				</div>
				<div id="menuContainer" class="d-flex flex-column justify-content-between align-items-center">
					<div id="menu_actions" class="d-flex justify-content-around width100">
						<a id="home_menu_label" class="text-white me-3 text-decoration-none cursor-pointer">
							<div data-role="rooms" class="d-flex flex-column align-items-center item position-relative">
								<svg class="svg_menu" id="room_menu_icon" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M16.625 22.75V17.5C16.625 17.2679 16.5328 17.0454 16.3687 16.8813C16.2046 16.7172 15.9821 16.625 15.75 16.625H12.25C12.0179 16.625 11.7954 16.7172 11.6313 16.8813C11.4672 17.0454 11.375 17.2679 11.375 17.5V22.75C11.375 22.9821 11.2828 23.2046 11.1187 23.3687C10.9546 23.5328 10.7321 23.625 10.5 23.625H5.25C5.01794 23.625 4.79538 23.5328 4.63128 23.3687C4.46719 23.2046 4.375 22.9821 4.375 22.75V12.6328C4.37696 12.5117 4.40313 12.3922 4.45197 12.2814C4.50081 12.1706 4.57133 12.0706 4.65937 11.9875L13.4094 4.03593C13.5707 3.88836 13.7814 3.80652 14 3.80652C14.2186 3.80652 14.4293 3.88836 14.5906 4.03593L23.3406 11.9875C23.4287 12.0706 23.4992 12.1706 23.548 12.2814C23.5969 12.3922 23.623 12.5117 23.625 12.6328V22.75C23.625 22.9821 23.5328 23.2046 23.3687 23.3687C23.2046 23.5328 22.9821 23.625 22.75 23.625H17.5C17.2679 23.625 17.0454 23.5328 16.8813 23.3687C16.7172 23.2046 16.625 22.9821 16.625 22.75Z" stroke="#FFFEFA" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
																<div>Accueil</div>
								<span id="number_unread_public_messages" class="number_unread_public_messages"></span>
							</div>
						</a>
						<a id="room_menu_btn" class="text-white me-3 text-decoration-none cursor-pointer disabled">
							<div data-role="room" class="d-flex flex-column align-items-center item position-relative">
								<svg class="svg_menu" id="room_menu_icon" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M14 24.5C19.799 24.5 24.5 19.799 24.5 14C24.5 8.20101 19.799 3.5 14 3.5C8.20101 3.5 3.5 8.20101 3.5 14C3.5 19.799 8.20101 24.5 14 24.5Z" stroke="#FFFEFA" stroke-width="1.75" stroke-miterlimit="10"/>
									<path d="M4.10156 10.5H23.8984" stroke="#FFFEFA" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M4.10156 17.5H23.8984" stroke="#FFFEFA" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M14 24.2156C16.4162 24.2156 18.375 19.6419 18.375 14C18.375 8.35806 16.4162 3.78437 14 3.78437C11.5838 3.78437 9.625 8.35806 9.625 14C9.625 19.6419 11.5838 24.2156 14 24.2156Z" stroke="#FFFEFA" stroke-width="1.75" stroke-miterlimit="10"/>
								</svg>
																<div>Room</div>
							</div>
						</a>
						<a id="private_menu_btn" class="text-white me-3 text-decoration-none cursor-pointer disabled">
							<div data-role="messages" class="d-flex flex-column align-items-center item position-relative">
								<svg class="svg_menu" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M3.72973 15.3891C2.74903 13.7368 2.40529 11.7834 2.76306 9.89557C3.12083 8.00778 4.15551 6.31556 5.67276 5.13672C7.19002 3.95788 9.08546 3.37352 11.0031 3.4934C12.9207 3.61328 14.7287 4.42915 16.0873 5.78777C17.4459 7.1464 18.2618 8.95431 18.3817 10.872C18.5015 12.7896 17.9172 14.685 16.7383 16.2023C15.5595 17.7195 13.8673 18.7542 11.9795 19.112C10.0917 19.4698 8.13824 19.126 6.48598 18.1453L3.76255 18.9219C3.65034 18.9534 3.53175 18.9545 3.41897 18.925C3.3062 18.8956 3.20331 18.8366 3.12089 18.7542C3.03847 18.6717 2.97949 18.5689 2.95001 18.4561C2.92053 18.3433 2.92162 18.2247 2.95317 18.1125L3.72973 15.3891Z" stroke="#FFFEFA" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M10.0734 19.2391C10.4589 20.3381 11.0836 21.3378 11.9026 22.166C12.7216 22.9941 13.7143 23.63 14.809 24.0277C15.9037 24.4254 17.0731 24.5749 18.2326 24.4656C19.3922 24.3562 20.5129 23.9907 21.514 23.3953V23.3953L24.2374 24.1719C24.3496 24.2034 24.4682 24.2045 24.581 24.175C24.6938 24.1456 24.7967 24.0866 24.8791 24.0042C24.9615 23.9217 25.0205 23.8188 25.05 23.7061C25.0794 23.5933 25.0784 23.4747 25.0468 23.3625L24.2702 20.6391C24.9635 19.4753 25.3445 18.1522 25.3761 16.798C25.4078 15.4438 25.0891 14.1043 24.451 12.9095C23.8128 11.7146 22.8769 10.7048 21.7338 9.97801C20.5907 9.25118 19.2792 8.83195 17.9265 8.76093" stroke="#FFFEFA" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
								<div class="number_unread_private_messages" id="number_unread_private_messages"></div>
																<div>Privés</div>
							</div>
						</a>
						<a id="users_menu_btn" class="text-white me-3 text-decoration-none cursor-pointer">
							<div data-role="users" class="d-flex flex-column align-items-center item position-relative">
								<svg class="svg_menu" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M8.75 21.875C10.683 21.875 12.25 20.308 12.25 18.375C12.25 16.442 10.683 14.875 8.75 14.875C6.817 14.875 5.25 16.442 5.25 18.375C5.25 20.308 6.817 21.875 8.75 21.875Z" stroke="#FFFEFA" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M3.5 24.5C4.11128 23.685 4.90392 23.0234 5.81516 22.5678C6.7264 22.1122 7.7312 21.875 8.75 21.875C9.7688 21.875 10.7736 22.1122 11.6848 22.5678C12.5961 23.0234 13.3887 23.685 14 24.5" stroke="#FFFEFA" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M8.75 10.5C10.683 10.5 12.25 8.933 12.25 7C12.25 5.067 10.683 3.5 8.75 3.5C6.817 3.5 5.25 5.067 5.25 7C5.25 8.933 6.817 10.5 8.75 10.5Z" stroke="#FFFEFA" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M3.5 13.125C4.11128 12.31 4.90392 11.6484 5.81516 11.1928C6.7264 10.7372 7.7312 10.5 8.75 10.5C9.7688 10.5 10.7736 10.7372 11.6848 11.1928C12.5961 11.6484 13.3887 12.31 14 13.125" stroke="#FFFEFA" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M19.25 21.875C21.183 21.875 22.75 20.308 22.75 18.375C22.75 16.442 21.183 14.875 19.25 14.875C17.317 14.875 15.75 16.442 15.75 18.375C15.75 20.308 17.317 21.875 19.25 21.875Z" stroke="#FFFEFA" stroke-width="1.75" stroke-linecap="round" stroke-linejoin "round"/>
									<path d="M14 24.5C14.6113 23.685 15.4039 23.0234 16.3152 22.5678C17.2264 22.1122 18.2312 21.875 19.25 21.875C20.2688 21.875 21.2736 22.1122 22.1848 22.5678C23.0961 23.0234 23.8887 23.685 24.5 24.5" stroke="#FFFEFA" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M19.25 10.5C21.183 10.5 22.75 8.933 22.75 7C22.75 5.067 21.183 3.5 19.25 3.5C17.317 3.5 15.75 5.067 15.75 7C15.75 8.933 17.317 10.5 19.25 10.5Z" stroke="#FFFEFA" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M14 13.125C14.6113 12.31 15.4039 11.6484 16.3152 11.1928C17.2264 10.7372 18.2312 10.5 19.25 10.5C20.2688 10.5 21.2736 10.7372 22.1848 11.1928C23.0961 11.6484 23.8887 12.31 24.5 13.125" stroke="#FFFEFA" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
								<div> <span class="number_users" id="number_users"></span></div>
																<div>Users</div>
							</div>
						</a>
						<a id="profile_btn" class="text-white text-decoration-none cursor-pointer">
							<div data-role="profile" class="d-flex flex-column align-items-center item">
								<svg class="svg_menu" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M14 19.25C16.8995 19.25 19.25 16.8995 19.25 14C19.25 11.1005 16.8995 8.75 14 8.75C11.1005 8.75 8.75 11.1005 8.75 14C8.75 16.8995 11.1005 19.25 14 19.25Z" stroke="#FFFEFA" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M21.5907 8.82657C21.854 9.20355 22.0846 9.60241 22.2798 10.0188L25.1126 11.5938C25.4663 13.178 25.47 14.8204 25.1235 16.4063L22.2798 17.9813C22.0846 18.3976 21.854 18.7965 21.5907 19.1734L21.6454 22.4219C20.4449 23.5159 19.0239 24.3401 17.4782 24.8391L14.6892 23.1656C14.2304 23.1984 13.7698 23.1984 13.311 23.1656L10.5329 24.8281C8.9823 24.3382 7.5567 23.5172 6.35478 22.4219L6.40947 19.1844C6.14836 18.8022 5.91795 18.3999 5.72041 17.9813L2.88759 16.4063C2.53387 14.822 2.53014 13.1796 2.87666 11.5938L5.72041 10.0188C5.91562 9.60241 6.14615 9.20355 6.40947 8.82657L6.35478 5.57813C7.55534 4.48412 8.97626 3.65991 10.522 3.16094L13.311 4.83438C13.7698 4.80156 14.2304 4.80156 14.6892 4.83438L17.4673 3.17188C19.0179 3.66177 20.4435 4.4828 21.6454 5.57813L21.5907 8.82657Z" stroke="#FFFEFA" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
																<div>Config</div>
							</div>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<style>
	.modal-fullscreen-custom {
		background-color: #5a2a14;
		color: white;
	}

	.premium-card {
		background: white;
		color: #333;
		border-radius: 1rem;
		padding: 1rem;
		position: relative;
		margin-bottom: 2rem;
	}

	.premium-card h5 {
		font-weight: bold;
		text-align: center;
		margin-bottom: 1rem;
	}

	#emoji18 {
		position: absolute;
		top: -40px;
		left: 0;
		width: 64px;
	}

	#under18 {
		position: absolute;
		bottom: -22px;
		right: -18px;
		width: 60px;
	}

	.star-list {
		list-style: none;
		padding-left: 0;
	}

	.star-list li::before {
		content: '⭐';
		margin-right: 8px;
	}

	.premium-btn {
		background: white;
		border: none;
		color: #5a2a14;
		font-weight: bold;
		padding: 0.75rem 1.25rem;
		border-radius: 0.75rem;
		box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
		width: 75%;
	}

	#premiumModal .modal-close-btn {
		position: absolute;
		top: 1rem;
		right: 1rem;
		background: none;
		border: none;
		font-size: 1.5rem;
		color: white;
		z-index: 10;
	}
</style>


<div class="modal fade" id="premiumModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-fullscreen">
		<div class="modal-content modal-fullscreen-custom d-flex justify-content-center align-items-center">
			<button id="btnClosePremium" type="button" class="modal-close-btn" data-bs-dismiss="modal" aria-label="Fermer">&times;</button>
			<div class="text-center">
				<div class="premium-card d-inline-block position-relative">
					<h5>LES AVANTAGES DU PREMIUM SUR<br>
						<span class="bounty-title"><strong>BOUNTY</strong></span>
					</h5>
					<svg id="emoji18" width="58" height="58" viewBox="0 0 58 58" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M28.818 53.831c-12.642 0-26.28-7.93-26.28-25.33s13.638-25.33 26.28-25.33c7.024 0 13.503 2.312 18.307 6.526 5.21 4.622 7.975 11.147 7.975 18.805s-2.764 14.137-7.975 18.759c-4.803 4.214-11.328 6.57-18.307 6.57" fill="url(#a)"/><path d="M28.818 53.831c-12.642 0-26.28-7.93-26.28-25.33s13.638-25.33 26.28-25.33c7.024 0 13.503 2.312 18.307 6.526 5.21 4.622 7.975 11.147 7.975 18.805s-2.764 14.137-7.975 18.759c-4.803 4.214-11.328 6.57-18.307 6.57" fill="url(#b)"/><path d="M28.818 53.831c-12.642 0-26.28-7.93-26.28-25.33s13.638-25.33 26.28-25.33c7.024 0 13.503 2.312 18.307 6.526 5.21 4.622 7.975 11.147 7.975 18.805s-2.764 14.137-7.975 18.759c-4.803 4.214-11.328 6.57-18.307 6.57" fill="url(#c)"/><path d="M50.519 13.444c2.415 3.897 3.675 8.537 3.675 13.698 0 7.658-2.764 14.138-7.975 18.76-4.803 4.214-11.328 6.57-18.306 6.57-8.184 0-16.784-3.33-21.832-10.395 4.876 8.003 14.042 11.754 22.738 11.754 6.978 0 13.503-2.356 18.306-6.57C52.336 42.64 55.1 36.16 55.1 28.501c0-5.772-1.572-10.902-4.581-15.057" fill="#D84213"/><path d="M12.348 37.455a7.93 7.93 0 1 0 0-15.859 7.93 7.93 0 0 0 0 15.86" fill="url(#d)"/><path d="M45.652 37.455a7.93 7.93 0 1 0 0-15.859 7.93 7.93 0 0 0 0 15.86" fill="url(#e)"/><path d="M29 46.672c7.124 0 12.9-2.404 12.9-5.37 0-2.965-5.776-5.369-12.9-5.369s-12.9 2.404-12.9 5.37c0 2.965 5.775 5.37 12.9 5.37" fill="#422B0D"/><path d="M29.498 44.552a.91.91 0 0 0 .906-.907v-6.217a48 48 0 0 0-1.812-.009v6.226c0 .499.408.906.906.906" fill="#F04"/><path opacity=".3" d="M29.498 44.552a.91.91 0 0 0 .906-.907v-6.217a48 48 0 0 0-1.812-.009v6.226c0 .499.408.906.906.906" fill="#1F0B08"/><path d="M8.845 48.503c-3.833 0-6.783-3.716-6.783-6.901 0-2.243 1.001-4.84 2.42-8.437.176-.53.412-1.06.648-1.654.675-1.686 1.273-3.512 2.18-5.093.615-1.079 2.188-1.079 2.781.013.843 1.554 1.423 3.235 2.298 5.197 2.478 5.547 3.185 7.785 3.185 10.028.055 3.127-2.954 6.847-6.729 6.847" fill="url(#f)"/><path d="M12.991 44.248c-.865 1.341-2.832 1.087-2.832-1.138 0-1.422.29-8.722 1.51-7.703 1.988 1.663 2.555 6.947 1.322 8.84" fill="#81D4FA"/><path d="M12.412 18.02c-.997.178-1.047 1.628.04 1.677 2.22.055 4.627-.752 6.49-2.47a7.9 7.9 0 0 0 1.685-2.034c.548-.996-.72-1.68-1.355-.956l-.045.046c-1.826 2.034-4.323 3.34-6.815 3.738m26.612-3.738-.045-.046c-.64-.72-1.904-.036-1.355.956a7.9 7.9 0 0 0 1.685 2.035c1.863 1.713 4.269 2.524 6.49 2.47 1.087-.05 1.037-1.5.04-1.677-2.497-.399-4.994-1.704-6.815-3.738" fill="#422B0D"/><path d="M30.405 38.905v4.74a.91.91 0 0 1-.907.906.91.91 0 0 1-.906-.907V38.91c-3.376.1-6.076.69-7.635 1.124v5.705a7.8 7.8 0 0 0 7.798 7.798h1.487a7.8 7.8 0 0 0 7.798-7.798v-5.705a31.5 31.5 0 0 0-7.635-1.128" fill="#FF4081"/><path d="M30.404 37.428v1.477c2.338.059 4.926.371 7.635 1.124v-.789c-1.54-.951-4.984-1.744-7.635-1.812" fill="#FF4081"/><path opacity=".3" d="M30.404 37.428v1.477c2.338.059 4.926.371 7.635 1.124v-.789c-1.54-.951-4.984-1.744-7.635-1.812" fill="#AB3F2E"/><path d="M28.592 37.419c-2.664.045-5.514.449-7.635 1.799v.815c1.563-.434 4.264-1.024 7.635-1.123z" fill="#FF4081"/><path opacity=".3" d="M28.592 37.419c-2.664.045-5.514.449-7.635 1.799v.815c1.563-.434 4.264-1.024 7.635-1.123z" fill="#AB3F2E"/><path d="M51.031 26.988c-3.39 0-6-3.285-6-6.104 0-1.98.889-4.277 2.14-7.458.158-.471.367-.938.575-1.46.598-1.49 1.124-3.108 1.926-4.503.548-.956 1.935-.952 2.46.013.748 1.373 1.255 2.86 2.03 4.595 2.189 4.903 2.819 6.883 2.819 8.868.05 2.764-2.61 6.049-5.95 6.049" fill="url(#g)"/><path d="M54.701 23.227c-.766 1.183-2.506.96-2.506-1.006 0-1.255.254-7.712 1.337-6.81 1.758 1.468 2.257 6.144 1.17 7.816" fill="#81D4FA"/><path d="M19.955 20.816c-1.898 0-3.625 1.605-3.625 4.269s1.727 4.268 3.625 4.268c1.899 0 3.625-1.604 3.625-4.268 0-2.665-1.722-4.269-3.625-4.269" fill="#422B0D"/><path d="M19.779 22.597c-.467-.326-1.17-.222-1.622.43-.454.657-.304 1.346.163 1.672.466.327 1.169.223 1.622-.43.453-.652.308-1.346-.163-1.672" fill="#896024"/><path d="M37.337 20.816c-1.899 0-3.625 1.605-3.625 4.269s1.726 4.268 3.625 4.268c1.898 0 3.625-1.604 3.625-4.268 0-2.665-1.727-4.269-3.625-4.269" fill="#422B0D"/><path d="M37.16 22.597c-.466-.326-1.169-.222-1.622.43-.453.657-.303 1.346.163 1.672.467.327 1.17.223 1.623-.43.457-.657.303-1.346-.163-1.672" fill="#896024"/><defs><radialGradient id="a" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(28.818 28.502)scale(25.8099)"><stop offset=".5" stop-color="#FDE030"/><stop offset=".919" stop-color="#F7C02B"/><stop offset="1" stop-color="#F4A223"/></radialGradient><radialGradient id="b" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(28.818 28.502)scale(25.8099)"><stop offset=".123" stop-color="#F4A223"/><stop offset=".356" stop-color="#F49F22"/><stop offset=".539" stop-color="#F6951D"/><stop offset=".705" stop-color="#F88416"/><stop offset=".861" stop-color="#FB6D0C"/><stop offset="1" stop-color="#FF5100"/></radialGradient><radialGradient id="d" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="matrix(8.6266 0 0 8.19487 12.348 29.527)"><stop offset=".005" stop-color="#ED0E00"/><stop offset=".145" stop-color="#ED1709" stop-opacity=".843"/><stop offset=".379" stop-color="#ED2F23" stop-opacity=".582"/><stop offset=".675" stop-color="#ED554C" stop-opacity=".251"/><stop offset=".9" stop-color="#ED7770" stop-opacity="0"/></radialGradient><radialGradient id="e" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="matrix(8.6266 0 0 8.19487 45.653 29.527)"><stop offset=".005" stop-color="#ED0E00"/><stop offset=".145" stop-color="#ED1709" stop-opacity=".843"/><stop offset=".379" stop-color="#ED2F23" stop-opacity=".582"/><stop offset=".675" stop-color="#ED554C" stop-opacity=".251"/><stop offset=".9" stop-color="#ED7770" stop-opacity="0"/></radialGradient><radialGradient id="f" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="matrix(15.135 0 0 23.3079 9.33 28.52)"><stop offset=".46" stop-color="#4FC3F7"/><stop offset="1" stop-color="#1E88E5"/></radialGradient><radialGradient id="g" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="matrix(13.3816 0 0 20.6077 51.461 9.321)"><stop offset=".46" stop-color="#4FC3F7"/><stop offset="1" stop-color="#1E88E5"/></radialGradient><linearGradient id="c" x1="28.818" y1="53.831" x2="28.818" y2="3.172" gradientUnits="userSpaceOnUse"><stop stop-color="#F4A223"/><stop offset=".083" stop-color="#F4A223" stop-opacity=".905"/><stop offset=".877" stop-color="#F4A223" stop-opacity="0"/></linearGradient></defs></svg>
					<svg id="under18" width="65" height="65" viewBox="0 0 65 65" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M32.5 2.031c-16.829 0-30.469 13.64-30.469 30.469S15.671 62.969 32.5 62.969 62.969 49.329 62.969 32.5 49.329 2.031 32.5 2.031M57.89 32.5a25.07 25.07 0 0 1-5.738 16.047l-35.699-35.7A25.07 25.07 0 0 1 32.5 7.11c14.02 0 25.39 11.37 25.39 25.391m-50.78 0a25.07 25.07 0 0 1 5.738-16.047l35.699 35.7A25.07 25.07 0 0 1 32.5 57.89c-14.02 0-25.39-11.37-25.39-25.39" fill="#C33"/><path d="M30.52 60.531c15.733 0 28.488-12.754 28.488-28.488S46.253 3.555 30.519 3.555 2.032 16.309 2.032 32.043 14.786 60.531 30.52 60.531" fill="#F44336"/><path d="M48.547 52.153A25.07 25.07 0 0 1 32.5 57.89c-14.02 0-25.39-11.37-25.39-25.39a25.07 25.07 0 0 1 5.738-16.048l3.605-3.605A25.07 25.07 0 0 1 32.5 7.11c14.02 0 25.39 11.37 25.39 25.39a25.07 25.07 0 0 1-5.737 16.047" fill="#fff"/><path d="M48.547 52.153A25.07 25.07 0 0 1 32.5 57.89c-14.02 0-25.39-11.37-25.39-25.39a25.07 25.07 0 0 1 5.738-16.048l3.605-3.605A25.07 25.07 0 0 1 32.5 7.11c14.02 0 25.39 11.37 25.39 25.39a25.07 25.07 0 0 1-5.737 16.047" fill="#231F20"/><path d="M48.954 52.406c-10.406 8.5-25.726 6.957-34.227-3.448-7.318-8.953-7.318-21.82 0-30.779l3.453-3.453a24.02 24.02 0 0 1 15.387-5.484c13.431 0 24.324 10.887 24.324 24.324a24.34 24.34 0 0 1-5.484 15.387" fill="#414042"/><path d="M47.226 29.605a6.83 6.83 0 0 1-1.167 3.91 7.8 7.8 0 0 1-3.149 2.691 8.6 8.6 0 0 1 3.656 2.895 7.2 7.2 0 0 1 1.371 4.316 7.71 7.71 0 0 1-2.59 6.094c-1.726 1.524-4.011 2.285-6.804 2.285s-5.13-.761-6.805-2.285a7.71 7.71 0 0 1-2.59-6.094 7.6 7.6 0 0 1 1.32-4.316 8.64 8.64 0 0 1 3.606-2.945 7.64 7.64 0 0 1-3.098-2.692 6.9 6.9 0 0 1-1.117-3.91c0-2.488.813-4.469 2.387-5.941 1.574-1.473 3.707-2.184 6.297-2.184s4.672.711 6.297 2.184a7.96 7.96 0 0 1 2.386 5.992M44.13 43.367a5.44 5.44 0 0 0-1.574-4.063 5.75 5.75 0 0 0-4.114-1.574 5.24 5.24 0 0 0-5.586 5.586 5.37 5.37 0 0 0 1.473 3.96c.965.966 2.387 1.423 4.164 1.423s3.149-.457 4.113-1.473c1.016-.863 1.524-2.184 1.524-3.86m-5.637-18.79a4.7 4.7 0 0 0-3.555 1.372 4.97 4.97 0 0 0-1.37 3.707 5.03 5.03 0 0 0 1.37 3.656 4.8 4.8 0 0 0 3.606 1.371 4.8 4.8 0 0 0 3.605-1.371c.94-.98 1.437-2.3 1.371-3.656A4.78 4.78 0 0 0 42.097 26a5.1 5.1 0 0 0-3.605-1.422m-13.558-2.742-7.262 2.742a.82.82 0 0 0-.559.813v1.625c0 .477.392.868.869.868q.153-.001.3-.056l2.488-.914a.863.863 0 0 1 1.112.513c.035.097.05.198.056.3v22.902a.89.89 0 0 0 .863.864h2.082a.89.89 0 0 0 .863-.864V22.395c0-.34-.279-.62-.624-.615q-.047-.001-.087.005zm18.027-4.216h-.558a.343.343 0 0 0-.356.336V19.5a.343.343 0 0 1-.335.355h-.274a.34.34 0 0 1-.356-.335v-5.15a.343.343 0 0 1 .335-.355h1.696a2.42 2.42 0 0 1 1.575.457c.375.335.578.818.558 1.32a1.7 1.7 0 0 1-.305 1.016 1.46 1.46 0 0 1-.507.457.46.46 0 0 0-.153.508l1.168 2.133v.05h-.863a.5.5 0 0 1-.355-.202l-.965-1.88c-.051-.202-.153-.253-.305-.253m-.914-1.218a.343.343 0 0 0 .335.355h.68a1.3 1.3 0 0 0 .813-.253.85.85 0 0 0 .305-.711 1.02 1.02 0 0 0-.254-.711 1.3 1.3 0 0 0-.813-.254h-.71a.343.343 0 0 0-.356.335zm-2.793.862h-1.98a.22.22 0 0 0-.204.203v1.371a.22.22 0 0 0 .203.204h2.336a.22.22 0 0 1 .203.203v.406a.22.22 0 0 1-.203.203h-3.351a.22.22 0 0 1-.203-.203v-5.383a.22.22 0 0 1 .203-.203h3.351a.22.22 0 0 1 .203.203v.406a.22.22 0 0 1-.203.203h-2.336a.22.22 0 0 0-.203.204v1.168a.22.22 0 0 0 .203.203h1.98a.22.22 0 0 1 .204.203v.406a.187.187 0 0 1-.173.203zm-8.531 2.387V14.27a.22.22 0 0 1 .203-.203h1.472a2.55 2.55 0 0 1 1.371.356 2.1 2.1 0 0 1 .915.964c.208.447.31.93.304 1.422v.305c.016.493-.091.98-.304 1.422a2.4 2.4 0 0 1-.914.965 3.1 3.1 0 0 1-1.372.355h-1.472a.22.22 0 0 1-.203-.203m1.015-4.57v3.758a.22.22 0 0 0 .203.203h.457a1.53 1.53 0 0 0 1.22-.508c.314-.406.456-.914.405-1.422v-.305a2.3 2.3 0 0 0-.406-1.421 1.49 1.49 0 0 0-1.168-.508h-.508a.22.22 0 0 0-.203.203m-8.124-1.017a.22.22 0 0 1 .203.203v3.656a1.88 1.88 0 0 1-.61 1.473 2.3 2.3 0 0 1-1.574.558 2.27 2.27 0 0 1-1.574-.507 1.84 1.84 0 0 1-.559-1.473V14.32a.22.22 0 0 1 .203-.203h.61a.22.22 0 0 1 .203.203v3.656c-.025.32.086.635.305.863.233.214.548.32.863.305a1.058 1.058 0 0 0 1.168-1.219V14.32a.22.22 0 0 1 .203-.203c0-.051.559-.051.559-.051m5.586 5.789h-.66c-.052 0-.153-.05-.153-.102l-2.133-3.402c-.102-.152-.406-.101-.406.102V19.6a.22.22 0 0 1-.203.203h-.61a.22.22 0 0 1-.203-.203v-5.33a.22.22 0 0 1 .203-.203h.66c.051 0 .153.05.153.101l2.133 3.403c.101.152.406.101.406-.102V14.27a.22.22 0 0 1 .203-.203h.558a.22.22 0 0 1 .204.203v5.332a.18.18 0 0 1-.087.239.14.14 0 0 1-.066.015" fill="#FAFAFA"/><path opacity=".8" d="m11.883 18.078 36.36 33.77 1.015-.965-33.719-33.922" fill="#231F20"/><path d="M52.761 49.157 12.847 9.596l-3.605 3.606 39.914 39.559" fill="#F44336"/><path d="M22.852 5.535c.863-.203 2.133-.812 2.996-.559.553.153.889.711.762 1.27a1.33 1.33 0 0 1-1.016.762c-1.98.492-3.9 1.193-5.738 2.082a26.4 26.4 0 0 0-9.192 8.023c-.965 1.371-1.726 2.793-2.945 3.961a.63.63 0 0 1-.406.203.37.37 0 0 1-.254-.05c-.61-.255-.711-.56-.61-1.169.092-.512.264-1.01.508-1.472a29 29 0 0 1 1.676-2.59 23.4 23.4 0 0 1 3.352-4.062c3.453-3.2 7.921-5.637 10.867-6.399" fill="#FF8A80"/><path d="m16.453 12.848-1.015 1.016 36.105 36.105.914-1.117z" fill="#C33"/></svg>

				</div>


				<ul class="star-list text-start mx-auto p-4">
					<li>Pas de publicité</li>
					<li>Créez votre propre room et soyez modérateur de votre room !</li>
					<li>Votre room en sous domaine ex: <b>https://sabrina.bounty.chat</b></li>
					<li>Tous vos messages privés sont enregistrés</li>
					<li>Et bien plus...</li>
				</ul>

									<div class="mb-3">
						<a href="/payment/1" class="premium-btn d-inline-block text-decoration-none">
							/ 1 mois – 5.95€
						</a>
					</div>
									<div class="mb-3">
						<a href="/payment/2" class="premium-btn d-inline-block text-decoration-none">
							/ 6 mois – 29.00€
						</a>
					</div>
									<div class="mb-3">
						<a href="/payment/3" class="premium-btn d-inline-block text-decoration-none">
							/ 12 mois – 49.00€
						</a>
					</div>
				
				<div class="mb-3 alert alert-info star-list text-start mx-auto p-2" style="width: 75%">
					Sans engagement, résiliable à tout moment.				</div>

				<button class="premium-btn mt-4" data-bs-dismiss="modal">
					⭐ PASSEZ PREMIUM !				</button>
			</div>
		</div>
	</div>
</div>
		<script src="/assets/js/chat.js?cache=1764753961" type="43c79d86114da1905a54a368-text/javascript"></script>
	<script type="43c79d86114da1905a54a368-text/javascript">
		var tra = {"Image Preview":"Image Preview","Search Gif":"Chercher Gif","chat_bubble_color":"Couleur bulle chat","font_size":"Taille caract\u00e8res","private_messages_male":"Bloquer messages priv\u00e9s homme","percent_80":"80%","percent_90":"90%","percent_100":"100%","percent_110":"110%","percent_120":"120%","percent_130":"130%","logout":"Se d\u00e9connecter","delete_account":"Supprimer mon compte","notice_heading":"Attention !","notice_message":"Vous \u00eates connect\u00e9 en mode <b>invit\u00e9\/guest<\/b>. Si vous souhaitez lever les restrictions, vous devez \u00eatre membre certifi\u00e9.","register_prompt":"Vous pouvez vous enregistrer ici","authenticate_account":"Authentifier son compte","recover_email":"R\u00e9cup\u00e9rer email","contact_us":"Contactez-nous","terms_of_use":"Conditions d'utilisation","upload_failed":"\u00c9chec du t\u00e9l\u00e9versement :","resizing_failed":"La redimension de l'image a \u00e9chou\u00e9 :","email_prompt":"Entrez votre adresse email svp","invalid_email_alert":"Adresse email invalide. Veuillez entrer une adresse email valide.","font_size_saved":"Taille de la police enregistr\u00e9e avec succ\u00e8s.","back":"Retour","your email":"Votre email","username label":"Pseudonyme","chat without registration":"Chat sans inscription","create certified account":"Cr\u00e9er compte certifi\u00e9","chat now":"CHATTEZ !","already have account":"D\u00e9j\u00e0 un compte ?","adult warning":"Attention ","adult warning text":"Ce site est r\u00e9serv\u00e9 aux adultes. Veuillez confirmer que vous avez 18 ans.<br>Bounty chat abandonne l'anonymat.","male":"Homme","female":"Femme","transgender":"Trans\/Trav","couple":"Couple","age label":"Age","zip code label":"Code postal","site title":"LE TCHAT AVEC INSCRIPTION","terms of use":"CGU","privacy":"Confidentialit\u00e9","blog":"Blog","contact":"Contact","forgotten password":"Mot de passe perdu ?","send password":"Envoyez mot de passe !","email label":"Votre email","password label":"Mot de passe","forgotten":"Mot de passe perdu","login":"Se connecter","register":"S'inscrire","my email":"Mon email","list of rooms":"Liste des rooms","adult rooms":"Rooms adultes","starred rooms":"Rooms vedette","search user":"Chercher chatteur","more filters":"Filtres de recherche","trans":"Trans","distance between us":"Distance entre nous","age between":"Age entre","years":"ans","km":"km","normal":"Normal","guest":"Invit\u00e9","moderator":"Mod\u00e9rateur","admin":"Admin","with webcam":"Avec webcam","around me":"Autour de moi","in the room":"Dans la room","home":"Accueil","room":"Room","privates":"Priv\u00e9s","users":"Users","config":"Config","enter your message":"Entrez votre message et envoyez avec entr\u00e9e","profile picture":"Votre avatar","color bubble":"Couleur bulle chat","size of chars":"Taille caract\u00e8res","block private men messages":"Bloquer messages priv\u00e9s homme","delete my account":"Supprimer mon compte","warning":"Attention !","you can register here":"Vous pouvez vous enregistrer ici","you are connected as guest":"<p>Vous \u00eates connect\u00e9 en mode <b>invit\u00e9\/guest<\/b>.Si vous souhaitez lever les restrictions, vous devez \u00eatre membre certifi\u00e9.<\/p><hr>","footer links":"\t<a href=\"\/contact\">Contactez-nous<\/a>\n\t<a href=\"\/cgu\">Conditions d'utilisation<\/a>\n","my_friends":"Mes amis en ligne","install_app":"Install Application \ud83e\udd65Bounty Chat\ud83e\udd65","my_email_label":"My email","my_password_label":"My password","email_placeholder":"email","password_placeholder":"password","valid_zip_code_error":"Entrez un code postal valide.","connect with google":"Se connecter avec Google","Page not found":"Page non trouv\u00e9e","Page not authorized":"Page not authorized","Email Sent. Check Email":"Email envoy\u00e9. Il faut v\u00e9rifier votre bo\u00eete de r\u00e9ception et cliquer sur le lien de v\u00e9rification envoy\u00e9.","Maximum number pics in gallery":"Erreur: Nombre maximum d'images dans la gallerie: ","Email not valid":"Email non valide","Junk email not allowed":"Junk email non permis","Authentificate your email":"Authentifiez votre email.","To authenticate your account, click on link":"Pour authentifier votre compte, cliquez sur le lien suivant:<hr>%s<br><br>Votre mot de passe pour une prochaine connexion est: <b>%s<\/b>","Size of file too big":"Taille du fichier trop grande. Maximum: %d Kbytes.","Error uploading video":"Error uploading video","Size of video too big":"Taille maximale de la vid\u00e9o : %d Kbytes","File is too large":"Taille trop grande","Vpn is forbidden":"L'utilisation des VPNs est interdite","Welcome to chat":"Bienvenue sur le chat","Photo was deleted":"La photo %s a \u00e9t\u00e9 supprim\u00e9e.","Impossible to delete photo":"Impossible de supprimer la photo %s.","Photo does not exist":"La photo %s n'existe pas.","No photo to delete":"Aucune photo \u00e0 supprimer dans le message.","Video was deleted":"La vid\u00e9o %s a \u00e9t\u00e9 supprim\u00e9e.","Impossible to delete video":"Impossible de supprimer la vid\u00e9o %s.","Video does not exist":"La vid\u00e9o %s n'existe pas.","No video to delete":"Aucune vid\u00e9o \u00e0 supprimer dans le message.","Error token":"Erreur token","Password was reset":"Le mot de passe a bien \u00e9t\u00e9 r\u00e9initialis\u00e9","You are banned":"Vous avez \u00e9t\u00e9 banni du chat.","Account not validated yet":"Votre compte n'a pas encore \u00e9t\u00e9 valid\u00e9","Welcome to ":"Bienvenue sur %s","Connected with success":"Connect\u00e9 au site avec succ\u00e8s","Email or password incorrect":"Email ou mot de passe incorrect","Your account was authetificated":"Votre compte a bien \u00e9t\u00e9 authentifi\u00e9.","You can now login":"Vous pouvez d\u00e9sormais vous connecter avec ces identifiants.","Back to chat":"Retour vers le chat","General conditions":"CGU bounty chat","Policy confidentiality":"Politique de confidentialit\u00e9 bounty chat","Contact us":"Contactez-nous","Your name":"Votre nom","Your email":"Votre email","Your request":"Votre demande","Send":"Envoyer","Your request was sent successfully":"Votre demande a \u00e9t\u00e9 envoy\u00e9e avec succ\u00e8s !","Error Sending":"Erreur lors de l'envoi. Veuillez r\u00e9essayer.","reset password":"Reset password","record audio message":"Enregistrer un message audio","seo":"\t<h4>Bienvenue sur Bounty ! Votre espace de tchat en ligne libre et convivial <\/h4>\n\t<h5>Une plateforme de tchat gratuit pens\u00e9e pour vous<\/h5>\n\tBounty r\u00e9volutionne le monde des conversations en ligne en proposant une exp\u00e9rience de tchat en ligne unique et innovante. Notre plateforme a \u00e9t\u00e9 con\u00e7ue pour r\u00e9pondre \u00e0 toutes vos attentes en mati\u00e8re de\n\tcommunication instantan\u00e9e. Que vous soyez \u00e0 la recherche de nouvelles amiti\u00e9s, d'\u00e9changes culturels ou simplement de moments de d\u00e9tente, Bounty est l'endroit id\u00e9al pour vous connecter avec des personnes\n\tpartageant vos centres d'int\u00e9r\u00eat.\n\t<h5>La libert\u00e9 d'un tchat gratuit avec inscription<\/h5>\n\tFini les processus d'inscription fastidieux ! Bounty vous propose un tchat gratuit sans inscription pour une exp\u00e9rience fluide et imm\u00e9diate. En quelques secondes, acc\u00e9dez \u00e0 notre tchat direct et commencez \u00e0\n\t\u00e9changer avec des milliers d'utilisateurs. Cette simplicit\u00e9 d'acc\u00e8s fait de Bounty le choix privil\u00e9gi\u00e9 pour ceux qui souhaitent profiter d'un tchat libre sans contraintes.\n\t<h5>Une exp\u00e9rience de tchat s\u00e9curis\u00e9e<\/h5>\n\tVotre vie priv\u00e9e est notre priorit\u00e9. Sur Bounty, profitez d'un tchat anonyme qui garantit la confidentialit\u00e9 de vos \u00e9changes. Notre syst\u00e8me de s\u00e9curit\u00e9 avanc\u00e9 prot\u00e8ge vos conversations tout en vous\n\tpermettant de vous exprimer librement. Choisissez votre pseudonyme et commencez \u00e0 tchatter sans compromettre votre identit\u00e9.\n\t<h5>Des fonctionnalit\u00e9s innovantes pour des \u00e9changes enrichis<\/h5>\n\tTchat Cam pour des conversations plus authentiques\n\tEnrichissez vos discussions gr\u00e2ce \u00e0 notre fonction tchat cam de derni\u00e8re g\u00e9n\u00e9ration. Optez pour des conversations vid\u00e9o fluides et de haute qualit\u00e9 pour des \u00e9changes plus personnels et vivants. Notre\n\ttechnologie optimis\u00e9e garantit une exp\u00e9rience sans latence, id\u00e9ale pour des conversations naturelles et spontan\u00e9es.\n\tSalons th\u00e9matiques personnalis\u00e9s\n\tBounty propose une vari\u00e9t\u00e9 de salons de chat en ligne adapt\u00e9s \u00e0 tous les go\u00fbts et centres d'int\u00e9r\u00eat. Musique, cin\u00e9ma, sport, culture, ou simplement discussions d\u00e9contract\u00e9es - trouvez votre communaut\u00e9\n\tid\u00e9ale parmi nos nombreux espaces de discussion.\n\t<h5>Une communaut\u00e9 vivante et respectueuse<\/h5>\n\tNotre tchat en ligne se distingue par sa communaut\u00e9 accueillante et bienveillante. Les mod\u00e9rateurs de Bounty veillent 24h\/24 au respect des r\u00e8gles de convivialit\u00e9 pour garantir des \u00e9changes agr\u00e9ables et\n\tenrichissants. Que vous soyez nouveau ou utilisateur r\u00e9gulier, vous trouverez toujours une oreille attentive et des conversations stimulantes.\n\t<h5>Accessible sur tous vos appareils<\/h5>\n\tRestez connect\u00e9 o\u00f9 que vous soyez ! Bounty s'adapte parfaitement \u00e0 tous vos appareils. Notre tchat direct est optimis\u00e9 pour les smartphones, tablettes et ordinateurs, vous permettant de poursuivre vos\n\tconversations en toute circonstance.\n\t<h5>Rejoignez l'aventure Bounty d\u00e8s maintenant !<\/h5>\n\tNe manquez pas l'opportunit\u00e9 de faire partie de la communaut\u00e9 de tchat la plus dynamique du web. Bounty vous offre un espace de tchat gratuit o\u00f9 convivialit\u00e9 rime avec libert\u00e9. Lancez-vous dans l'aventure\n\tet d\u00e9couvrez le plaisir des conversations authentiques dans un environnement moderne et s\u00e9curis\u00e9.\n\tConnectez-vous d\u00e8s maintenant et d\u00e9couvrez pourquoi des milliers d'utilisateurs ont d\u00e9j\u00e0 choisi Bounty comme leur plateforme de tchat en ligne pr\u00e9f\u00e9r\u00e9e !","No such email address":"Aucun compte avec l'adresse <b>%s<\/b> indiqu\u00e9e","forgotten content":"<h1>Mot de passe perdu ?<\/h1><p>Voici le lien pour r\u00e9-initialiser votre mot de passe: <br><b>%s<\/b><\/p><p>A bient\u00f4t sur %s<\/p>","forgotten email":"Mot de passe oubli\u00e9","email with password was sent":"Un email avec un mot de passe  vous a \u00e9t\u00e9 envoy\u00e9.","username forbidden":"Pseudo interdit","Username already exist":"Pseudo d\u00e9j\u00e0 existant","Email already exists":"Email d\u00e9j\u00e0 existant","Email sent to":"Email envoy\u00e9 \u00e0 %s","No such user":"Aucun utilisateur.","Try again later":"Trop de tentatives. Essayez dans 1 heure.","Sitename Authentificate your email":"%s, authentifiez votre email.","To authentificate your account, click on link":"Pour authentifier votre compte, cliquez sur le lien suivant:<hr>%s<br><br>","Your password is":"Votre mot de passe pour une prochaine connexion est: <b>%s<\/b>","Forbidden, no session !":"Forbidden, no session !","Role Forbidden":"Role Forbidden","No role name":"No role name","No rights to access this page":"<b>%s<\/b>, No rights to access this page <b>%s<\/b>","Access my account":"Acc\u00e9der \u00e0 mon compte","Select a webcam":"Select a webcam","Select an audio input":"Select an audio input","Report illegal activity":"Souhaitez vous reporter un comportement ill\u00e9gal de","Describe illegal activity":"D\u00e9crivez quel genre de comportement ill\u00e9gal svp","Thanks for contribution":"Merci pour votre contribution.","Would you like to unblock":"Souhaitez vous d\u00e9bloquer","Would you like to block":"Souhaitez vous bloquer","You will not receive more messages from him":"Vous ne recevrez plus de messages de sa part.","Chatters from region":"Chatteurs du d\u00e9partement","minute":"min","minutes":"mins","hour":"heure","hours":"heures","day":"jour","days":"jours","week":"semaine","weeks":"semaines","Kick":"Kick","Ban":"Ban","UnBan":"Unban","photo":"photo","delete image from gallery ?":"Effacer cette image de la gallerie ?","Choose cam and micro":"Choisir cam et micro","choose":"Choisir","Access to webcam impossible":"Acc\u00e8s \u00e0 la cam impossible:","An email will be send. Click on it":"Un email vous sera adress\u00e9 avec un lien de v\u00e9rification. Il suffira de cliquer sur ce lien pour v\u00e9rifier votre email","Adding friends only available for authetificated":"L'ajout des amis est uniquement accessible <a href=# data-bs-toggle=modal data-bs-target=#premiumModal>aux utilisateurs abonn\u00e9s.<\/a>.","remove":"Supprimer","from_friend_list":"de la liste des amis ?","add_prompt":"Souhaitez vous ajouter","as_friend":"comme ami ?","Gender":"Genre","Age":"\u00c2ge","Postal code":"Code postal","Modify your data":"Modifier vos informations","Enter valid postal code":"Veuillez entrer un code postal valide \u00e0 5 chiffres.","Error accessing audio devices":"Error accessing audio devices. Please check your settings.","Report message to moderator":"Reporter ce message \u00e0 un mod\u00e9rateur","Explain why report":"Expliquez pourquoi vous signalez ce message...","Ban User":"Ban User","Ban duration":"Dur\u00e9e du ban","year":"year","Explanation":"Explication","Reason of ban":"Raison du ban...","Cancel":"Cancel","Enter IP to unban":"Entrez adresse IP \u00e0 d\u00e9bannir","Delete your account":"Effacer votre compte ? <br>Tapez <b>Oui<\/b> pour confirmer","Private message":"Message priv\u00e9","Mention":"Mentionner","Attention, Illegal photo will be reported":"Attention, Toute photo ill\u00e9gale sera automatiquement supprim\u00e9e et signal\u00e9e \u00e0 la plateforme Pharos accompagn\u00e9e de votre adresse IP.","Your data has been updated":"Vous donn\u00e9es ont bien \u00e9t\u00e9 actualis\u00e9es.","Images JPEG and PNG":"Images JPEG et PNG","Click here to authentificate your email":"Cliquez ici pour v\u00e9rifier votre adresse email","User profile":"Profile d'un user","Report this user":"Alerter sur cet utilisateur","Add Remove Friend":"Ajouter\/Supprimer comme ami","Problem anonymous navigator":"Probl\u00e8me navigateur anonyme. Veuillez utiliser un navigateur standard.","Attention : Inappropriate content":"Attention : Contenu inappropri\u00e9. Vous risquez un bannissement prochain.","Moderation requested":"Moderation demand\u00e9e","reports":"signale","Message written by":"Message \u00e9crit par","Delete message":"Effacer message","would_like_to_show_you_their_cam":"voudrait vous montrer sa cam","What to do ?":"Que voulez-vous faire ?","Watch cam":"Voir cam","Deny":"Refuser","Block":"Bloquer","typing":"En train d'\u00e9crire","hot_rooms_are_reserved":"Les rooms \"hot\" sont r\u00e9serv\u00e9es aux membres enregistr\u00e9s.","as_a":"En tant que","you_cannot_enter_this_room":"vous n'avez pas le droit d'entrer dans cette room.","watchers":"watchers","City":"City","Room of users":"Rooms des membres","room name already used":"Nom de salle d\u00e9j\u00e0 utilis\u00e9.","payment_success_title":"Paiement effectu\u00e9 avec succ\u00e8s","payment_success_heading":"Paiement effectu\u00e9 avec succ\u00e8s","payment_success_text":"Merci pour votre paiement. Vous recevrez un e-mail de confirmation sous peu.<br>Vous pouvez d\u00e8s maintenant <a href='\/'>retourner sur le site.<\/a>","payment_error_title":"Erreur de paiement","payment_error_heading":"Le paiement a \u00e9chou\u00e9","payment_error_text":"Une erreur est survenue lors de votre tentative de paiement. Vous allez \u00eatre redirig\u00e9 vers l'accueil dans quelques secondes.","premium_benefits_title":"LES AVANTAGES DU PREMIUM SUR","premium_no_ads":"Pas de publicit\u00e9","personal_link_room":"Votre room en sous domaine ex: <b>https:\/\/sabrina.bounty.chat<\/b>","premium_own_room":"Cr\u00e9ez votre propre room et soyez mod\u00e9rateur de votre room !","premium_save_messages":"Tous vos messages priv\u00e9s sont enregistr\u00e9s","premium_cta":"\u2b50 PASSEZ PREMIUM !","Not VIP Status":"Not VIP Status","Country":"Pays","Premium until":"Vous \u00eates Premium jusqu'au","Get PREMIUM !":"MODE SANS PUB","Manage my subscription":"G\u00e9rer mon abonnement","and more...":"Et bien plus...","Manage my room":"G\u00e9rer mon abo.","guest_not_allowed":"Vous ne pouvez pas entrer dans cette room comme simple invit\u00e9: il faut \u00eatre authentifi\u00e9","room_password_incorrect":"Cette room est prot\u00e9g\u00e9e par un mot de passe. Mot de passe incorrect.","Enter password":"Entrez le mot de passe","Join":"Rejoindre","banned":"Vous avez \u00e9t\u00e9 banni de cette room","One payment shot":"Sans engagement, r\u00e9siliable \u00e0\u00a0tout\u00a0moment.","Secured payement":"Paiement s\u00e9curis\u00e9","Card number":"Num\u00e9ro de carte","Enter card number":"Entrez votre num\u00e9ro de carte","Expiration date":"Date d\u2019expiration","Code CVV":"Code CVV","Name and first Name":"Pr\u00e9nom d\u00e9tenteur de la carte","Name on card":"Nom du d\u00e9tenteur","tel":"Tel","Total to pay":"Total \u00e0 payer","Validate payment":"Valider le paiement","Expiration date invalid":"Date expiration carte non valide","Invalid card number":"Num\u00e9ro de carte non valide.","onlyUsersCanUploadPhotos":"Seuls ceux s'\u00e9tant inscrit via Google Connect ou ayant souscrit au mode sans pub peuvent envoyer des photos et activer leur webcam","onlyUsersCanRecordVoice":"Seuls ceux s'\u00e9tant inscrit via Google Connect ou ayant souscrit au mode sans pub laisser un message audio","onlyUsersCanStream":"Seuls ceux s'\u00e9tant inscrit via Google Connect ou ayant souscrit au mode sans pub diffuser leur cam","Save":"Sauvegarder","Private delete all days":"<b>Non premium<\/b>: priv\u00e9s effac\u00e9s tous les jours","Welcome to chat registered":"Bienvenue sur le chat. Un email pour certifier votre compte vous a \u00e9t\u00e9 envoy\u00e9. V\u00e9rifiez votre bo\u00eette de r\u00e9ception d'email.","Select reason":"Choisir la raison","Username must be 3\u201325 characters, letters and numbers only":"Pseudo invalide: utilisez des lettres et chiffres uniquement sans accent"};
		var forbidden_word_client=[{"word":"(?:\\+33\\s?|0)[1-9](?:[\\s.-]?\\d{2}){4}"},{"word":"(?:\\+?33|0)(?:[\\s.,;:()\/_-]|x+)*([1-9](?:(?:[\\s.,;:()\/_-]|x+)*\\d){8})"},{"word":"(?:^|\\s)\\d{1,5}\\s?(\u20ac|euros)(?=\\s|\\.|,|$)"},{"word":"discord"},{"word":"discord\\.gg"},{"word":"eos\\.to"},{"word":"facebook"},{"word":"google\\s?chat"},{"word":"insta(gram)?"},{"word":"je vend"},{"word":"ma soeur"},{"word":"Mon Snap"},{"word":"paypal"},{"word":"sn@p"},{"word":"snap"},{"word":"snap(chat)?"},{"word":"sortirecesoir\\.fr"},{"word":"s[\\s\\W_]*n[\\s\\W_]*a[\\s\\W_]*p(?:[\\s\\W_]*c[\\s\\W_]*h[\\s\\W_]*a[\\s\\W_]*t)?"},{"word":"t(?:\\.|\\s)*e(?:\\.|\\s)*l(?:\\.|\\s)*e(?:\\.|\\s)*g(?:\\.|\\s)*r(?:\\.|\\s)*a(?:\\.|\\s)*m"},{"word":"ta chienne"},{"word":"teleguard"},{"word":"tiktok"},{"word":"t\\.me"},{"word":"unlockt\\.me"},{"word":"wa\\.me"},{"word":"whatsapp"},{"word":"whtap"},{"word":"zangi"},{"word":"[\\w.-]+@[\\w.-]+\\.[a-zA-Z]{2,}"},{"word":"\\b(1h|30min|1h30|nuit|journ\u00e9e compl\u00e8te)\\s?\\d{1,4}\\s?\u20ac\\b"},{"word":"\\b(69|anal|fellation|branlette|massage \u00e9rotique)\\b"},{"word":"\\b(cam\\s?\\d{1,4}\u20ac|show\\s?cam)\\b"},{"word":"\\b(escort|prostitu\u00e9e|services?\\s?(sexuels|\u00e9rotiques))\\b"},{"word":"\\b(je me d\u00e9place|j.?encaisse|je suis chaude)\\b"},{"word":"\\b(je suis c\u00e9libataire|discret plan|je suis libre \u00e0 toi)\\b"},{"word":"\\b(je suis|je fais tout|dispo)\\s?(cam|nude)\\b"},{"word":"\\b(prix|tarif|forfait|prestation)\\b"},{"word":"\\b(tout devient possible|tu peux me joindre|viens on discute)\\b"},{"word":"\\b(viens me parler|\u00e9cris-moi|dispo maintenant|rejoins-moi)\\b"},{"word":"\\bplan\\s?(cul|sexe|coquin|coquine)\\b"}];
		var myUser;
		function startChat() {
			$('#seo').remove();
			$.post('/ajax', {a: 'getMyUser'}, (res) => {
				if (!res) window.location = 'https://bounty.chat';
				res = JSON.parse(res);
				myUser = res.user;
				let roles = {"admin":{"id":5,"webmaster_id":1,"role":"admin","can_kick":1,"can_ban":1,"can_stream":1,"can_see_user_list":1,"can_private":1,"can_access_admin":1,"can_send_message_immediatly":1,"can_send_tips":1,"can_ask_private_show":1,"can_spin_wheel":1,"can_alert":1,"can_delete_message":1,"can_be_banned":0,"can_be_kicked":0,"access_config":"index,users,config,rooms,roles,forbiddenWords,forbiddenWordsUsernames,signalements,bans,messages,bubble_color,photo_explorer,subscriber,purchase,connexion,csam,csam_xcrud,connexion2","max_create_room":99,"can_upload":1,"can_gif":1,"can_friends":1,"can_upload_gallery":1,"can_watch_cam":1,"can_record_voice":1,"can_reaction":1,"can_join_crowded_room":1,"can_download":1,"displayed_on_top":1,"display_ads":0,"canPaste":1,"can_send_public":1},"guest":{"id":6,"webmaster_id":1,"role":"guest","can_kick":0,"can_ban":0,"can_stream":0,"can_see_user_list":0,"can_private":0,"can_access_admin":0,"can_send_message_immediatly":0,"can_send_tips":0,"can_ask_private_show":0,"can_spin_wheel":0,"can_alert":0,"can_delete_message":0,"can_be_banned":1,"can_be_kicked":1,"access_config":"","max_create_room":0,"can_upload":0,"can_gif":0,"can_friends":0,"can_upload_gallery":0,"can_watch_cam":0,"can_record_voice":0,"can_reaction":0,"can_join_crowded_room":0,"can_download":0,"displayed_on_top":0,"display_ads":1,"canPaste":0,"can_send_public":0},"user":{"id":7,"webmaster_id":1,"role":"user","can_kick":0,"can_ban":0,"can_stream":0,"can_see_user_list":1,"can_private":1,"can_access_admin":0,"can_send_message_immediatly":1,"can_send_tips":1,"can_ask_private_show":1,"can_spin_wheel":1,"can_alert":0,"can_delete_message":0,"can_be_banned":1,"can_be_kicked":1,"access_config":"","max_create_room":0,"can_upload":0,"can_gif":1,"can_friends":1,"can_upload_gallery":0,"can_watch_cam":0,"can_record_voice":0,"can_reaction":1,"can_join_crowded_room":0,"can_download":0,"displayed_on_top":0,"display_ads":1,"canPaste":0,"can_send_public":1},"moderator":{"id":8,"webmaster_id":1,"role":"moderator","can_kick":1,"can_ban":1,"can_stream":1,"can_see_user_list":1,"can_private":1,"can_access_admin":1,"can_send_message_immediatly":1,"can_send_tips":1,"can_ask_private_show":1,"can_spin_wheel":1,"can_alert":1,"can_delete_message":1,"can_be_banned":0,"can_be_kicked":0,"access_config":"index,signalements,bans,messages,photo_explorer","max_create_room":1,"can_upload":1,"can_gif":1,"can_friends":1,"can_upload_gallery":1,"can_watch_cam":1,"can_record_voice":1,"can_reaction":1,"can_join_crowded_room":1,"can_download":1,"displayed_on_top":1,"display_ads":0,"canPaste":1,"can_send_public":1},"user_verified":{"id":9,"webmaster_id":1,"role":"user_verified","can_kick":0,"can_ban":0,"can_stream":1,"can_see_user_list":1,"can_private":1,"can_access_admin":0,"can_send_message_immediatly":1,"can_send_tips":1,"can_ask_private_show":1,"can_spin_wheel":1,"can_alert":0,"can_delete_message":0,"can_be_banned":1,"can_be_kicked":1,"access_config":"","max_create_room":0,"can_upload":1,"can_gif":1,"can_friends":1,"can_upload_gallery":1,"can_watch_cam":1,"can_record_voice":1,"can_reaction":1,"can_join_crowded_room":0,"can_download":0,"displayed_on_top":0,"display_ads":1,"canPaste":1,"can_send_public":1},"user_premium":{"id":10,"webmaster_id":1,"role":"user_premium","can_kick":0,"can_ban":0,"can_stream":1,"can_see_user_list":1,"can_private":1,"can_access_admin":0,"can_send_message_immediatly":1,"can_send_tips":1,"can_ask_private_show":1,"can_spin_wheel":1,"can_alert":0,"can_delete_message":0,"can_be_banned":1,"can_be_kicked":1,"access_config":"","max_create_room":1,"can_upload":1,"can_gif":1,"can_friends":1,"can_upload_gallery":1,"can_watch_cam":1,"can_record_voice":1,"can_reaction":1,"can_join_crowded_room":1,"can_download":1,"displayed_on_top":1,"display_ads":0,"canPaste":1,"can_send_public":1},"police":{"id":11,"webmaster_id":1,"role":"police","can_kick":0,"can_ban":0,"can_stream":1,"can_see_user_list":1,"can_private":1,"can_access_admin":1,"can_send_message_immediatly":1,"can_send_tips":1,"can_ask_private_show":1,"can_spin_wheel":1,"can_alert":0,"can_delete_message":0,"can_be_banned":0,"can_be_kicked":0,"access_config":"index,users,config,rooms,roles,forbiddenWords,forbiddenWordsUsernames,signalements,bans,messages,bubble_color,photo_explorer","max_create_room":0,"can_upload":1,"can_gif":1,"can_friends":1,"can_upload_gallery":1,"can_watch_cam":1,"can_record_voice":1,"can_reaction":1,"can_join_crowded_room":1,"can_download":1,"displayed_on_top":1,"display_ads":1,"canPaste":0,"can_send_public":1},"moderator_local":{"id":14,"webmaster_id":1,"role":"moderator_local","can_kick":1,"can_ban":1,"can_stream":1,"can_see_user_list":1,"can_private":1,"can_access_admin":1,"can_send_message_immediatly":1,"can_send_tips":1,"can_ask_private_show":1,"can_spin_wheel":1,"can_alert":1,"can_delete_message":1,"can_be_banned":0,"can_be_kicked":0,"access_config":"","max_create_room":1,"can_upload":1,"can_gif":1,"can_friends":1,"can_upload_gallery":1,"can_watch_cam":1,"can_record_voice":1,"can_reaction":1,"can_join_crowded_room":1,"can_download":1,"displayed_on_top":1,"display_ads":0,"canPaste":1,"can_send_public":1}};
				let config = {"id":1,"webmaster_id":1,"message_connection":"","message_premium":"<p>Pour pouvoir envoyer des messages, photos et faire des cams, vous devez vous inscrire via Google connect (via votre compte Google) ou alors vous abonner en cliquant sur &quot;mode sans pub&quot;.<\/p>","username_pattern":"[A-Za-z0-9]{3,15}","forbidden_word_action":"ban_1_minute","forbid_vpn":0,"ban_duration_hours":1,"cgu":"<h2>Mentions l&eacute;gales<\/h2>\n\n<p><strong>&Eacute;diteur du service<\/strong><br \/>\nSIRET : 44053778500014<br \/>\nEmail de contact : bounty.chatt@gmail.com<br \/>\nT&eacute;l&eacute;phone : 06.83.23.81.36<\/p>\n\n<p><strong>Directeur&middot;rice de la publication<\/strong> : ANFOSSO<\/p>\n\n<p><strong>H&eacute;bergement<\/strong><br \/>\nH&eacute;bergeur : OVHcloud<br \/>\nAdresse : 2 rue Kellermann, 59100 Roubaix, France<br \/>\nT&eacute;l&eacute;phone : +33 (0)9 72 10 10 07<\/p>\n\n<p><strong>Propri&eacute;t&eacute; intellectuelle<\/strong><br \/>\nLes &eacute;l&eacute;ments composant Bounty (marques, logos, textes, graphismes, logiciels, etc.) sont prot&eacute;g&eacute;s. Toute reproduction non autoris&eacute;e est interdite.<\/p>\n\n<p><strong>Signalement de contenus illicites<\/strong><br \/>\nVoir la page &laquo;&nbsp;Proc&eacute;dure de signalement&nbsp;&raquo;.<\/p>\n\n<p><strong>Contact autorit&eacute;s \/ judiciaire<\/strong><br \/>\nPoint de contact autorit&eacute;s (DSA\/ARCOM\/CNIL) : bounty.chatt@gmail.com<br \/>\nR&eacute;quisitions judiciaires : bounty.chatt@gmail.com<\/p>\n\n<hr \/>\n<h2>Conditions G&eacute;n&eacute;rales d&rsquo;Utilisation et de Vente (CGU\/CGV)<\/h2>\n\n<blockquote>\n<p><strong>Derni&egrave;re mise &agrave; jour :<\/strong>&nbsp;23\/08\/2025<\/p>\n<\/blockquote>\n\n<h3>1. Objet<\/h3>\n\n<p>Les pr&eacute;sentes CGU\/CGV encadrent l&rsquo;acc&egrave;s et l&rsquo;utilisation du tchat &laquo;&nbsp;Bounty&nbsp;&raquo; (le &laquo;&nbsp;Service&nbsp;&raquo;), y compris ses fonctionnalit&eacute;s gratuites et payantes (&laquo;&nbsp;Premium&nbsp;&raquo;), ainsi que les conditions commerciales applicables.<\/p>\n\n<h3>2. Public vis&eacute; &ndash; Majeurs uniquement<\/h3>\n\n<p>Le Service est strictement r&eacute;serv&eacute; aux personnes &acirc;g&eacute;es de <strong>18 ans et plus<\/strong>. Un <strong>contr&ocirc;le d&rsquo;&acirc;ge<\/strong> conforme au r&eacute;f&eacute;rentiel ARCOM est requis avant tout acc&egrave;s &agrave; du contenu explicite. Toute tentative d&rsquo;acc&egrave;s par un mineur est interdite.<\/p>\n\n<h3>3. Compte, acc&egrave;s invit&eacute; et v&eacute;rification d&rsquo;&acirc;ge<\/h3>\n\n<ul>\n <li>\n <p>Acc&egrave;s <strong>invit&eacute;<\/strong> possible avec fonctionnalit&eacute;s limit&eacute;es.<\/p>\n <\/li>\n <li>\n <p>L&rsquo;acc&egrave;s aux salons, intitul&eacute;s, vignettes ou m&eacute;dias explicites n&rsquo;est <strong>pas<\/strong> autoris&eacute; avant v&eacute;rification d&rsquo;&acirc;ge.<\/p>\n <\/li>\n <li>\n <p>Bounty utilisera le prestataire <strong>AgeVerif<\/strong> (https:\/\/www.ageverif.com\/fr), en cours d&rsquo;int&eacute;gration, pour assurer le contr&ocirc;le conforme.<\/p>\n <\/li>\n<\/ul>\n\n<h3>4. Contenus interdits et mod&eacute;ration<\/h3>\n\n<p>Sont strictement interdits : contenus impliquant des <strong>mineurs<\/strong>, toute forme d&rsquo;exploitation sexuelle, la diffusion d&rsquo;images intimes <strong>sans consentement<\/strong>, l&rsquo;incitation &agrave; commettre des infractions, les contenus haineux, violents, terroristes, la bestialit&eacute;, etc.<br \/>\nBounty applique une <strong>tol&eacute;rance z&eacute;ro<\/strong> pour les contenus relatifs aux mineurs (CSAM)&nbsp;: suppression imm&eacute;diate, signalement aux autorit&eacute;s, bannissement.<br \/>\nEn cas de <strong>notification<\/strong> valable d&rsquo;un contenu manifestement illicite, Bounty agit <strong>promptement<\/strong> (retrait ou blocage) et peut <strong>notifier les autorit&eacute;s<\/strong>.<\/p>\n\n<h3>5. Fonctionnalit&eacute;s Premium et conditions commerciales (CGV)<\/h3>\n\n<p><strong>Premium<\/strong> inclut&nbsp;: suppression de la publicit&eacute;, cr&eacute;ation de salons personnels, messages enregistr&eacute;s, liste d&rsquo;amis, copier\/coller.<\/p>\n\n<ul>\n <li>\n <p><strong>Prix TTC<\/strong>&nbsp;: 1 mois = 5,95&euro; \/ 6 mois = 29,00&euro; \/ 12 mois = 49,00&euro;.<\/p>\n <\/li>\n <li>\n <p><strong>Paiement<\/strong> : par carte bancaire via <strong>Stripe<\/strong>.<\/p>\n <\/li>\n <li>\n <p><strong>Renouvellement<\/strong> : pas de renouvellement automatique ; r&eacute;siliation effective &agrave; l&rsquo;&eacute;ch&eacute;ance en cours.<\/p>\n <\/li>\n <li>\n <p><strong>Disponibilit&eacute;<\/strong> : abonnement accessible depuis tous les pays.<\/p>\n <\/li>\n <li>\n <p><strong>Ex&eacute;cution imm&eacute;diate<\/strong> : l&rsquo;utilisateur consent &agrave; ce que l&rsquo;acc&egrave;s Premium commence imm&eacute;diatement, et reconna&icirc;t par cons&eacute;quent <strong>perdre son droit de r&eacute;tractation<\/strong> (art. L221\u201128 Code conso).<\/p>\n <\/li>\n <li>\n <p><strong>Service client<\/strong> : bounty.chatt@gmail.com<\/p>\n <\/li>\n <li>\n <p><strong>M&eacute;diation<\/strong> : Bounty a d&eacute;sign&eacute; le <strong>m&eacute;diateur de la consommation CM2C<\/strong> (Centre de la M&eacute;diation de la Consommation de Conciliateurs de Justice &ndash; cm2c.net). L&rsquo;utilisateur peut aussi recourir &agrave; la Plateforme europ&eacute;enne de r&egrave;glement en ligne des litiges (ODR) : https:\/\/ec.europa.eu\/odr<\/p>\n <\/li>\n<\/ul>\n\n<h3>6. Publicit&eacute; adulte et partenariats<\/h3>\n\n<p>Le Service affiche de la <strong>publicit&eacute; adulte<\/strong> (y compris formats intrusifs type pop\u2011under). Ces formats ne sont activ&eacute;s <strong>qu&rsquo;avec consentement<\/strong> aux cookies\/traceurs correspondant. La gestion des campagnes est confi&eacute;e &agrave; la plateforme <strong>Adspyglass<\/strong>. La <strong>liste des partenaires publicitaires<\/strong> et les finalit&eacute;s de traitement sont indiqu&eacute;es dans la Politique Cookies.<\/p>\n\n<h3>7. Licence et responsabilit&eacute;s utilisateur<\/h3>\n\n<p>L&rsquo;utilisateur conserve les droits sur ses contenus et conc&egrave;de &agrave; Bounty, aux seules fins techniques d&rsquo;h&eacute;bergement\/diffusion sur le Service, une <strong>licence mondiale, non exclusive, gratuite<\/strong>, pour la dur&eacute;e l&eacute;gale n&eacute;cessaire. L&rsquo;utilisateur <strong>garantit<\/strong> d&eacute;tenir tous droits et <strong>consentements<\/strong> n&eacute;cessaires (y compris des personnes repr&eacute;sent&eacute;es) et s&rsquo;interdit d&rsquo;uploader des contenus de tiers sans autorisation.<\/p>\n\n<h3>8. Proc&eacute;dure de signalement &ndash; Notice &amp; Action (DSA)<\/h3>\n\n<p>Un bouton <strong>Signaler<\/strong> est disponible sur chaque message\/photo\/salon. Le formulaire recueille&nbsp;: <strong>URL\/ID du contenu<\/strong>, cat&eacute;gorie de l&rsquo;illicite, description, et (facultatif) email du plaignant.<br \/>\nChaque retrait\/restriction fait l&rsquo;objet d&rsquo;une <strong>d&eacute;cision motiv&eacute;e<\/strong> notifi&eacute;e &agrave; l&rsquo;&eacute;metteur. Un <strong>recours interne<\/strong> est disponible pendant 6 mois.<\/p>\n\n<h3>9. Journalisation et conservation l&eacute;gale<\/h3>\n\n<p>Bounty conserve, au titre de ses obligations l&eacute;gales, les <strong>donn&eacute;es d&rsquo;identification<\/strong> des contributeurs (ex. IP source, port, identifiants techniques, ID de contenu\/op&eacute;ration et horodatages) pour une dur&eacute;e de <strong>1 an<\/strong>. Voir la Politique de confidentialit&eacute;.<\/p>\n\n<h3>10. S&eacute;curit&eacute; et abus<\/h3>\n\n<p>Mesures de s&eacute;curit&eacute; adapt&eacute;es (chiffrement, contr&ocirc;le d&rsquo;acc&egrave;s). En cas d&rsquo;abus r&eacute;p&eacute;t&eacute; (ex. CSAM, revenge porn, harc&egrave;lement), Bounty peut <strong>suspendre<\/strong> ou <strong>r&eacute;silier<\/strong> l&rsquo;acc&egrave;s.<\/p>\n\n<h3>11. &Eacute;volution du Service et des CGU\/CGV<\/h3>\n\n<p>Bounty peut modifier le Service ou les pr&eacute;sentes. Les changements substantiels sont notifi&eacute;s aux utilisateurs <strong>au moins 15 jours<\/strong> avant leur prise d&rsquo;effet.<\/p>\n\n<h3>12. Droit applicable &ndash; Juridiction<\/h3>\n\n<p>Droit fran&ccedil;ais. Comp&eacute;tence des tribunaux du ressort du si&egrave;ge de Bounty (sous r&eacute;serve des r&egrave;gles imp&eacute;ratives applicables au consommateur).<\/p>","confidentiality":"<h2>Politique de confidentialit&eacute; &amp; Cookies<\/h2>\n\n<blockquote>\n<p><strong>Derni&egrave;re mise &agrave; jour :<\/strong>&nbsp;23\/08\/2025&nbsp;&ndash; Responsable du traitement&nbsp;: ANFOSSO G&eacute;rald<br \/>\nContact vie priv&eacute;e&nbsp;: bounty.chatt@gmail.com<br \/>\nDPO&nbsp;: aucun d&eacute;sign&eacute; &agrave; ce jour<\/p>\n<\/blockquote>\n\n<h3>1. Donn&eacute;es que nous traitons<\/h3>\n\n<ul>\n <li>\n <p><strong>Donn&eacute;es techniques et d&rsquo;identification<\/strong> : IP source, <strong>port<\/strong>, identifiants de session\/compte, empreintes techniques limit&eacute;es, IDs de contenus\/op&eacute;rations, horodatages.<\/p>\n <\/li>\n <li>\n <p><strong>Contenus<\/strong> : messages\/photos &eacute;chang&eacute;s ; <strong>stockage minimal<\/strong> (pas d&rsquo;archive des messages, sauf &laquo;&nbsp;messages enregistr&eacute;s&nbsp;&raquo; pour Premium &agrave; l&rsquo;initiative de l&rsquo;utilisateur).<\/p>\n <\/li>\n <li>\n <p><strong>V&eacute;rification d&rsquo;&acirc;ge<\/strong> : donn&eacute;es limit&eacute;es selon la m&eacute;thode choisie ; via prestataire <strong>AgeVerif<\/strong>, en mode double anonymat quand disponible.<\/p>\n <\/li>\n <li>\n <p><strong>Paiement<\/strong> : coordonn&eacute;es et tokens de paiement g&eacute;r&eacute;s par Stripe ; Bounty ne stocke pas les num&eacute;ros de carte.<\/p>\n <\/li>\n <li>\n <p><strong>Cookies\/traceurs<\/strong> : mesure d&rsquo;audience, personnalisation, publicit&eacute; adulte (Adspyglass et ses r&eacute;gies partenaires).<\/p>\n <\/li>\n<\/ul>\n\n<h3>2. Finalit&eacute;s et bases l&eacute;gales<\/h3>\n\n<ul>\n <li>\n <p><strong>Fournir le Service<\/strong> (messagerie, salons, Premium)&nbsp;: <strong>contrat<\/strong>.<\/p>\n <\/li>\n <li>\n <p><strong>Mod&eacute;ration\/anti\u2011abus, s&eacute;curit&eacute;, lutte contre les contenus illicites<\/strong>&nbsp;: <strong>int&eacute;r&ecirc;t l&eacute;gitime<\/strong> et <strong>obligation l&eacute;gale<\/strong>.<\/p>\n <\/li>\n <li>\n <p><strong>Conservation l&eacute;gale des donn&eacute;es d&rsquo;identification<\/strong>&nbsp;: <strong>obligation l&eacute;gale<\/strong> (1&nbsp;an).<\/p>\n <\/li>\n <li>\n <p><strong>Publicit&eacute; et mesure d&rsquo;audience<\/strong>&nbsp;: <strong>consentement<\/strong>.<\/p>\n <\/li>\n <li>\n <p><strong>Photos &agrave; caract&egrave;re sexuel<\/strong> pouvant r&eacute;v&eacute;ler la <strong>vie sexuelle<\/strong> : <strong>consentement explicite<\/strong> de l&rsquo;uploadeur ; interdiction de publier des tiers sans consentement.<\/p>\n <\/li>\n<\/ul>\n\n<h3>3. Dur&eacute;es de conservation<\/h3>\n\n<ul>\n <li>\n <p><strong>Donn&eacute;es d&rsquo;identification\/connexion et op&eacute;rations li&eacute;es aux contenus<\/strong>&nbsp;: <strong>1 an<\/strong>.<\/p>\n <\/li>\n <li>\n <p><strong>Comptes<\/strong> : pendant l&rsquo;usage + [x] mois apr&egrave;s inactivit&eacute; ; donn&eacute;es contractuelles\/facturation&nbsp;: <strong>10 ans<\/strong> (obligations comptables).<\/p>\n <\/li>\n <li>\n <p><strong>Cookies publicitaires<\/strong> : selon la dur&eacute;e indiqu&eacute;e par les partenaires Adspyglass, maximum celle accept&eacute;e via la CMP.<\/p>\n <\/li>\n <li>\n <p><strong>Messages enregistr&eacute;s (Premium)<\/strong> : jusqu&rsquo;&agrave; suppression par l&rsquo;utilisateur.<\/p>\n <\/li>\n<\/ul>\n\n<h3>4. Destinataires<\/h3>\n\n<p>Prestataires techniques (h&eacute;bergement, anti\u2011abus, v&eacute;rification d&rsquo;&acirc;ge, paiement Stripe, publicit&eacute; via Adspyglass et ses r&eacute;gies, analytics), autorit&eacute;s comp&eacute;tentes sur r&eacute;quisition l&eacute;gale.<\/p>\n\n<h3>5. Transferts hors UE<\/h3>\n\n<p>Encadr&eacute;s par <strong>clauses contractuelles types<\/strong> (SCC) et mesures additionnelles si n&eacute;cessaire. La CMP liste les partenaires et pays.<\/p>\n\n<h3>6. Droits des personnes<\/h3>\n\n<p>Acc&egrave;s, rectification, effacement, opposition, limitation, portabilit&eacute;, directives post\u2011mortem. Exercice : bounty.chatt@gmail.com. R&eacute;clamation : <strong>CNIL<\/strong> (cnil.fr).<\/p>\n\n<h3>7. S&eacute;curit&eacute;<\/h3>\n\n<p>Mesures techniques et organisationnelles&nbsp;: chiffrement au repos et en transit, cloisonnement, contr&ocirc;le d&rsquo;acc&egrave;s strict, journalisation, sauvegardes, tests de s&eacute;curit&eacute; r&eacute;guliers.<\/p>\n\n<h3>8. Cookies &amp; traceurs<\/h3>\n\n<p>Banni&egrave;re conforme&nbsp;: <strong>Refuser aussi facilement qu&rsquo;Accepter<\/strong>.<\/p>\n\n<ul>\n <li>\n <p><strong>Exempt&eacute;s<\/strong> : fonctionnement, s&eacute;curit&eacute;, mesure d&rsquo;audience <strong>exempt&eacute;e<\/strong> (si param&eacute;tr&eacute;e strictement).<\/p>\n <\/li>\n <li>\n <p><strong>Soumis &agrave; consentement<\/strong> : personnalisation, publicit&eacute; adulte, profilage via Adspyglass.<br \/>\n Un <strong>Centre de pr&eacute;f&eacute;rences<\/strong> permet de modifier le choix &agrave; tout moment. La <strong>liste des partenaires<\/strong> (vendors) est accessible et tenue &agrave; jour.<\/p>\n <\/li>\n<\/ul>\n\n<h3>9. V&eacute;rification d&rsquo;&acirc;ge<\/h3>\n\n<p>Le contr&ocirc;le d&rsquo;&acirc;ge est requis pour acc&eacute;der &agrave; tout <strong>contenu explicite<\/strong>. Bounty mettra en &oelig;uvre la solution <strong>AgeVerif<\/strong>, conforme au r&eacute;f&eacute;rentiel ARCOM. Aucune preuve d&rsquo;identit&eacute; n&rsquo;est conserv&eacute;e par Bounty ; seul un <strong>jeton de majorit&eacute;<\/strong> non tra&ccedil;ant est stock&eacute; localement pour la session.<\/p>\n\n<h3>10. AIPD (DPIA) et registre des traitements<\/h3>\n\n<p>Bounty r&eacute;alise une AIPD pour les traitements &agrave; <strong>risque &eacute;lev&eacute;<\/strong> (ex. v&eacute;rification d&rsquo;&acirc;ge, anti\u2011abus, contenus sensibles) et tient un <strong>registre<\/strong> (art.&nbsp;30 RGPD).<\/p>\n\n<hr \/>\n<h2>Proc&eacute;dure de signalement &amp; Transparence DSA<\/h2>\n\n<h3>1. Signaler un contenu<\/h3>\n\n<p>Un bouton <strong>Signaler<\/strong> est pr&eacute;sent sur chaque message\/photo\/salon. Le formulaire demande&nbsp;: URL\/ID du contenu, motif, d&eacute;claration de bonne foi, et (facultatif) email. Les non\u2011inscrits peuvent aussi signaler via un formulaire public.<\/p>\n\n<h3>2. Traitement et d&eacute;lais<\/h3>\n\n<ul>\n <li>\n <p><strong>CSAM\/menaces graves<\/strong> : traitement prioritaire, retrait\/blocage imm&eacute;diat et <strong>notification aux autorit&eacute;s<\/strong> comp&eacute;tentes.<\/p>\n <\/li>\n <li>\n <p>Autres signalements&nbsp;: examen sous 24&ndash;72h ouvr&eacute;es ; demandes d&rsquo;informations compl&eacute;mentaires possibles.<\/p>\n <\/li>\n<\/ul>\n\n<h3>3. D&eacute;cision motiv&eacute;e &amp; recours<\/h3>\n\n<p>Pour chaque retrait\/restriction, l&rsquo;&eacute;metteur re&ccedil;oit une <strong>d&eacute;cision motiv&eacute;e<\/strong>. Un <strong>recours interne<\/strong> est possible sous 6 mois.<\/p>\n\n<h3>4. Points de contact<\/h3>\n\n<ul>\n <li>\n <p><strong>Autorit&eacute;s\/ARCOM\/CNIL<\/strong> : bounty.chatt@gmail.com<\/p>\n <\/li>\n <li>\n <p><strong>Usagers<\/strong> : bounty.chatt@gmail.com<\/p>\n <\/li>\n<\/ul>\n\n<h3>5. Rapport de transparence<\/h3>\n\n<p>Bounty publie <strong>au moins annuellement<\/strong> des statistiques agr&eacute;g&eacute;es (nombre de signalements, d&eacute;lais moyens, retraits, suspensions, recours, demandes des autorit&eacute;s).<\/p>\n\n<hr \/>\n<h2>V&eacute;rification d&rsquo;&acirc;ge &ndash; Informations<\/h2>\n\n<h3>Pourquoi&nbsp;?<\/h3>\n\n<p>Emp&ecirc;cher l&rsquo;acc&egrave;s des <strong>mineurs<\/strong> &agrave; tout contenu <strong>pornographique<\/strong> et se conformer au droit fran&ccedil;ais.<\/p>\n\n<h3>Comment&nbsp;?<\/h3>\n\n<p>M&eacute;thodes conformes au <strong>r&eacute;f&eacute;rentiel ARCOM<\/strong> (au moins une m&eacute;thode en <strong>double anonymat<\/strong>) via le prestataire <strong>AgeVerif<\/strong>. Aucune preuve d&rsquo;identit&eacute; n&rsquo;est conserv&eacute;e par Bounty ; seul un <strong>jeton de majorit&eacute;<\/strong> non tra&ccedil;ant est stock&eacute; localement pour la session.<\/p>\n\n<h3>Quand&nbsp;?<\/h3>\n\n<p><strong>Avant<\/strong> l&rsquo;affichage de tout contenu, intitul&eacute; de salon, vignette ou m&eacute;dia &agrave; caract&egrave;re sexuel.<\/p>","fb":"https:\/\/x.com\/bounty_chat","twitter":"https:\/\/www.facebook.com\/profile.php?id=61565665954497","show_labels_menu":0,"giphyApiKey":"7QcshDMAzYGjznmEI5NwQoYVxGj3fYFe","website_uploads":"https:\/\/gallery.bounty.chat","display_people_in_dep":1,"url":"https:\/\/sockets.bounty.chat","postal_code_use":0,"payment_type":"stripe","clickadu":0.1,"rate_reward":0.75};
				myUser.initial_room_id = 0;
				chat.connectToChat(myUser, res.jwt, roles, config);
			});
		}
	</script>
		<div id="image-modal" style="display: none;">
			<!--<button id="prev-image" class="arrow">❮</button>-->
			<div class="modal-content">
				<img id="modal-image" src="" alt="Image Preview">
			</div>
			<!--<button id="next-image" class="arrow">❯</button>-->
		</div>
		<div id="video-modal" style="display: none;">
			<div class="modal-content">
				<video id="modal-video" controls src="">
			</div>
		</div>
		<div id="audio-modal" style="display: none;">
			<div class="modal-content">
				<audio id="audioPlayer" controls>
					<source src="" type="audio/wav">
				</audio>
			</div>
		</div>
	</div>
	<div class="emoji-picker" id="emoji-picker">
		<div id="emojiContainer">
			<button>😀</button>
<button>😁</button>
<button>😂</button>
<button>😃</button>
<button>😄</button>
<button>😅</button>
<button>😆</button>
<button>😉</button>
<button>😊</button>
<button>😋</button>
<button>😎</button>
<button>😍</button>
<button>😘</button>
<button>😗</button>
<button>😙</button>
<button>😚</button>
<button>🙂</button>
<button>🤗</button>
<button>🤩</button>
<button>🤔</button>
<button>🤨</button>
<button>😐</button>
<button>😑</button>
<button>😶</button>
<button>🙄</button>
<button>😏</button>
<button>😣</button>
<button>😥</button>
<button>😮</button>
<button>🤐</button>
<button>😯</button>
<button>😪</button>
<button>😫</button>
<button>😴</button>
<button>😌</button>
<button>😛</button>
<button>😜</button>
<button>😝</button>
<button>🤤</button>
<button>😒</button>
<button>😓</button>
<button>😔</button>
<button>😕</button>
<button>🙃</button>
<button>🤑</button>
<button>😲</button>
<button>🙁</button>
<button>😖</button>
<button>😞</button>
<button>😟</button>
<button>😤</button>
<button>😢</button>
<button>😭</button>
<button>😦</button>
<button>😧</button>
<button>😨</button>
<button>😩</button>
<button>🤯</button>
<button>😬</button>
<button>😰</button>
<button>😱</button>
<button>🥵</button>
<button>🥶</button>
<button>😳</button>
<button>🤪</button>
<button>😵</button>
<button>😡</button>
<button>😠</button>
<button>🤬</button>
<button>😷</button>
<button>🤒</button>
<button>🤕</button>
<button>🤢</button>
<button>🤮</button>
<button>🤧</button>
<button>😇</button>
<button>🥳</button>
<button>🥺</button>
<button>🤠</button>
<button>🥴</button>
<button>🥵</button>
<button>🥶</button>
<button>🥱</button>
<button>😎</button>		</div>
		<div class="emoji-search width100">
			<input type="text" id="gif-search" placeholder="Chercher Gif" />
		</div>
		<div id="giphy-results" class="giphy-results d-flex flex-wrap mt-1 gap-1">
		</div>
	</div>

	<foreignObject><script src="/cdn-cgi/scripts/7d0fa10a/cloudflare-static/rocket-loader.min.js" data-cf-settings="43c79d86114da1905a54a368-|49" defer></script></foreignObject><script defer src="https://static.cloudflareinsights.com/beacon.min.js/vcd15cbe7772f49c399c6a5babf22c1241717689176015" integrity="sha512-ZpsOmlRQV6y907TI0dKBHq9Md29nnaEIPlkf84rnaERnq6zvWvPUqr2ft8M1aS28oN72PdrCzSjY4U6VaAw1EQ==" data-cf-beacon='{"version":"2024.11.0","token":"08c0b2bee2d14c799c778072d64b2d22","r":1,"server_timing":{"name":{"cfCacheStatus":true,"cfEdge":true,"cfExtPri":true,"cfL4":true,"cfOrigin":true,"cfSpeedBrain":true},"location_startswith":null}}' crossorigin="anonymous"></script>
</body>
	<script src="/assets/js/socket.io.min.js" type="43c79d86114da1905a54a368-text/javascript"></script>
	<script src="/assets/js/bootstrap.bundle.min.js" type="43c79d86114da1905a54a368-text/javascript"></script>
	<script src="/assets/js/bootstrap-toaster.min.js" type="43c79d86114da1905a54a368-text/javascript"></script>
	<script src="/assets/js/bootbox.all.min.js" type="43c79d86114da1905a54a368-text/javascript"></script>
	<script src="/assets/js/jquery-ui.min.js" type="43c79d86114da1905a54a368-text/javascript"></script>
	<script src="/assets/js/jquery.ui.touch-punch.min.js" type="43c79d86114da1905a54a368-text/javascript"></script>
	<script src="/assets/js/easy-mediasoup.bundle.min.js" type="43c79d86114da1905a54a368-text/javascript"></script>
	<script src="/assets/js/ms3.js" type="43c79d86114da1905a54a368-text/javascript"></script>
	<script src="/assets/js/Question.js" defer async type="43c79d86114da1905a54a368-text/javascript"></script>
	<script src="https://cdn.jsdelivr.net/npm/@thumbmarkjs/thumbmarkjs/dist/thumbmark.umd.js" type="43c79d86114da1905a54a368-text/javascript"></script>

			<script src="https://www.ageverif.com/checker.js?key=cknf1uXgBhy8W4QFRbVs76mxrl0Pt9IKZEND3ipG&nostart&lang=fr&onsuccess=age_verified&challenges=selfie,credit_card,ticket" async defer type="43c79d86114da1905a54a368-text/javascript"></script>
	


	
	<!-- Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-9SEJEWGXH4" type="43c79d86114da1905a54a368-text/javascript"></script>
	<script type="43c79d86114da1905a54a368-text/javascript">
		bootbox.setLocale('fr');
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		gtag('config', 'G-9SEJEWGXH4');

		if ('serviceWorker' in navigator) {
			navigator.serviceWorker.register('/service-worker.js')
				.then((registration) => {
					console.log('Service Worker registered with scope:', registration.scope);
				})
				.catch((error) => {
					console.log('Service Worker registration failed:', error);
				});
		}


		let deferredPrompt;
		if (isMobile()) {
			window.addEventListener('beforeinstallprompt', (event) => {
				event.preventDefault();
				deferredPrompt = event;
				const installButton = document.getElementById('installPWA');
				installButton.style.display = 'block';
				installButton.addEventListener('click', () => {
					deferredPrompt.prompt();
					deferredPrompt.userChoice.then((choiceResult) => {
						if (choiceResult.outcome === 'accepted') {
							console.log('PWA installed');
						} else {
							console.log('PWA installation rejected');
						}
						deferredPrompt = null;
					});
				});
			});
			window.addEventListener('appinstalled', () => {
				console.log('PWA installed successfully');
				document.getElementById('installPWA').style.display = 'none';
			});
		}
		function isMobile() {
			return /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
		}

	</script>
	<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer type="43c79d86114da1905a54a368-text/javascript"></script>
	<div id="ts-login"></div>
</html>
