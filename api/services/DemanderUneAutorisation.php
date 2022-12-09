<?php


// URL DE TEST : http://localhost/ws-php-letertre/tracegps/api/DemanderUneAutorisation?pseudo=europa&mdp=13e3668bbee30b004380052b086457b014504b3e&pseudoDestinataire=nicolas&texteMessage=coucou&nomPrenom=francois-cluzet&lang=xml


// Projet TraceGPS - services web
// fichier :  api/services/DemanderUneAutorisation.php
// Dernière mise à jour : 330/11/2022 par Nicolas Le Tertre A 100 %

// Rôle : ce service web permet à un utilisateur destinataire d'accepter ou de rejeter une demande d'autorisation provenant d'un utilisateur demandeur
// il envoie un mail au demandeur avec la décision de l'utilisateur destinataire

// Le service web doit être appelé avec 4 paramètres obligatoires dont les noms sont volontairement non significatifs :
//    a : le mot de passe (hashé) de l'utilisateur destinataire de la demande ($mdpSha1)
//    b : le pseudo de l'utilisateur destinataire de la demande ($pseudoAutorisant)
//    c : le pseudo de l'utilisateur source de la demande ($pseudoAutorise)
//    d : la decision 1=oui, 0=non ($decision)

// Les paramètres doivent être passés par la méthode GET :
//     http://<hébergeur>/tracegps/api/DemandrUneeAutorisation......
	
// ces variables globales sont définies dans le fichier modele/parametres.php
global $ADR_MAIL_EMETTEUR, $ADR_SERVICE_WEB;

// connexion du serveur web à la base MySQL
$dao = new DAO();

// Récupération des données transmises
$pseudo = ( empty($this->request['pseudo'])) ? "" : $this->request['pseudo'];
$mdp = ( empty($this->request['mdp'])) ? "" : $this->request['mdp'];
$pseudoDestinataire = ( empty($this->request['pseudoDestinataire'])) ? "" : $this->request['pseudoDestinataire'];
$texteMessage = ( empty($this->request['texteMessage'])) ? "" : $this->request['texteMessage'];
$nomPrenom = ( empty($this->request['nomPrenom'])) ? "" : $this->request['nomPrenom'];
$lang = ( empty($this->request['lang'])) ? "" : $this->request['lang'];

// La méthode HTTP utilisée doit être GET
if ($this->getMethodeRequete() != "GET")
{	$message = "Erreur : méthode HTTP incorrecte.";
    $code_reponse = 406;
}
else {
    // Les paramètres doivent être présents et corrects
    if ( $pseudo == "" || $mdp == "" || $pseudoDestinataire == "" || $texteMessage == "" || $nomPrenom == "" || $lang == "")
    {	$message = "Erreur : données incomplètes.";
        $code_reponse = 400;
    }
    else
    {	// test de l'authentification de l'utilisateur
    	// la méthode getNiveauConnexion de la classe DAO retourne les valeurs 0 (non identifié) ou 1 (utilisateur) ou 2 (administrateur)
    	$niveauConnexion = $dao->getNiveauConnexion($pseudo, $mdp);
    
    	if ( $niveauConnexion == 0 )
    	{  $message = "Erreur : authentification incorrecte.";
    	   $code_reponse = 401;
    	}
    	else
    	{	$utilisateurDemandeur = $dao->getUnUtilisateur($pseudo);
            $utilisateurDestinataire = $dao->getUnUtilisateur($pseudoDestinataire);
            $idAutorisant = $utilisateurDestinataire->getId();
            $idAutorise = $utilisateurDemandeur->getId();            
            $adrMailDestinataire = $utilisateurDestinataire->getAdrMail();

            
            if ($dao->autoriseAConsulter($idAutorisant, $idAutorise))
            {	$message = "Erreur : autorisation déjà accordée.";
                $code_reponse = 400;
            }
            else 
            {
                $lien = "http://localhost/ws-php-letertre/tracegps/api/ValiderDemandeAutorisation?a=".$utilisateurDestinataire->getMdpSha1()."&b=".$pseudoDestinataire."&c=".$pseudo."&d=";
                // envoi d'un mail de demande d'autorisation au concerné
                $sujetMail = "Votre demande d'autorisation à un utilisateur du système TraceGPS";
                $contenuMail = "Cher ou chère " . $pseudoDestinataire . "\n\n";
                $contenuMail .= "Un utilisateur du système TraceGPS vous demande l'autorisation de suivre vos parcours.\n";
                $contenuMail .= "Voici les données le concernant :\n\n";
            	$contenuMail .= "Son pseudo : " . $pseudo ."\n";
            	$contenuMail .= "Son adresse mail : " . $utilisateurDemandeur->getAdrMail() ."\n";
            	$contenuMail .= "Son numéro de téléphone : " . $utilisateurDemandeur->getNumTel() ."\n";
            	$contenuMail .= "Son nom et prénom : " . $nomPrenom ."\n";
            	$contenuMail .= "Son message : " . $texteMessage ."\n\n";
            	$contenuMail .= "Pour accepter la demande, cliquez sur ce lien :\n";
            	$contenuMail .= $lien."1\n\n";
            	$contenuMail .= "Pour rejeter la demande, cliquez sur ce lien :\n";
            	$contenuMail .= $lien."0\n\n";
            	$ok = Outils::envoyerMail($adrMailDestinataire, $sujetMail, $contenuMail, $ADR_MAIL_EMETTEUR);
            	if ( ! $ok ) {
            	    $message = "Erreur : l'envoi du courriel de demande d'autorisation a rencontré un problème.";
            	    $code_reponse = 500;
            	}
            	else {
            		$message = $pseudoDestinataire." va recevoir un courriel avec votre demande.";
            		$code_reponse = 200;
            	}
            }
    	}	
    }
}
// ferme la connexion à MySQL :
unset($dao);

