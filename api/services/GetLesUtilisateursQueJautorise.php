<?php
// Projet TraceGPS - services web
// fichier : api/services/GetLesUtilisateursQueJautorise.php

// connexion du serveur web à la base MySQL
$dao = new DAO();
	
// Récupération des données transmises
$pseudo = ( empty($this->request['pseudo'])) ? "" : $this->request['pseudo'];
$mdpSha1 = ( empty($this->request['mdp'])) ? "" : $this->request['mdp'];
$lang = ( empty($this->request['lang'])) ? "" : $this->request['lang'];

// "xml" par défaut si le paramètre lang est absent ou incorrect
if ($lang != "json") $lang = "xml";

// initialisation du nombre de réponses
$nbReponses = 0;
$lesUtilisateursAutorises = array();
$idUtilisateur = $dao->getUnUtilisateur($pseudo)->getId();

// La méthode HTTP utilisée doit être GET
if ($this->getMethodeRequete() != "GET")
{	$msg = "Erreur : méthode HTTP incorrecte.";
    $code_reponse = 406;
}
else {
    // Les paramètres doivent être présents
    if ( $pseudo == "" || $mdpSha1 == "" )
    {	$msg = "Erreur : données incomplètes.";
        $code_reponse = 400;
    }
    else
    {	if ( $dao->getNiveauConnexion($pseudo, $mdpSha1) == 0 ) {
    		$msg = "Erreur : authentification incorrecte.";
    		$code_reponse = 401;
        }
    	else 
    	{    // récupération de la liste des utilisateurs autorisé à l'aide de la méthode getLesUtilisateursAutorises de la classe DAO
    	    $lesUtilisateursAutorises = $dao->getLesUtilisateursAutorises($idUtilisateur);
    	    
    	    // mémorisation du nombre d'utilisateurs
    	    $nbReponses = sizeof($lesUtilisateursAutorises);
    	
    	    if ($nbReponses == 0) {
    	        $msg = "Aucune autorisation accordée par " . $pseudo;
    			$code_reponse = 200;
    	    }
    	    else {
    	        $msg = $nbReponses . " autorisation(s) accordée(s) par neon.";
    			$code_reponse = 200;
    	    }
    	}
    }
}
// ferme la connexion à MySQL :
unset($dao);

// création du flux en sortie
if ($lang == "xml") {
    $content_type = "application/xml; charset=utf-8";      // indique le format XML pour la réponse
    $donnees = creerFluxXML($msg, $lesUtilisateursAutorises);
}
else {
    $content_type = "application/json; charset=utf-8";      // indique le format Json pour la réponse
    $donnees = creerFluxJSON($msg, $lesUtilisateursAutorises);
}

// envoi de la réponse HTTP
$this->envoyerReponse($code_reponse, $content_type, $donnees);

// fin du programme (pour ne pas enchainer sur les 2 fonctions qui suivent)
exit;

// ================================================================================================
 
// création du flux XML en sortie
function creerFluxXML($msg, $lesUtilisateursAutorises)
{	
    
    
    
    /* Exemple de code XML
        <?xml version="1.0" encoding="UTF-8"?>
        <!--Service web getLesUtilisateursAutorises - BTS SIO - Lycée De La Salle - Rennes-->
        <data>
          <reponse>2 autorisation(s) accordée(s) par neon.</reponse>
          <donnees>
             <lesUtilisateurs>
                <utilisateur>
                  <id>12</id>
                  <pseudo>oxygen</pseudo>
                  <adrMail>delasalle.sio.eleves@gmail.com</adrMail>
                  <numTel>44.55.66.77.88</numTel>
                  <niveau>1</niveau>
                  <dateCreation>2018-11-03 21:46:44</dateCreation>
                  <nbTraces>2</nbTraces>
                  <dateDerniereTrace>2018-01-19 13:08:48</dateDerniereTrace>
                </utilisateur>
                <utilisateur>
                  <id>13</id>
                  <pseudo>photon</pseudo>
                  <adrMail>delasalle.sio.eleves@gmail.com</adrMail>
                  <numTel>44.55.66.77.88</numTel>
                  <niveau>1</niveau>
                  <dateCreation>2018-11-03 21:46:44</dateCreation>
                  <nbTraces>0</nbTraces>
                </utilisateur>
             </lesUtilisateurs>
          </donnees>
        </data>
     */
    
    // crée une instance de DOMdocument (DOM : Document Object Model)
	$doc = new DOMDocument();
	
	// specifie la version et le type d'encodage
	$doc->version = '1.0';
	$doc->encoding = 'UTF-8';
	
	// crée un commentaire et l'encode en UTF-8
	$elt_commentaire = $doc->createComment('Service web GetLesUtilisateursQueJautorise  - BTS SIO - Lycée De La Salle - Rennes');
	// place ce commentaire à la racine du document XML
	$doc->appendChild($elt_commentaire);
	
	// crée l'élément 'data' à la racine du document XML
	$elt_data = $doc->createElement('data');
	$doc->appendChild($elt_data);
	
	// place l'élément 'reponse' dans l'élément 'data'
	$elt_reponse = $doc->createElement('reponse', $msg);
	$elt_data->appendChild($elt_reponse);
	
	// traitement des utilisateurs
	if (sizeof($lesUtilisateursAutorises) > 0) {
	    // place l'élément 'donnees' dans l'élément 'data'
	    $elt_donnees = $doc->createElement('donnees');
	    $elt_data->appendChild($elt_donnees);
	    
	    // place l'élément 'lesUtilisateurs' dans l'élément 'donnees'
	    $elt_lesUtilisateursAutorises = $doc->createElement('lesUtilisateurs');
	    $elt_donnees->appendChild($elt_lesUtilisateursAutorises);
	    
	    foreach ($lesUtilisateursAutorises as $unUtilisateurAutorise)
		{
		    // crée un élément vide 'utilisateur'
		    $elt_unUtilisateurAutorise = $doc->createElement('utilisateur');	    
		    // place l'élément 'utilisateur' dans l'élément 'lesUtilisateurs'
		    $elt_lesUtilisateursAutorises->appendChild($elt_unUtilisateurAutorise);
		
		    // crée les éléments enfants de l'élément 'utilisateur'
		    $elt_id         = $doc->createElement('id', $unUtilisateurAutorise->getId());
		    $elt_unUtilisateurAutorise->appendChild($elt_id);
		    
		    $elt_pseudo     = $doc->createElement('pseudo', $unUtilisateurAutorise->getPseudo());
		    $elt_unUtilisateurAutorise->appendChild($elt_pseudo);
		    
		    $elt_adrMail    = $doc->createElement('adrMail', $unUtilisateurAutorise->getAdrMail());
		    $elt_unUtilisateurAutorise->appendChild($elt_adrMail);
		    
		    $elt_numTel     = $doc->createElement('numTel', $unUtilisateurAutorise->getNumTel());
		    $elt_unUtilisateurAutorise->appendChild($elt_numTel);
		    
		    $elt_niveau     = $doc->createElement('niveau', $unUtilisateurAutorise->getNiveau());
		    $elt_unUtilisateurAutorise->appendChild($elt_niveau);
		    
		    $elt_dateCreation = $doc->createElement('dateCreation', $unUtilisateurAutorise->getDateCreation());
		    $elt_unUtilisateurAutorise->appendChild($elt_dateCreation);
		    
		    $elt_nbTraces   = $doc->createElement('nbTraces', $unUtilisateurAutorise->getNbTraces());
		    $elt_unUtilisateurAutorise->appendChild($elt_nbTraces);
		    
		    if ($unUtilisateurAutorise->getNbTraces() > 0)
		    {   $elt_dateDerniereTrace = $doc->createElement('dateDerniereTrace', $unUtilisateurAutorise->getDateDerniereTrace());
		    $elt_unUtilisateurAutorise->appendChild($elt_dateDerniereTrace);
		    }
		}
	}	
	// Mise en forme finale
	$doc->formatOutput = true;
	
	// renvoie le contenu XML
	return $doc->saveXML();
}

