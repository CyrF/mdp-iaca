# MDP-IACA adaptation pour l'installer avec docker

une vulnérabilité dans glibc peut affecter php
- [x] partir d'une image alpine, qui n'utilise pas la libc
- [x] tester avec les dernières versions de php; a l'air ok en mode demo
- [x] voir comment installer les modules php requis
- [ ] execution nonroot
- [x] ajouter un healcheck dans compose pour redemarrer le service nginx
- [ ] ajouter un healcheck dans compose pour redemarrer le service php

résultats des tests
- [x] 403 quand il y a pas de fichier dans l'url ? bug config nginx
- [ ] lenteur pour générer les pages: il existe un profileur valgrind/xdebug
- [x] probleme d'encodage des accents: utf8 dans la conf nginx
- [ ] php crash quand on spamme F5 (recomand augmenter pm.max_children)

l'appli utilise un fichier de conf et une base sqlite pour les logs
- [x] monter dans un volume la base sqlite
- [x] monter dans un volume le fichier conf
- [ ] ou convertir la conf en var. envirnmt.

apache est configuré en https
- [ ] gérer comment mettre le certificat
- [ ] exposer port 443/4343

gestion des comptes invités: 
- [ ] ajouter une page de création de comptes temporaires
- [ ] associer ces comptes dans "invités" ou "exam"
- [ ] attribuer une date d'expiration (7jours)
- [ ] formulaire demandant nom-prénom, liste déroulante ajax?
- [ ] loguer qui crée et utilise ces comptes

