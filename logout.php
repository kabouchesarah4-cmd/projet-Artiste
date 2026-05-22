<?php
session_start();
session_destroy(); // Détruit toutes les données de session
header('Location: login.php'); // Redirige vers la page de login
exit;
?>