// ================================================================================================

// création du flux JSON en sortie
function creerFluxJSON($msg, $lesUtilisateursAutorises)
{
    /* Exemple de code JSON
        {
            "data": {
                "reponse": "2 autorisation(s) accord\u00e9e(s) par neon.",
                "donnees": {
                    "lesUtilisateurs": [
                        {
                            "id": "12",
                            "pseudo": "oxygen",
                            "adrMail": "delasalle.sio.eleves@gmail.com",
                            "numTel": "44.55.66.77.88",
                            "niveau": "1",
                            "dateCreation": "2018-11-03 21:46:44",
                            "nbTraces": "2",
                            "dateDerniereTrace": "2018-01-19 13:08:48"
                        },
                        {
                            "id": "13",
                            "pseudo": "photon",
                            "adrMail": "delasalle.sio.eleves@gmail.com",
                            "numTel": "44.55.66.77.88",
                            "niveau": "1",
                            "dateCreation": "2018-11-03 21:46:44",
                            "nbTraces": "0"
                        }
                    ]
                }
            }
        }
     */
    

    if (sizeof($lesUtilisateursAutorises) == 0) {
        // construction de l'élément "data"
        $elt_data = ["reponse" => $msg];
    }
    else {
        // construction d'un tableau contenant les utilisateurs
        $lesObjetsDuTableau = array();
        foreach ($lesUtilisateursAutorises as $unUtilisateurAutorise)
        {	// crée une ligne dans le tableau
            $unObjetUtilisateur = array();
            $unObjetUtilisateur["id"] = $unUtilisateurAutorise->getId();
            $unObjetUtilisateur["pseudo"] = $unUtilisateurAutorise->getPseudo();
            $unObjetUtilisateur["adrMail"] = $unUtilisateurAutorise->getAdrMail();
            $unObjetUtilisateur["numTel"] = $unUtilisateurAutorise->getNumTel();
            $unObjetUtilisateur["niveau"] = $unUtilisateurAutorise->getNiveau();
            $unObjetUtilisateur["dateCreation"] = $unUtilisateurAutorise->getDateCreation();
            $unObjetUtilisateur["nbTraces"] = $unUtilisateurAutorise->getNbTraces();
            if ($unUtilisateurAutorise->getNbTraces() > 0)
            {   $unObjetUtilisateur["dateDerniereTrace"] = $unUtilisateurAutorise->getDateDerniereTrace();
            }
            $lesObjetsDuTableau[] = $unObjetUtilisateur;
        }
        // construction de l'élément "lesUtilisateurs"
        $elt_utilisateur = ["lesUtilisateurs" => $lesObjetsDuTableau];
        
        // construction de l'élément "data"
        $elt_data = ["reponse" => $msg, "donnees" => $elt_utilisateur];
    }
    
    // construction de la racine
    $elt_racine = ["data" => $elt_data];
    
    // retourne le contenu JSON (l'option JSON_PRETTY_PRINT gère les sauts de ligne et l'indentation)
    return json_encode($elt_racine, JSON_PRETTY_PRINT);
}

// ================================================================================================
?>
