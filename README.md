# plxNewMode

1. Filtre et redirige toutes vos potentielles page 404 vers la page d'erreur (404) de pluxml au lieu de la page HOME.
2. Sa configuration permet de rediriger vers la page d'accueil certaines pages 404 faisant du à une requete `$_GET` non reconnue nativement par PluXml mais que vous validez comme acceptable en page d'accueil
 _Cela peut-être une url (sponsorisée,amical,commerciale,..) du type `monSite.com/index.php?visiteur=vient-du-site-de-mon-pote` ou `monSite.com/visiteur=vient-du-site-de-mon-pote` qui sera accepté comme un accés valide à la page d'accueil 

