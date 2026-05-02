<?php
$title = "Banque de compagnie";

/* ---Header--- */
ob_start();
?>
<div class='background-img bankBg'>
</div>
<div class="row justify-content-center">
	<div class="col mx-2 rounded bg-light py-3 bg-opacity-75">
		<nav class='mb-3'>
			<a class='btn btn-outline-primary' href='compagnie.php'>Page compagnie</a>
			<a class='btn btn-outline-secondary' href='jouer.php'>Retour au jeu</a>
		</nav>
		<h2 class='mb-3'>
			<span class='position-relative'>
				Banque de la compagnie
					<a class='text-primary btn btn-sm rounded-pill position-absolute top-0 start-100 text-nowrap ps-0' href='https://encyclopedienvs.nord-vs-sud.fr/index.php/Accueil'>
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
							<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
						</svg>
						<span class='align-middle'>Règles sur la banque (wiki)</span>
					</a>
			<span>
		</h2>
		<p class='mt-3'>
			Votre compagnie dispose de :<br>
			<span class='fw-bold'><?= $disposableIncome?></span> thune(s)
		</p>
		<h3 class='mb-1 fs-5'>Vos thunes</h3>
		<div class='row'>
			<div class='col col-md-6'>
				<ul class="list-group list-group-horizontal">
					<li class="list-group-item lh-lg">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
							<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
						</svg>
						<span class='align-middle'>Sur vous :</span><br>
						<span class='fw-bold'><?= $perso->or_perso ?></span> thune(s)
					</li>
					<li class="list-group-item lh-lg">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
							<path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
						</svg>
						<span class='align-middle'>En banque :</span><br>
						<span class='fw-bold'><?= $account->montant ?></span> thune(s)
					</li>
					<li class="list-group-item lh-lg">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="0.8" stroke="currentColor"  class="w-6 h-6">
							<path d="m15.995 2.0037c-1.2506-0.043502-2.4962 0.30932-3.5527 0.97461-1.5342 0.90807-3.1091 1.752-4.625 2.6895-0.50277 0.26098-0.95758 0.6018-1.3477 1.0117-7.823e-4 8.579e-4 -0.00118 0.00307-0.00195 0.00378-0.5926 0.62445-1.0173 1.387-1.2344 2.2187-0.047924 0.17333-0.088403 0.34844-0.11913 0.52539-5.896e-4 0.0094-0.00137 0.02003-0.00195 0.02948-0.034394 0.2383-0.050646 0.47996-0.050797 0.7207 0.00166 0.26808 0.024567 0.53434 0.068372 0.79883 0.027591 0.20931 0.07132 0.41616 0.13478 0.61914 0.019653 0.05828 0.040743 0.1163 0.062513 0.17382 0.012094 0.03213 0.021921 0.06576 0.03515 0.09766 0.00605 0.01739 0.013606 0.03326 0.019654 0.0508 0.06909 0.19109 0.15073 0.37712 0.24219 0.5586 0.065688 0.13089 0.13395 0.26022 0.21094 0.38476 0.046791 0.07238 0.094148 0.14494 0.14453 0.21483 0.078917 0.11426 0.16188 0.22462 0.25002 0.33203 0.048907 0.06028 0.098721 0.11977 0.15039 0.17775 0.12813 0.14238 0.26275 0.27723 0.40625 0.4043 0.015874 0.0155 0.032504 0.03175 0.048831 0.04687 0.16566 0.1429 0.34227 0.27046 0.52539 0.39063 0.051099 0.03213 0.10401 0.06512 0.15625 0.0957 0.15753 0.09524 0.31654 0.18678 0.48437 0.26366 0.047509 0.02117 0.09445 0.04252 0.14256 0.06251 0.023433 0.0098 0.046715 0.02003 0.070299 0.02948 0.15523 0.0675 0.31612 0.12382 0.47851 0.17382 0.024189 0.0076 0.047773 0.01587 0.072265 0.02343 0.21634 0.06285 0.43833 0.1098 0.66211 0.14256 0.01209 2e-3 0.02305 0.0038 0.03515 0.0057 0.22715 0.03175 0.45645 0.04777 0.68555 0.04883 0.0098 6.88e-4 0.01965 0.0013 0.02948 2e-3 0.0045-3.44e-4 0.0091-0.0016 0.01361-2e-3 0.23252-8.63e-4 0.46299-0.0155 0.69141-0.04687 0.0023-3.4e-4 0.0057-0.0016 0.0079-2e-3 0.03175-0.0057 0.06387-0.01323 0.0957-0.01965 0.1561-0.02646 0.30981-0.05941 0.46289-0.09963 0.09895-0.02457 0.19767-0.05333 0.29492-0.08398 0.1372-0.04452 0.27315-0.09494 0.40625-0.15039 0.11441-0.0483 0.22537-0.09974 0.33592-0.15624 0.09226-0.04634 0.18217-0.09672 0.27148-0.14842 0.09562-0.05454 0.19128-0.11131 0.2832-0.17186 0.13587-0.0909 0.26623-0.18909 0.39258-0.29295 0.06127-0.05053 0.12083-0.10292 0.17968-0.15624 0.10556-0.09585 0.20598-0.19744 0.30274-0.30274 0.107-0.11603 0.2089-0.23592 0.30467-0.36132 0.04282-0.05586 0.08648-0.11229 0.12696-0.16993 0.08221-0.11565 0.15821-0.23339 0.23048-0.35546l2e-3 -2e-3v-2e-3c0.40663-0.69276 0.6438-1.4723 0.6914-2.2734 0.0014-0.02192 0.0045-0.04422 6e-3 -0.06641 0.0076-0.07846 0.01172-0.1556 0.0155-0.23437-7.2e-5 -0.58883-0.1025-1.1745-0.30274-1.7285 0.03175-0.01701 0.06391-0.03742 0.0957-0.05665l0.02154-0.01172c0.29363-0.18108 0.58756-0.45427 0.91601-0.20118 0.71324 0.28789 1.4455 0.62358 2.2344 0.52734 0.67212 0.03326 1.3493 9e-4 1.9629-0.30467 0.53262-0.17163 0.95517-0.63157 1.4863-0.76172h1.2402c-0.06384-0.27772 0.19808-0.87849-0.29688-0.72656-0.44895 0.04131-0.94494-0.083565-1.3652 0.060548-0.81108 0.50043-1.6574 1.0785-2.6563 1.0293-0.83969 0.08152-1.6449-0.17968-2.3984-0.52344-0.30217-0.12378-0.65604-0.34439-0.93945-0.05469l-2.1855 1.2325c-0.45731 0.1584-1.0092-0.23342-0.92188-0.72656 0.0045-0.17193 0.06954-0.30497 0.16407-0.41797l3.3223-2.0098c-0.15232-0.24011-0.28823-0.82204-0.63672-0.44727l-1.7949 1.0352c-0.85943-0.63881-1.8987-0.99135-2.9707-1.0098 1.2095-0.67793 2.3739-1.4509 3.6387-2.0254 1.0861-0.41175 2.2623-0.37259 3.3926-0.19922 1.2961 0.13168 2.5959 0.3109 3.8906 0.41211 0.60012-0.14536 1.2477-0.20013 1.8184-0.40039-0.08474-0.27984-0.0034-0.86373-0.4668-0.60938-0.68046 0.1115-1.3534 0.38414-2.0469 0.2129-1.4998-0.14804-2.9966-0.36408-4.498-0.46875zm-5.6934 3.5938c0.87722 0.021543 1.739 0.30584 2.4629 0.79492l-1.0176 0.63867c-0.47589-0.23509-1.0145-0.37032-1.5488-0.37304-0.04384-1.663e-4 -0.08708 4.271e-4 -0.13085 0.00195-0.31056 0.00869-0.59731 0.058129-0.90039 0.14649-0.43141 0.12688-0.88911 0.36442-1.2539 0.66797-0.72351 0.58709-1.1785 1.4602-1.2598 2.3828-0.086967 0.91149 0.20149 1.8816 0.8125 2.5898 0.69957 0.8254 1.792 1.3168 2.8789 1.248 0.89329-0.04706 1.7754-0.43548 2.3945-1.0977 0.7517-0.79412 1.1034-1.9431 0.91211-3.0195-0.0189-0.11414-0.04702-0.22639-0.07812-0.33789l0.95508-0.53515c0.21868 0.6441 0.29643 1.3355 0.2129 2.0117-0.16025 1.4391-1.0584 2.7685-2.3301 3.4668-1.2333 0.69314-2.791 0.76453-4.084 0.1914-1.2849-0.55276-2.2729-1.7279-2.6094-3.0801-0.34345-1.3286-0.036284-2.7999 0.79883-3.8887 0.82389-1.093 2.1619-1.7697 3.5313-1.8066 0.08508-0.00288 0.16894-0.00416 0.25391-0.00195zm-0.03704 1.6836c0.40806 0.015496 0.81426 0.11482 1.1875 0.28123-0.03288 0.51663-0.01172 1.0944 0.4336 1.4414 0.27099 0.31914 0.70649 0.41839 1.1113 0.42188 0.25482 0.94138 0.01436 2.0004-0.66406 2.7129-0.67765 0.75434-1.7772 1.1289-2.7734 0.88867-0.58219-0.12181-1.1404-0.41544-1.5234-0.87695-0.47073-0.44202-0.70231-1.0982-0.77734-1.7285-0.053707-0.7378 0.17802-1.4986 0.64648-2.0664 0.32916-0.36714 0.69321-0.68706 1.166-0.85352 0.37701-0.16743 0.7853-0.23599 1.1934-0.22072zm-1.5293 1.625-0.060548 1.1035h0.21483c0.04611-0.26853 0.11482-0.47063 0.20704-0.60351 0.12748-0.19257 0.28551-0.28906 0.47266-0.28906 0.05975 0 0.09831 0.01172 0.1172 0.03326 0.0189 0.0189 0.02948 0.06425 0.02948 0.13478v1.9727c0 0.1302-0.0011 0.21041-0.0038 0.24023-0.0028 0.02683-0.01172 0.04808-0.02532 0.06444-0.0325 0.04071-0.14532 0.06055-0.33789 0.06055h-0.08791v0.22069h1.6426v-0.22069h-0.08984c-0.19257 0-0.30535-0.02003-0.33789-0.06055-0.01361-0.01625-0.02079-0.03742-0.02343-0.06444-0.0028-0.02986-0.0038-0.11006-0.0038-0.24023v-1.9727c0-0.0706 0.0083-0.11588 0.02721-0.13478 0.0189-0.02154 0.05949-0.03326 0.11913-0.03326 0.16819 0 0.31472 0.0802 0.43945 0.24023 0.10579 0.13833 0.18402 0.35671 0.2383 0.65234h0.21679l-0.06055-1.1035zm-2.6602 6.3574c-0.10677 8.58e-4 -0.21351 0.0064-0.32032 0.01361-0.94498-0.0049-1.8104 0.4179-2.584 0.92383-0.42736 0.31722-1.0005 0.10534-1.498 0.166-0.37489-0.02721-0.77252-0.07559-0.61914 0.42383-0.15447 0.51258 0.48379 0.23471 0.77539 0.30274 0.44865-0.03859 0.94058 0.08198 1.3613-0.05858 0.86203-0.51711 1.7737-1.1451 2.832-1.0234 0.63725-0.02646 1.2611 0.07431 1.8359 0.35936 0.48582 0.19294 0.9593 0.45235 1.5 0.36132 0.73698 0.01739 1.4888-0.03288 2.2168 0.02532 0.52314 0.10579 0.70543 0.79906 0.36522 1.1816-0.42421 0.41272-1.0793 0.18966-1.6172 0.24608h-2.5645c0.063799 0.27768-0.19612 0.87877 0.29881 0.72656 1.279-0.01398 2.5664 0.02457 3.8398-0.02154 0.941-0.29802 1.7308-0.92634 2.6016-1.377 0.73628-0.39076 1.419-0.89099 2.1934-1.2031 0.56655-0.01512 1.0016 0.64488 0.67188 1.1328-0.47526 0.48334-1.1644 0.71656-1.7285 1.0937-1.4252 0.78569-2.7914 1.6846-4.2617 2.3828-1.053 0.41546-2.2035 0.42719-3.3086 0.25194-1.3718-0.13395-2.7437-0.34156-4.1152-0.42969-0.97683 0.13671-1.987 0.20621-2.9434 0.38477 0.088327 0.26944-0.061153 0.86891 0.41211 0.65234 0.94733-0.08545 1.8942-0.27931 2.8418-0.29688 1.6569 0.1632 3.3115 0.40324 4.9707 0.51758 0.29204-0.01209 0.58679 0.0102 0.87695-0.02721 1.2669-0.12352 2.3866-0.755 3.4531-1.4023 1.3683-0.79913 2.7616-1.5663 4.1152-2.3848 0.41635-0.31994 0.56939-0.84601 0.50195-1.3516-0.07487-0.66372-0.70814-1.2064-1.377-1.2344-0.75552-0.0102-1.3296 0.58486-1.9844 0.88867-0.66113 0.35985-1.304 0.78807-1.9766 1.1055 0.06478-0.6657-0.35981-1.3047-0.99219-1.5176-0.68356-0.12015-1.4147-0.0418-2.1172-0.08398-0.51485-0.0042-1.0391 0.03897-1.4961-0.24219-0.67161-0.33925-1.4127-0.49266-2.1602-0.48633z"/>
						</svg>
						<span class='align-middle'>Emprunt :</span><br>
						<?php if($remainingLoan>0): ?>
						<span class='fw-bold'><?= $remainingLoan ?></span> thune(s)
						<?php else: ?>
						Aucun
						<?php endif; ?>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
