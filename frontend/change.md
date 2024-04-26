# Changelog:

- Ajout d'un champ pour rechercher un utilisateur par nom.
- Correction d'une erreur causée par des noms avec des apostrophes
- Ajout d'un bouton pour afficher tous les mots de passe provisoire sur la page
- Ajout d'un bouton pour générer des étiquettes imprimables
- Correction d'une erreur sur l'expiration de session
- Ajout de code pour forcer le changement de mot de passe à la prochaine ouverture de session
- Correction d'un bug d'affichage avec des caractères Unicode
- Affichage des classes sur 4 colonnes
- Ajout de la possibilité de masquer certaines classes
- Remplace les requêtes de type GET par POST
- Ajout permettant au prof de changer son propre mot de passe


## version 2
- refonte pour séparer html et php avec un moteur de template
- ajout d'un generateur de mdp inspiré de 'diceware' : éviscérer3lapinsDiaboliques?
- permettre aux eleves de modifier uniquement leur mdp
- ajout d'une page parametres pour configurer l'app
- ajout d'une journalisation
- bloquer l'acces des eleves a ajax