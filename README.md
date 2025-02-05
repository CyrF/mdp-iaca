# Serveur Web Mdp-Iaca

_Serveur offrant une interface web permettant la réinitialisation du mot de passe d’un élève, dans un environnement IACA._

![screenshot](miniature.png)


## Ressources matérielles

utilise un serveur linux sur lequel est installé docker.


## Configuration du serveur IACA

_Extrait de la doc IACA: https://www.iacasoft.fr/outils/TCPComIACA/index.htm_

> Afin de ne pas permettre à n'importe quel serveur Web de lire les mots de passe de IACA ou de les modifier, vous devez indiquer dans la base de registre du serveur IACA l'adresse IP du ou des serveurs Web autorisés.
>
> * Si votre serveur est en 64 bits : Placez-vous dans HKEY_LOCAL_MACHINE\SOFTWARE\WOW6432Node\Sayer\IACA
>
> Créez une valeur chaîne nommée IPMdpWeb et donnez comme valeur l'adresse IP du serveur Web à autoriser. Si vous avez plusieurs adresses à indiquer, séparez-les par un point-virgule. Ne mettez pas d'espace. Il est inutile de redémarrer le service IACA, la modification est prise en compte immédiatement.
>
> Si plus tard, vous ne voulez plus autoriser des serveurs Web, supprimez les adresses IP correspondantes.


### Configuration dans l'AD

Dans l'AD, ajouter un compte utilisateur, membre du groupe "opérateur de comptes". 
Il sera uniquement utilisé pour forcer le mot de passe a être changé a la prochaine connexion.

> todo: utiliser une delegation de droits plutot que le groupe pour que pingcastle soit content.

Le renseigner dans le fichier de config, au champ `AD_UserGest`. 


## Installation de l'application

Télécharge l’appli avec l’outil `git clone` dans le dossier `/docker/mdp-iaca` :
 
```bash
cd /docker
git clone https://github.com/CyrF/mdp-iaca.git
```

Nettoyage et création du fichier de config :

```bash
cd /docker/mdp-iaca
rm compose.override.yaml  # a supprimer, definit des variables pour le développement
chmod +x backend/test-config  # s'assure que ce script est bien executable

# creer une config à partir du fichier d'exemple
cp env.example .env
nano .env
docker compose config  # check du fichier de config, pour s'assurer qu'il lit correctement le .env

# copie le mot de passe de l'opérateur de comptes dans un fichier (docker secrets)
echo mon_mot_de_passe > password.txt
```

Crée les certificats SSL autosigné avec la commande `./create_cert.sh`, ou suivre [Obtenir un certificat signé par la CA](./certificat_signature_CA.md). 
Le container ne peut pas démarrer sans, docker affiche l'erreur : _invalid mount config, source path does not exist_

Démarrer l'appli :

```bash
docker compose up -d  # Démarre les containers en arrière-plan
docker compose exec -i backend ./test-config  # test des paramètres de connexion à l'ad
```


## Utilisation

L’utilisateur pointe son fureteur sur l’adresse https://srv-mdpiaca:4343.
Il choisit sa classe, puis clique sur le bouton reset en face du nom de l'élève.

Techniquement :
- La connexion s’effectue vers le serveur Mdp avec un chiffrement SSL.
- Le serveur Mdp obtient la liste d’élèves depuis le serveur IACA par le protocole LDAP.
- Lors du changement de mot de passe, le serveur Mdp l’envoie au serveur IACA via son API "TcpCom".

L'appli dispose de 3 niveaux d'accès (configurable):
- un mode élève. Il peut seulement changer son mot de passe, avec une bafouille pour expliquer comment en choisir un, et aussi voir son identifiant ENI.
- un mode prof. Ils peuvent réinitialiser le mdp d'un élève en 2-3 clics. + la page pour modifier le leur.
- un mode admin. Pour reconfigurer l'appli, voir le journal des actions utilisateurs, et lister les comptes élèves inactifs.


### Petit panda roux

Réglage à effectuer dans la GPO de configuration du navigateur Firefox.

* Pour ne pas passer par le proxy :

Entre **srv-mdpiaca** dans le champ **No proxy for**, sous
_Configuration ordinateur > modèle administration > mozilla firefox > Paramètres du proxy_

* Pour éviter l’alerte de sécurité lié au certificat auto-signé :

Se connecter à l’adresse https://srv-mdpiaca:4343, cliquer sur **afficher le certificat** pour l’enregistrer sous
`\\NomDuDomaine.local\NetLogon\srv-mdpiaca.pem`

Ensuite, sous _Configuration ordinateur > modèle d’administration > mozilla firefox > Certificates_ ouvre **Installation des Certificats**, coche **Activé**, clique sur **Afficher…** et renseigne la valeur :

```
\\NomDuDomaine.local\NetLogon\srv-mdpiaca.pem
```

## Notes

La page affiche "Le compte n'a été utilisé" alors que ce n'est pas le cas :

> L'appli interroge l'attribut "logoncount" dans l'active directory.
> Cet attribut n’est pas répliqué et est conservé sur chaque contrôleur de domaine du domaine.
> Pour obtenir une valeur précise pour le nombre total de tentatives d’ouverture de session réussies de l’utilisateur dans le domaine, chaque contrôleur de domaine du domaine doit être interrogé et la somme des valeurs doit être utilisée.
> *Source : https://docs.microsoft.com/fr-fr/windows/win32/adschema/a-logoncount*