// création du flux en sortie
if ($lang == "xml")
{
    $content_type = "application/xml; charset=utf-8"; // indique le format XML pour la réponse
    $donnees = creerFluxXML($message);
}
else
{
    $content_type = "application/json; charset=utf-8"; // indique le format Json pour la réponse
    $donnees = creerFluxJSON($message);
}

// envoi de la réponse HTTP
$this->envoyerReponse($code_reponse, $content_type, $donnees);

// fin du programme (pour ne pas enchainer sur les 2 fonctions qui suivent)
exit;

// ================================================================================================
// création du flux XML en sortie
function creerFluxXML($msg)
{
    /* Exemple de code XML
     <?xml version="1.0" encoding="UTF-8"?>
     <!--Service web ChangerDeMdp - BTS SIO - Lycée De La Salle - Rennes-->
     <data>
     <reponse>Erreur : authentification incorrecte.</reponse>
     </data>
     */
    
    // crée une instance de DOMdocument (DOM : Document Object Model)
    $doc = new DOMDocument();
    
    // specifie la version et le type d'encodage
    $doc->version = '1.0';
    $doc->encoding = 'UTF-8';
    
    // crée un commentaire et l'encode en UTF-8
    $elt_commentaire = $doc->createComment('Service web DemanderUneAutorisation - BTS SIO - Lycée De La Salle - Rennes');
    // place ce commentaire à la racine du document XML
    $doc->appendChild($elt_commentaire);
    
    // crée l'élément 'data' à la racine du document XML
    $elt_data = $doc->createElement('data');
    $doc->appendChild($elt_data);
    
    // place l'élément 'reponse' juste après l'élément 'data'
    $elt_reponse = $doc->createElement('reponse', $msg);
    $elt_data->appendChild($elt_reponse);
    
    // Mise en forme finale
    $doc->formatOutput = true;
    
    // renvoie le contenu XML
    return $doc->saveXML();
}

// ================================================================================================
// création du flux JSON en sortie
function creerFluxJSON($msg)
{
    /* Exemple de code JSON
     {
     "data": {
     "reponse": "Erreur : authentification incorrecte."
     }
     }
     */
    
    // construction de l'élément "data"
    $elt_data = ["reponse" => $msg];
    
    // construction de la racine
    $elt_racine = ["data" => $elt_data];
    
    // retourne le contenu JSON (l'option JSON_PRETTY_PRINT gère les sauts de ligne et l'indentation)
    return json_encode($elt_racine, JSON_PRETTY_PRINT);
}

// ================================================================================================

?>