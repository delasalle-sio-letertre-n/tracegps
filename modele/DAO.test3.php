<?php
// Projet TraceGPS
// fichier : modele/DAO.test1.php
// Rôle : test de la classe DAO.class.php
// Dernière mise à jour : xxxxxxxxxxxxxxxxx par xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

// Le code des tests restant à développer va être réparti entre les membres de l'équipe de développement.
// Afin de limiter les conflits avec GitHub, il est décidé d'attribuer un fichier de test à chaque développeur.
// Développeur 1 : fichier DAO.test1.php
// Développeur 2 : fichier DAO.test2.php
// Développeur 3 : fichier DAO.test3.php
// Développeur 4 : fichier DAO.test4.php

// Quelques conseils pour le travail collaboratif :
// avant d'attaquer un cycle de développement (début de séance, nouvelle méthode, ...), faites un Pull pour récupérer
// la dernière version du fichier.
// Après avoir testé et validé une méthode, faites un commit et un push pour transmettre cette version aux autres développeurs.
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Test de la classe DAO</title>
	<style type="text/css">body {font-family: Arial, Helvetica, sans-serif; font-size: small;}</style>
</head>
<body>

<?php
// connexion du serveur web à la base MySQL
include_once ('DAO.class.php');
$dao = new DAO();

// test de la méthode creerUneTrace ----------------------------------------------------------
// modifié par Jimmy Le Goff le 22/11/2022
echo "<h3>Test de creerUneTrace : </h3>";
$trace1 = new Trace(0, "2017-12-18 14:00:00", "2017-12-18 14:10:00", true, 3);
$ok = $dao->creerUneTrace($trace1);
if ($ok) {
    echo "<p>Trace bien enregistrée !</p>";
    echo $trace1->toString();
}
else {
    echo "<p>Echec lors de l'enregistrement de la trace !</p>";
}
$trace2 = new Trace(0, date('Y-m-d H:i:s', time()), null, false, 3);
$ok = $dao->creerUneTrace($trace2);
if ($ok) {
    echo "<p>Trace bien enregistrée !</p>";
    echo $trace2->toString();
}
else {
    echo "<p>Echec lors de l'enregistrement de la trace !</p>";
}

// test de la méthode supprimerUneTrace -----------------------------------------------------------
// modifié par Jimmy Le Goff le 22/11/2022
echo "<h3>Test de supprimerUneTrace : </h3>";
$ok = $dao->supprimerUneTrace(22);
if ($ok) {
    echo "<p>Trace bien supprimée !</p>";
}
else {
    echo "<p>Echec lors de la suppression de la trace !</p>";
}


// test des méthodes creerUnPointDeTrace et terminerUneTrace --------------------------------------
// modifié par Jimmy Le Goff le 22/11/2022
echo "<h3>Test de terminerUneTrace : </h3>";
// on choisit une trace non terminée
$unIdTrace = 3;
// on l'affiche
$laTrace = $dao->getUneTrace($unIdTrace);
echo "<h4>l'objet laTrace avant l'appel de la méthode terminerUneTrace : </h4>";
echo ($laTrace->toString());
echo ('<br>');
// on la termine
$dao->terminerUneTrace($unIdTrace);
// et on l'affiche à nouveau
$laTrace = $dao->getUneTrace($unIdTrace);
echo "<h4>l'objet laTrace après l'appel de la méthode terminerUneTrace : </h4>";
echo ($laTrace->toString());
echo ('<br>');



//test
// ferme la connexion à MySQL :
unset($dao);
?>

</body>
</html>