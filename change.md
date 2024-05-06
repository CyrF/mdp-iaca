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


## version 2.dev: adaptation pour l'installer avec docker

une vulnérabilité découverte en avril 2024 dans glibc peut affecter php
- [x] partir d'une image alpine, qui n'utilise pas la glibc, mais "musl"
- [x] tester avec les dernières versions de php; a l'air ok en mode demo
- [x] installer les modules php requis: via un script communauté, cité dans la doc officielle
- [x] execution nonroot: déjà le cas pour php, nok pour nginx 
- [x] ajouter un healcheck dans compose pour redemarrer le service nginx
- [ ] ajouter un healcheck dans compose pour redemarrer le service php

>https://stackoverflow.com/questions/47088261/restarting-an-unhealthy-docker-container-based-on-healthcheck
Unhealthy docker containers may be restarted with simple crontab rule:
`* * * * * docker ps -f health=unhealthy --format "docker restart {{.ID}}" | sh`
Probably safer to do `docker ps -q -f health=unhealthy | xargs docker restart`

l'appli utilise un fichier de conf et une base sqlite pour les logs
- [x] monter dans un volume la base sqlite
- [x] monter dans un volume le fichier conf
- [ ] ou convertir la conf en var. envirnmt.

apache est configuré en https
- [x] gérer comment mettre le certificat
- [x] exposer port 443/4343

résultats des tests
- [x] 403 quand il y a pas de fichier dans l'url ? bug config nginx
- [ ] lenteur pour générer les pages: disparait si connecté au net (doit y a avoir un timeout)
>trouvé c'est gethostbyaddr qui provoque ce ralenti
>a voir, il existe un profileur valgrind/xdebug
- [x] probleme d'encodage des accents: utf8 dans la conf nginx
- [ ] php crash quand on spamme F5 (recomand augmenter pm.max_children)


## version 2.next: gestion des comptes invités

- [x] ajouter une page de création de comptes temporaires
- [ ] associer ces comptes dans "invités" ou "exam"
- [ ] attribuer une date d'expiration (7jours)
- [ ] formulaire demandant nom-prénom, liste déroulante ajax?
- [ ] loguer qui crée et utilise ces comptes
- [x] recharge la page une fois expirée