<?php
$header = ob_get_clean();

/* ---Content--- */
ob_start();
?>
<?php if(isset($_SESSION['flash'])&& !empty($_SESSION['flash'])): ?>
<div class="row">
	<div class='col'>
		<div class='alert alert-<?= $_SESSION['flash']['class'] ?>'>
			<svg xmlns="http://www.w3.org/2000/svg" class="warning-icon-lg me-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
			  <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
			</svg>
			<span class='align-middle'><?= $_SESSION['flash']['message'] ?></span>
		</div>
	</div>
</div>
<?php endif ?>
<div class="row">
	<div class="col">
		<div class="card shadow">
			<div class='card-header'>
				<h4>Gestion de vos thunes</h4>
				<nav>
					<div class='nav nav-tabs mt-3' id="account-nav" role="tablist">
						<button class="nav-link<?php if(!isset($_SESSION['flash']['tab'])):?> active<?php endif?>" id="nav-overview-tab" data-bs-toggle="tab" data-bs-target="#nav-overview" type="button" role="tab" aria-controls="nav-overview" aria-selected="true">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
								<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605" />
							</svg>
							Suivi
						</button>
						<button class="nav-link<?php if(isset($_SESSION['flash']['tab']) AND $_SESSION['flash']['tab']=='operations'):?> active<?php endif?>" id="nav-operations-tab" data-bs-toggle="tab" data-bs-target="#nav-operations" type="button" role="tab" aria-controls="nav-operations" aria-selected="false">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
								<path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5 7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5" />
							</svg>
							Dépôt/Retrait/Virement
						</button>
						<button class="nav-link<?php if(isset($_SESSION['flash']['tab']) AND $_SESSION['flash']['tab']=='loan'):?> active<?php endif?>" id="nav-loan-tab" data-bs-toggle="tab" data-bs-target="#nav-loan" type="button" role="tab" aria-controls="nav-loan" aria-selected="false">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="0.8" stroke="currentColor"  class="w-6 h-6">
								<path d="m15.995 2.0037c-1.2506-0.043502-2.4962 0.30932-3.5527 0.97461-1.5342 0.90807-3.1091 1.752-4.625 2.6895-0.50277 0.26098-0.95758 0.6018-1.3477 1.0117-7.823e-4 8.579e-4 -0.00118 0.00307-0.00195 0.00378-0.5926 0.62445-1.0173 1.387-1.2344 2.2187-0.047924 0.17333-0.088403 0.34844-0.11913 0.52539-5.896e-4 0.0094-0.00137 0.02003-0.00195 0.02948-0.034394 0.2383-0.050646 0.47996-0.050797 0.7207 0.00166 0.26808 0.024567 0.53434 0.068372 0.79883 0.027591 0.20931 0.07132 0.41616 0.13478 0.61914 0.019653 0.05828 0.040743 0.1163 0.062513 0.17382 0.012094 0.03213 0.021921 0.06576 0.03515 0.09766 0.00605 0.01739 0.013606 0.03326 0.019654 0.0508 0.06909 0.19109 0.15073 0.37712 0.24219 0.5586 0.065688 0.13089 0.13395 0.26022 0.21094 0.38476 0.046791 0.07238 0.094148 0.14494 0.14453 0.21483 0.078917 0.11426 0.16188 0.22462 0.25002 0.33203 0.048907 0.06028 0.098721 0.11977 0.15039 0.17775 0.12813 0.14238 0.26275 0.27723 0.40625 0.4043 0.015874 0.0155 0.032504 0.03175 0.048831 0.04687 0.16566 0.1429 0.34227 0.27046 0.52539 0.39063 0.051099 0.03213 0.10401 0.06512 0.15625 0.0957 0.15753 0.09524 0.31654 0.18678 0.48437 0.26366 0.047509 0.02117 0.09445 0.04252 0.14256 0.06251 0.023433 0.0098 0.046715 0.02003 0.070299 0.02948 0.15523 0.0675 0.31612 0.12382 0.47851 0.17382 0.024189 0.0076 0.047773 0.01587 0.072265 0.02343 0.21634 0.06285 0.43833 0.1098 0.66211 0.14256 0.01209 2e-3 0.02305 0.0038 0.03515 0.0057 0.22715 0.03175 0.45645 0.04777 0.68555 0.04883 0.0098 6.88e-4 0.01965 0.0013 0.02948 2e-3 0.0045-3.44e-4 0.0091-0.0016 0.01361-2e-3 0.23252-8.63e-4 0.46299-0.0155 0.69141-0.04687 0.0023-3.4e-4 0.0057-0.0016 0.0079-2e-3 0.03175-0.0057 0.06387-0.01323 0.0957-0.01965 0.1561-0.02646 0.30981-0.05941 0.46289-0.09963 0.09895-0.02457 0.19767-0.05333 0.29492-0.08398 0.1372-0.04452 0.27315-0.09494 0.40625-0.15039 0.11441-0.0483 0.22537-0.09974 0.33592-0.15624 0.09226-0.04634 0.18217-0.09672 0.27148-0.14842 0.09562-0.05454 0.19128-0.11131 0.2832-0.17186 0.13587-0.0909 0.26623-0.18909 0.39258-0.29295 0.06127-0.05053 0.12083-0.10292 0.17968-0.15624 0.10556-0.09585 0.20598-0.19744 0.30274-0.30274 0.107-0.11603 0.2089-0.23592 0.30467-0.36132 0.04282-0.05586 0.08648-0.11229 0.12696-0.16993 0.08221-0.11565 0.15821-0.23339 0.23048-0.35546l2e-3 -2e-3v-2e-3c0.40663-0.69276 0.6438-1.4723 0.6914-2.2734 0.0014-0.02192 0.0045-0.04422 6e-3 -0.06641 0.0076-0.07846 0.01172-0.1556 0.0155-0.23437-7.2e-5 -0.58883-0.1025-1.1745-0.30274-1.7285 0.03175-0.01701 0.06391-0.03742 0.0957-0.05665l0.02154-0.01172c0.29363-0.18108 0.58756-0.45427 0.91601-0.20118 0.71324 0.28789 1.4455 0.62358 2.2344 0.52734 0.67212 0.03326 1.3493 9e-4 1.9629-0.30467 0.53262-0.17163 0.95517-0.63157 1.4863-0.76172h1.2402c-0.06384-0.27772 0.19808-0.87849-0.29688-0.72656-0.44895 0.04131-0.94494-0.083565-1.3652 0.060548-0.81108 0.50043-1.6574 1.0785-2.6563 1.0293-0.83969 0.08152-1.6449-0.17968-2.3984-0.52344-0.30217-0.12378-0.65604-0.34439-0.93945-0.05469l-2.1855 1.2325c-0.45731 0.1584-1.0092-0.23342-0.92188-0.72656 0.0045-0.17193 0.06954-0.30497 0.16407-0.41797l3.3223-2.0098c-0.15232-0.24011-0.28823-0.82204-0.63672-0.44727l-1.7949 1.0352c-0.85943-0.63881-1.8987-0.99135-2.9707-1.0098 1.2095-0.67793 2.3739-1.4509 3.6387-2.0254 1.0861-0.41175 2.2623-0.37259 3.3926-0.19922 1.2961 0.13168 2.5959 0.3109 3.8906 0.41211 0.60012-0.14536 1.2477-0.20013 1.8184-0.40039-0.08474-0.27984-0.0034-0.86373-0.4668-0.60938-0.68046 0.1115-1.3534 0.38414-2.0469 0.2129-1.4998-0.14804-2.9966-0.36408-4.498-0.46875zm-5.6934 3.5938c0.87722 0.021543 1.739 0.30584 2.4629 0.79492l-1.0176 0.63867c-0.47589-0.23509-1.0145-0.37032-1.5488-0.37304-0.04384-1.663e-4 -0.08708 4.271e-4 -0.13085 0.00195-0.31056 0.00869-0.59731 0.058129-0.90039 0.14649-0.43141 0.12688-0.88911 0.36442-1.2539 0.66797-0.72351 0.58709-1.1785 1.4602-1.2598 2.3828-0.086967 0.91149 0.20149 1.8816 0.8125 2.5898 0.69957 0.8254 1.792 1.3168 2.8789 1.248 0.89329-0.04706 1.7754-0.43548 2.3945-1.0977 0.7517-0.79412 1.1034-1.9431 0.91211-3.0195-0.0189-0.11414-0.04702-0.22639-0.07812-0.33789l0.95508-0.53515c0.21868 0.6441 0.29643 1.3355 0.2129 2.0117-0.16025 1.4391-1.0584 2.7685-2.3301 3.4668-1.2333 0.69314-2.791 0.76453-4.084 0.1914-1.2849-0.55276-2.2729-1.7279-2.6094-3.0801-0.34345-1.3286-0.036284-2.7999 0.79883-3.8887 0.82389-1.093 2.1619-1.7697 3.5313-1.8066 0.08508-0.00288 0.16894-0.00416 0.25391-0.00195zm-0.03704 1.6836c0.40806 0.015496 0.81426 0.11482 1.1875 0.28123-0.03288 0.51663-0.01172 1.0944 0.4336 1.4414 0.27099 0.31914 0.70649 0.41839 1.1113 0.42188 0.25482 0.94138 0.01436 2.0004-0.66406 2.7129-0.67765 0.75434-1.7772 1.1289-2.7734 0.88867-0.58219-0.12181-1.1404-0.41544-1.5234-0.87695-0.47073-0.44202-0.70231-1.0982-0.77734-1.7285-0.053707-0.7378 0.17802-1.4986 0.64648-2.0664 0.32916-0.36714 0.69321-0.68706 1.166-0.85352 0.37701-0.16743 0.7853-0.23599 1.1934-0.22072zm-1.5293 1.625-0.060548 1.1035h0.21483c0.04611-0.26853 0.11482-0.47063 0.20704-0.60351 0.12748-0.19257 0.28551-0.28906 0.47266-0.28906 0.05975 0 0.09831 0.01172 0.1172 0.03326 0.0189 0.0189 0.02948 0.06425 0.02948 0.13478v1.9727c0 0.1302-0.0011 0.21041-0.0038 0.24023-0.0028 0.02683-0.01172 0.04808-0.02532 0.06444-0.0325 0.04071-0.14532 0.06055-0.33789 0.06055h-0.08791v0.22069h1.6426v-0.22069h-0.08984c-0.19257 0-0.30535-0.02003-0.33789-0.06055-0.01361-0.01625-0.02079-0.03742-0.02343-0.06444-0.0028-0.02986-0.0038-0.11006-0.0038-0.24023v-1.9727c0-0.0706 0.0083-0.11588 0.02721-0.13478 0.0189-0.02154 0.05949-0.03326 0.11913-0.03326 0.16819 0 0.31472 0.0802 0.43945 0.24023 0.10579 0.13833 0.18402 0.35671 0.2383 0.65234h0.21679l-0.06055-1.1035zm-2.6602 6.3574c-0.10677 8.58e-4 -0.21351 0.0064-0.32032 0.01361-0.94498-0.0049-1.8104 0.4179-2.584 0.92383-0.42736 0.31722-1.0005 0.10534-1.498 0.166-0.37489-0.02721-0.77252-0.07559-0.61914 0.42383-0.15447 0.51258 0.48379 0.23471 0.77539 0.30274 0.44865-0.03859 0.94058 0.08198 1.3613-0.05858 0.86203-0.51711 1.7737-1.1451 2.832-1.0234 0.63725-0.02646 1.2611 0.07431 1.8359 0.35936 0.48582 0.19294 0.9593 0.45235 1.5 0.36132 0.73698 0.01739 1.4888-0.03288 2.2168 0.02532 0.52314 0.10579 0.70543 0.79906 0.36522 1.1816-0.42421 0.41272-1.0793 0.18966-1.6172 0.24608h-2.5645c0.063799 0.27768-0.19612 0.87877 0.29881 0.72656 1.279-0.01398 2.5664 0.02457 3.8398-0.02154 0.941-0.29802 1.7308-0.92634 2.6016-1.377 0.73628-0.39076 1.419-0.89099 2.1934-1.2031 0.56655-0.01512 1.0016 0.64488 0.67188 1.1328-0.47526 0.48334-1.1644 0.71656-1.7285 1.0937-1.4252 0.78569-2.7914 1.6846-4.2617 2.3828-1.053 0.41546-2.2035 0.42719-3.3086 0.25194-1.3718-0.13395-2.7437-0.34156-4.1152-0.42969-0.97683 0.13671-1.987 0.20621-2.9434 0.38477 0.088327 0.26944-0.061153 0.86891 0.41211 0.65234 0.94733-0.08545 1.8942-0.27931 2.8418-0.29688 1.6569 0.1632 3.3115 0.40324 4.9707 0.51758 0.29204-0.01209 0.58679 0.0102 0.87695-0.02721 1.2669-0.12352 2.3866-0.755 3.4531-1.4023 1.3683-0.79913 2.7616-1.5663 4.1152-2.3848 0.41635-0.31994 0.56939-0.84601 0.50195-1.3516-0.07487-0.66372-0.70814-1.2064-1.377-1.2344-0.75552-0.0102-1.3296 0.58486-1.9844 0.88867-0.66113 0.35985-1.304 0.78807-1.9766 1.1055 0.06478-0.6657-0.35981-1.3047-0.99219-1.5176-0.68356-0.12015-1.4147-0.0418-2.1172-0.08398-0.51485-0.0042-1.0391 0.03897-1.4961-0.24219-0.67161-0.33925-1.4127-0.49266-2.1602-0.48633z"/>
							</svg>
							Emprunt
						</button>
					</div>
				</nav>
			</div>
			<div class="card-body">
				<div class="tab-content" id="nav-accountContent">
					<div class='tab-pane fade<?php if(!isset($_SESSION['flash']['tab'])):?> show active<?php endif?>' id="nav-overview" role="tabpanel" aria-labelledby="nav-overview-tab" tabindex="0">
						<h5 class="card-title mb-3">Vos <?= $overview_limit ?> dernières opérations</h5>
						<?php if(isset($bank_log) AND !empty($bank_log)):?>
						<div class="table-responsive">
							<table class="table table-striped table-hover">
								<caption>opérations récentes</caption>
								<thead class='table-light'>
									<tr>
										<th scope="col">Catégorie</th>
										<th scope="col">Montant</th>
										<th class='w-25' scope="col">détail</th>
										<th class='text-center' scope="col">date</th>
									</tr>
								</thead>
								<tbody>
								<?php foreach($bank_log as $operation):
										switch($operation->operation){
											case 0:
												$ope = "dépôt";
												$symbol = '+';
												$classColor = 'table-success';
												break;
											case 1:
												$ope = "retrait";
												$symbol = '-';
												$classColor = 'table-danger';
												break;
											case 2:
												$ope = "Emprunt";
												$symbol = '+';
												$classColor = 'table-success';
												break;
											case 3:
												$ope = "Remboursement d'emprunt";
												$symbol = '-';
												$classColor = 'table-danger';
												break;
											case 4:
												$ope = "virement";
												if($operation->id_receiver==$perso->id_perso){
													$symbol = '+';
													$classColor = 'table-success';
												}else{
													$symbol = '-';
													$classColor = 'table-danger';
												}
												break;
											default :
												$ope = "inconnu";
												$symbol = '?';
										}
								?>
									<tr class='<?=$classColor?>'>
										<th scope="row">
											<?= $ope ?>
										</th>
										<td>
											<?= $symbol?> <?= $operation->montant_transfert?>
										</td>
										<td>
											<?= $operation->details ?? '---'?>
										</td>
										<td class='text-center'>
											<?php $operationDate = new DateTimeImmutable($operation->date_log)?>
											<?= $operationDate->format('\l\e d-m-Y à H:i:s');?>
										</td>
									</tr>
								<?php endforeach;?>
								</tbody>
							</table>
						</div>
						<?php else: ?>
						<p>
							Vous n'avez fait aucune opération pour l'instant.
						</p>
						<?php endif; ?>
					</div>
					<div class='tab-pane fade<?php if(isset($_SESSION['flash']['tab']) AND $_SESSION['flash']['tab']=='operations'):?> show active<?php endif?>' id="nav-operations" role="tabpanel" aria-labelledby="nav-operations-tab" tabindex="0">
						<div class='row row-cols-1 row-cols-md-3 g-2'>
							<div class="col">
								<div class="card h-100">
									<div class="card-body">
										<h5 class="card-title">
											<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
												<path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 0 0-2.25 2.25v9a2.25 2.25 0 0 0 2.25 2.25h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25H15M9 12l3 3m0 0 3-3m-3 3V2.25" />
											</svg>
											<span class='align-middle'>Dépôt</span>
										</h5>
										<small class='text-muted'>
											<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
												<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
											</svg>
											Minimum 25 thunes
										</small>
										<?php if(isset($_SESSION['errors'])&& !empty($_SESSION['errors']) && $_SESSION['old_input']['operation']=='deposit'): ?>
										<p>
											<div class='alert alert-danger'>
												<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 me-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
												  <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
												</svg>
												<?php foreach($_SESSION['errors'] as $error):?>
													<?php foreach($error as $key=>$value):?>
													<span class='align-middle'><?= $value ?></span>
													<?php endforeach ;?>
												<?php endforeach ?>
											</div>
										</p>
										<?php endif ?>
										<p class="card-text">
											<?php if($antizerk): ?>
											<p class='alert alert-warning' role='alert'>
												<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-clock-history" viewBox="0 0 16 16">
													<path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022zm2.004.45a7 7 0 0 0-.985-.299l.219-.976q.576.129 1.126.342zm1.37.71a7 7 0 0 0-.439-.27l.493-.87a8 8 0 0 1 .979.654l-.615.789a7 7 0 0 0-.418-.302zm1.834 1.79a7 7 0 0 0-.653-.796l.724-.69q.406.429.747.91zm.744 1.352a7 7 0 0 0-.214-.468l.893-.45a8 8 0 0 1 .45 1.088l-.95.313a7 7 0 0 0-.179-.483m.53 2.507a7 7 0 0 0-.1-1.025l.985-.17q.1.58.116 1.17zm-.131 1.538q.05-.254.081-.51l.993.123a8 8 0 0 1-.23 1.155l-.964-.267q.069-.247.12-.501m-.952 2.379q.276-.436.486-.908l.914.405q-.24.54-.555 1.038zm-.964 1.205q.183-.183.35-.378l.758.653a8 8 0 0 1-.401.432z"/>
													<path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0z"/>
													<path d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5"/>
												</svg>
												Loi antizerk. Vous avez retiré de la thune il y a moins de 8 heures.<br>
												<span class='fw-semibold'>temps restant : <?=$antizerk->format('%Hh%Im%Ss')?></span>
											</p>
											<?php else: ?>
											<?php if($remainingLoan>0): ?>
											<p class='alert alert-primary'>
												Vous avez un emprunt en cours de <?= $remainingLoan?> thunes.<br>
												Votre dépôt servira d'abord à rembourser vos dettes
											</p>
											<?php endif; ?>
											<div class='row'>
												<div class='col col-sm-9 col-md-12 col-lg-9'>
													<form class='input-group mb-3' action="?id=<?= $bank->id ?>&action=ope&id_bank=<?= $bank->id ?>" method="post" name="depositForm">
														<input id='value' name='value' type="number" class="form-control" placeholder="25" min='25' aria-label="déposer des thunes" aria-describedby='deposit_btn'>
														<input id="operation" name="operation" type="hidden" value="deposit" />
														<input id="id_company" name="id_company" type="hidden" value="<?= $bank->id_compagnie ?>" />
														<input id="id_perso" name="id_perso" type="hidden" value="<?= $perso->id_perso ?>" />
														<input id="id_bank" name="id_bank" type="hidden" value="<?= $bank->id ?>" />
														<input id="id_account" name="id_account" type="hidden" value="<?= $account->id ?>" />
														<button class="btn btn-success" type="submit" id="deposit_btn">Faire un dépôt</button>
													</form>
												</div>
											</div>
											<?php endif; ?>
										</p>
									</div>
								</div>
							</div>
							<div class="col">
								<div class="card h-100">
									<div class="card-body">
										<h5 class="card-title">
											<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
												<path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 0 0-2.25 2.25v9a2.25 2.25 0 0 0 2.25 2.25h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25H15m0-3-3-3m0 0-3 3m3-3V15" />
											</svg>
											<span class='align-middle'>Retrait</span>
										</h5>
										<small class='text-muted'>
											<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
												<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
											</svg>
											<span class='align-middle'><?= $account->montant?> thune(s) en banque</span>
											<?php if($disposableIncome<$account->montant): ?>
											<br><span span class='align-middle'>retrait maximum : <?= $disposableIncome?> thune(s)</span>
											<?php else: ?>
											<br><span span class='align-middle'>retrait maximum : <?= $account->montant?> thune(s)</span>
											<?php endif; ?>
										</small>
										<?php if(isset($_SESSION['errors'])&& !empty($_SESSION['errors']) && $_SESSION['old_input']['operation']=='withdrawal'): ?>
										<p>
											<div class='alert alert-danger'>
												<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 me-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
												  <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
												</svg>
												<?php foreach($_SESSION['errors'] as $error):?>
													<?php foreach($error as $key=>$value):?>
													<span class='align-middle'><?= $value ?></span>
													<?php endforeach ;?>
												<?php endforeach ?>
											</div>
										</p>
										<?php endif ?>
										<p class="card-text">
											<?php if($disposableIncome<=0): ?>
											<p class='alert alert-warning' role='alert'>
												<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
													<path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
												</svg>
												<span class='align-middle'>Votre compagnie a <?= $disposableIncome ?> thune(s) disponible(s).<br>
												Vous ne pouvez pas retirer d'argent.</span>
											</p>
											<?php elseif($account->montant<=0): ?>
											<p class='alert alert-warning' role='alert'>
												<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
													<path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
												</svg>
												<span class='align-middle'>Vous avez <?= $account->montant ?> thunes en banque.<br>
												Vous ne pouvez pas retirer d'argent. Si besoin, faites un emprunt.</span>
											</p>
											<?php else: ?>
											<div class='row'>
												<div class='col col-sm-9 col-md-12 col-lg-9'>
													<form class='input-group mb-3' action="?id=<?= $bank->id ?>&action=ope&id_bank=<?= $bank->id ?>" method="post" name="withdrawalForm">
														<input id='value' name='value' type="number" class="form-control" placeholder="0" min='1' max='<?=$disposableIncome?>' aria-label="retirer des thunes" aria-describedby='withdrawal_btn'>
														<input id="operation" name="operation" type="hidden" value="withdrawal" />
														<input id="id_company" name="id_company" type="hidden" value="<?= $bank->id_compagnie ?>" />
														<input id="id_perso" name="id_perso" type="hidden" value="<?= $perso->id_perso ?>" />
														<input id="id_bank" name="id_bank" type="hidden" value="<?= $bank->id ?>" />
														<input id="id_account" name="id_account" type="hidden" value="<?= $account->id ?>" />
														<button class="btn btn-primary" type="submit" id="withdrawal_btn">Faire un retrait</button>
													</form>
												</div>
											</div>
											<?php endif; ?>
										</p>
									</div>
								</div>
							</div>
							<div class="col">
								<div class="card h-100">
									<div class="card-body">
										<h5 class="card-title">
											<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
												<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
											</svg>
											<span class='align-middle'>Virement</span>
										</h5>
										<small class='text-muted'>à un autre membre de la compagnie</small>
										<?php if(isset($_SESSION['errors'])&& !empty($_SESSION['errors']) && $_SESSION['old_input']['operation']=='transfer'): ?>
										<p>
											<div class='alert alert-danger'>
												<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 me-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
												  <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
												</svg>
												<?php foreach($_SESSION['errors'] as $error):?>
													<?php foreach($error as $key=>$value):?>
													<span class='align-middle'><?= $value ?></span>
													<?php endforeach ;?>
												<?php endforeach ?>
											</div>
										</p>
										<?php endif ?>
										<p class="card-text">
											<?php if($account->montant<=0): ?>
											<p class='alert alert-warning' role='alert'>
												<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
													<path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
												</svg>
												<span class='align-middle'>Vous avez <?= $account->montant ?> thunes en banque.<br>
												Vous ne pouvez pas faire de virement à un autre membre.</span>
											</p>
											<?php else: ?>
											<div class='row'>
												<div class='col col-sm-9 col-md-12 col-lg-9'>
													<?php if(isset($companyMembers) AND !empty($companyMembers)): ?>
													<form class='mb-3' action="?id=<?= $bank->id ?>&action=ope&id_bank=<?= $bank->id ?>" method="post" name="transferForm">
														<select name='id_target' id='id_target' class="form-select mb-3" aria-label="Sélection du bénéficiaire">
															<option selected>Bénéficiaire</option>
															<?php foreach($companyMembers as $member): ?>
															<option value="<?= $member->id_perso ?>"><?= $member->nom_perso ?> [<?= $member->id_perso ?>]</option>
															<?php endforeach ;?>
														</select>
														<div class='input-group'>
															<input id='value' name='value' type="number" class="form-control" placeholder="0" min='1' aria-label="faire un virement" aria-describedby='transfer_btn'>
															<input id="operation" name="operation" type="hidden" value="transfer" />
															<input id="id_company" name="id_company" type="hidden" value="<?= $bank->id_compagnie ?>" />
															<input id="id_perso" name="id_perso" type="hidden" value="<?= $perso->id_perso ?>" />
															<input id="id_bank" name="id_bank" type="hidden" value="<?= $bank->id ?>" />
															<input id="id_account" name="id_account" type="hidden" value="<?= $account->id ?>" />
															<button class="btn btn-secondary" type="submit" id="transfer_btn">Faire un virement</button>
														</div>
													</form>
													<?php else: ?>
													<p class='alert alert-warning' role='alert'>
														<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
															<path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
														</svg>
														<span class='align-middle'>Vous êtes seul dans votre compagnie.<br>
														Vous ne pouvez pas faire de virement à un autre membre.</span>
													</p>
													<?php endif; ?>
												</div>
											</div>
											<?php endif; ?>
										</p>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class='tab-pane fade<?php if(isset($_SESSION['flash']['tab']) AND $_SESSION['flash']['tab']=='loan'):?> show active<?php endif?>' id="nav-loan" role="tabpanel" aria-labelledby="nav-loan-tab" tabindex="0">
						<div class='row'>
							<div class="col col-sm-7 col-md-5">
								<h5 class="card-title mb-3">Emprunts</h5>
								<?php if(isset($_SESSION['errors'])&& !empty($_SESSION['errors']) && $_SESSION['old_input']['operation']=='loan'): ?>
										<p>
											<div class='alert alert-danger'>
												<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 me-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
												  <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
												</svg>
												<?php foreach($_SESSION['errors'] as $error):?>
													<?php foreach($error as $key=>$value):?>
													<span class='align-middle'><?= $value ?></span>
													<?php endforeach ;?>
												<?php endforeach ?>
											</div>
										</p>
								<?php endif ?>
								<?php if($loans AND !empty($loans)):?>
									<?php if($remainingLoan<=0): ?>
										<?php if($account->demande_emprunt==1): ?>
										<p class='alert alert-info'>
											<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
											  <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 15.75-2.489-2.489m0 0a3.375 3.375 0 1 0-4.773-4.773 3.375 3.375 0 0 0 4.774 4.774ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
											</svg>
											<span class='align-middle'>Demande en cours d'étude...</span>
										</p>
										<p class=''>
											Vous avez une demande d'emprunt de <span class='fw-semibold'><?= $account->montant_emprunt?> thune(s)</span> en attente.<br>
											Elle sera étudiée prochainement par le trésorier.<br>
											<form class='my-3' action="?id=<?= $bank->id ?>&action=ope&id_bank=<?= $bank->id ?>" method="post" name="loanCancelForm">
												<input id="value" name="value" type="hidden" value="<?= $account->montant_emprunt?>" />
												<input id="operation" name="operation" type="hidden" value="cancel_loan_demand" />
												<input id="id_company" name="id_company" type="hidden" value="<?= $bank->id_compagnie ?>" />
												<input id="id_perso" name="id_perso" type="hidden" value="<?= $perso->id_perso ?>" />
												<input id="id_bank" name="id_bank" type="hidden" value="<?= $bank->id ?>" />
												<input id="id_account" name="id_account" type="hidden" value="<?= $account->id ?>" />
												<button class="btn btn-secondary" type="submit" id="loanCancel_btn" name='loanCancel_btn' value='1'>Annuler la demande</button>
											</form>
										</p>
										<?php else: ?>
										<p class=''>
											Votre emprunt est remboursé.<br>
											<small class='text-muted'>
												<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
													<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
												</svg>
												<span class='align-middle'><?= $account->montant?> thune(s) en banque</span>
												<br><span span class='align-middle'>montant maximum : <?= $disposableIncome?> thune(s)</span>
											</small>
											<form class='input-group my-3' action="?id=<?= $bank->id ?>&action=ope&id_bank=<?= $bank->id ?>" method="post" name="loanDemandForm">
												<input id='value' name='value' type="number" class="form-control" placeholder="0" min='1' max='<?=$disposableIncome?>' aria-label="montant de l'emprunt" aria-describedby='loan_demand_btn'>
												<input id="operation" name="operation" type="hidden" value="loan_demand" />
												<input id="id_company" name="id_company" type="hidden" value="<?= $bank->id_compagnie ?>" />
												<input id="id_perso" name="id_perso" type="hidden" value="<?= $perso->id_perso ?>" />
												<input id="id_bank" name="id_bank" type="hidden" value="<?= $bank->id ?>" />
												<input id="id_account" name="id_account" type="hidden" value="<?= $account->id ?>" />
												<button class="btn btn-secondary" type="submit" id="loan_demand_btn">Faire une nouvelle demande</button>
											</form>
										</p>
										<?php endif; ?>
									<?php endif;?>
									<hr>
									<h5 class='card-title mb-3'>Historique d'emprunt</h5>
									<div class="table-responsive">
										<table class="table table-striped table-hover">
											<caption><?php if($remainingLoan<=0): ?>historique d'emprunt<?php else: ?>Emprunts en cours<?php endif;?></caption>
											<thead class='table-light'>
												<tr>
													<th scope="col">Catégorie</th>
													<th scope="col">Montant</th>
													<th class='w-25' scope="col">détail</th>
													<th class='text-center' scope="col">date</th>
												</tr>
											</thead>
											<tbody>
												<?php foreach($loans as $loan):?>
												<tr>
													<th scope="row" class=''>
														<?php
														switch($loan->operation){
															case 0:
																$ope = "dépôt";
																break;
															case 1:
																$ope = "retrait";
																break;
															case 2:
																$ope = "Emprunt";
																break;
															case 3:
																$ope = "Remboursement d'emprunt";
																break;
															case 4:
																$ope = "virement";
																break;
															default :
																$ope = "inconnu";
														}
														echo $ope;
														?>
													</th>
													<td>
														<?= $loan->montant_transfert?>
													</td>
													<td>
														<?= $loan->details ?? '---'?>
													</td>
													<td class='text-center'>
														<?php $loanDate = new DateTimeImmutable($loan->date_log)?>
														<?= $loanDate->format('\l\e d-m-Y à H:i:s');?>
													</td>
												</tr>
												<?php endforeach;?>
											</tbody>
										</table>
									</div>
								<?php else: ?>
									<?php if($account->demande_emprunt==1): ?>
										<p class='alert alert-info'>
											<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
											  <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 15.75-2.489-2.489m0 0a3.375 3.375 0 1 0-4.773-4.773 3.375 3.375 0 0 0 4.774 4.774ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
											</svg>
											<span class='align-middle'>Demande en cours d'étude...</span>
										</p>
										<?php if(isset($_SESSION['errors'])&& !empty($_SESSION['errors']) && $_SESSION['old_input']['operation']=='cancel_loan_demand'): ?>
										<p>
											<div class='alert alert-danger'>
												<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 me-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
												  <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
												</svg>
												<?php foreach($_SESSION['errors'] as $error):?>
													<?php foreach($error as $key=>$value):?>
													<span class='align-middle'><?= $value ?></span>
													<?php endforeach ;?>
												<?php endforeach ?>
											</div>
										</p>
										<?php endif ?>
										<p class=''>
											Vous avez une demande d'emprunt de <span class='fw-semibold'><?= $account->montant_emprunt?> thune(s)</span> en attente.<br>
											Elle sera étudiée prochainement par le trésorier.<br>
											<form class='my-3' action="?id=<?= $bank->id ?>&action=ope&id_bank=<?= $bank->id ?>" method="post" name="loanCancelForm">
												<input id="value" name="value" type="hidden" value="<?= $account->montant_emprunt?>" />
												<input id="operation" name="operation" type="hidden" value="cancel_loan_demand" />
												<input id="id_company" name="id_company" type="hidden" value="<?= $bank->id_compagnie ?>" />
												<input id="id_perso" name="id_perso" type="hidden" value="<?= $perso->id_perso ?>" />
												<input id="id_bank" name="id_bank" type="hidden" value="<?= $bank->id ?>" />
												<input id="id_account" name="id_account" type="hidden" value="<?= $account->id ?>" />
												<button class="btn btn-secondary" type="submit" id="loanCancel_btn" name='loanCancel_btn' value='1'>Annuler la demande</button>
											</form>
										</p>
									<?php else: ?>
										<p class=''>
											Vous n'avez fait aucune demande d'emprunt.<br>
											<small class='text-muted'>
												<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
													<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
												</svg>
												<span class='align-middle'><?= $account->montant?> thune(s) en banque</span>
												<br><span span class='align-middle'>montant maximum : <?= $disposableIncome?> thune(s)</span>
											</small>
											<form class='input-group my-3' action="?id=<?= $bank->id ?>&action=ope&id_bank=<?= $bank->id ?>" method="post" name="loanDemandForm">
												<input id='value' name='value' type="number" class="form-control" placeholder="0" min='1' max='<?=$disposableIncome?>' aria-label="montant de l'emprunt" aria-describedby='loan_demand_btn'>
												<input id="operation" name="operation" type="hidden" value="loan_demand" />
												<input id="id_company" name="id_company" type="hidden" value="<?= $bank->id_compagnie ?>" />
												<input id="id_perso" name="id_perso" type="hidden" value="<?= $perso->id_perso ?>" />
												<input id="id_bank" name="id_bank" type="hidden" value="<?= $bank->id ?>" />
												<input id="id_account" name="id_account" type="hidden" value="<?= $account->id ?>" />
												<button class="btn btn-secondary" type="submit" id="loan_demand_btn">Faire une demande</button>
											</form>
										</p>
									<?php endif; ?>
								<?php endif; ?>
							</div>
						</div>
						
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php $content = ob_get_clean(); ?>

<?php require('../mvc/view/layouts/app.php'); ?>