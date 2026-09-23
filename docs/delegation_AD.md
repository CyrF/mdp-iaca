# Delegation de controle

- [ ] tester la procédure
- [ ] chercher la commande powershell équivalente

Les bonne pratiques :
- ne pas attribuer des permissions directement sur un utilisateur
- ne pas utiliser les groupes prédéfinis (trop permissifs)
- les admins doivent être dans une uo séparée, pour éviter des attributions accidentelles
- auditer régulièrement l'annuaire
- l'anssi recommande de placer les users dans un groupe global, placer ce GG dans un groupe domaine local, et enfin d'attribuer des droits à ce GDL

## mdpiaca

créer un groupe de sécurité [globale/domaine/local]? nommé "GS_reset_mdp"
```powershell
$GroupName = "GS_reset_mdp"
New-ADGroup $GroupName -Path "OU=Groupes,DC=Contoso,DC=local" -GroupScope Global
Add-AdGroupMember -Identity $GroupName -Members "Cyril"
```
se placer sur l'uo ELEVES, clic-droit délégation de controle
choisir le groupe GS_reset_mdp
choisir "Créer une tâche personnalisée à déléguer"
choisir "Objets utilisateurs"
cocher "Afficher les autorisations spécifiques aux propriétés"
cocher "lire/écrire LockoutTime" (déverrouillage de compte)
cocher "lire/écrire pwdLastSet" (Expiration de mot de passe)
cocher "réinitialiser le mot de passe"

## fog

créer un groupe de sécurité [globale/domaine/local]? nommé "GS_joindre_machine"
se placer sur l'uo Computers, clic-droit délégation de controle
choisir le groupe GS_joindre_machine
choisir "Créer une tâche personnalisée à déléguer"
choisir "Objets ordinateurs" + cocher "Créer les objets selectionnés dans ce dossier"

# autre

- [ ] delegation de controle dans l'ad: comment les lister/revoquer ?
- [ ] modifier test-config pour qu'il découvre ses parametres.
- [o] possibilité d'utiliser un compte de service gmsa ? faut joindre au domaine, aws a un outil
- [o] utiliser kerberos ? chiant, faut installer un client linux et le joindre au domaine
- [ ] docker swarm: si coupure du master ?
