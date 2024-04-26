# MDP-IACA adaptation pour l'installer avec docker

une vulnérabilité dans glibc peut affecter php
- [ ] partir d'une image alpine, qui n'utilise pas la libc
- [ ] tester avec les dernières versions de php
- [ ] voir comment installer les modules php requis
- [ ] execution nonroot

l'appli utilise un fichier de conf et une base sqlite pour les logs
- [ ] monter dans un volume la base sqlite
- [ ] monter dans un volume le fichier conf
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
