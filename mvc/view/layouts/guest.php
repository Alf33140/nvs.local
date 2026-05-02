<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="Le jeu au tour par tour sur la guerre de sécession">
		<meta name="author" content="Maxime RAFFIN">

        <title><?php if($title){echo $title.' - ';}?>Nord vs Sud</title><!--1861 : Blood and War-->
		
		<!--<link rel="shortcut icon" href="public/favicon.ico">-->
		<!--<link rel="icon" type="image/png" href="public/favicon.png">-->

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
			<!-- Bootstrap CSS -->
			<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
			<link rel="stylesheet" href="../public/css/app.css">

        <!-- Scripts -->
		<!-- Bunddle Popper.js & Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous" defer></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
		<script type="text/javascript" src="../public/js/app.js" defer></script>
    </head>
    <body>
        <div class='app'>
			<!-- Page Header -->
            <?php include('header.php') ?>            

            <!-- Page Content -->
            <main class='container-xxl'>
				<?= $content ?>
            </main>
			
			<!-- Page Footer -->
			<?php include('footer.php') ?>
        </div>
    </body>
</html>
<?php
	unset($_SESSION["flash"]);
	unset($_SESSION["old_input"]);
	unset($_SESSION["errors"]);
?>