# Serveur Web Mdp-Iaca

_Serveur offrant une interface web permettant la réinitialisation du mot de passe d’un élève, dans un environnement IACA._

## Ressources matérielles

L'application n'a besoin que d'un apache et PHP avec le module LDAP d'activé.
Aucune info personnelle n'y est stockée, elle sert juste de relais.

Intégrer un PHP sur IIS avait l'air casse-gueule, donc j'ai monté un serveur web dédié.

* Processeur : 1 vCPU
* Mémoire : 2 Go
* Espace disque : 16 Go dans la LUN___ ( 4 Go minimum )
* Réseau : VMXNET3 sur vlan serveur, @IP 10.1___.___.___
* Système OS : Debian 11 sans GUI

Testé sur un PHP 7.1 et 7.4, avec les extensions LDAP et SQLITE d'activées.

## Installation du système

Créer une VM **RNE-MDPIACA**, y associer l’iso de debian. Au démarrage, choisir graphical install. Choisir la langue/pays/clavier.

* Nommer la machine : SRV-MDPIACA
* Choisir le mdp root : _________
* Créer un compte local : locadm / _________
* partitionnement : assisté un disque entier / tout dans 1 partition
* proxy http : <nowiki>http://172.30.137.29:3128/</nowiki>
* sélection des logiciels : serveur ssh, utilitaires systèmes usuels

Le système redémarre. Se connecter en root, car la commande sudo n’est pas encore installée.

Ajouter ces lignes dans `/etc/network/interfaces` et commenter la ligne finissant par DHCP :

```bash
 #iface ens160 inet dhcp
 #fc2020-05 configure l'ip manuellement (! ens160 peut être différent)
 iface ens160 inet static
   address 10.1___.___.___/26
   gateway 10.1___.___.126
```

On renseigne le proxy pour les différents outils du système dans le fichier `/etc/environment`

> Note : L’installeur a déjà paramétré le proxy pour les mises à jour dans `/etc/apt/apt.conf`

```bash
 #fc2020-03 Parametres proxy lycees
 http_proxy=<nowiki>http://172.30.137.29:3128</nowiki>
 https_proxy=<nowiki>http://172.30.137.29:3128</nowiki>
 no_proxy=.__<NomDuDomaine>__.local
```

### Installation des services

On **met à jour** le système, installe les **vmware-tools**, un serveur web **apache**, un **php** ainsi que son module ldap, et **git** pour récupérer mdp-iaca

```bash
apt update && apt dist-upgrade
apt install open-vm-tools sudo apache2 php libapache2-mod-php php-ldap git curl php-sqlite3
```

Modifie le fichier `/etc/apache2/apache.conf` pour interdire le parcours des sous-répertoires et autoriser apache à utiliser un dossier autre que `/var/www/html`

```apache
<Directory /opt/webapp-mdp-iaca/>
  Options -Indexes
  AllowOverride None
  Require all granted
</Directory>
```

Modifie le fichier `` pour déplacer le dossier contenant l'application

```
<VirtualHost *:80>
           DocumentRoot /opt/webapp-mdp-iaca
```

Redémarrer pour prendre en compte les changements.

## Configuration du serveur IACA

_Extrait de la doc IACA: https://www.iacasoft.fr/outils/TCPComIACA/index.htm_

Afin de ne pas permettre à n'importe quel serveur Web de lire les mots de passe de IACA ou de les modifier, vous devez indiquer dans la base de registre du serveur IACA l'adresse IP du ou des serveurs Web autorisés.

* Si votre serveur est en 32 bits : Placez-vous dans HKEY_LOCAL_MACHINE\SOFTWARE\Sayer\IACA
* Si votre serveur est en 64 bits : Placez-vous dans HKEY_LOCAL_MACHINE\SOFTWARE\WOW6432Node\Sayer\IACA

Créez une valeur chaîne nommée IPMdpWeb et donnez comme valeur l'adresse IP du serveur Web à autoriser. Si vous avez plusieurs adresses à indiquer, séparez-les par un point-virgule. Ne mettez pas d'espace. Il est inutile de redémarrer le service IACA, la modification est prise en compte immédiatement.

Si plus tard, vous ne voulez plus autoriser des serveurs Web, supprimez les adresses IP correspondantes.


### Configuration dans l'AD

Dans l'AD, ajouter un compte utilisateur, nommé __________ , membre du groupe "opérateur de comptes".

> todo: utiliser une delegation de droits plutot le groupe pour que pingcastle soit content.

Le renseigner dans le fichier de config, au champ `AD_UserGest`. Il sera utilisé par l'appli pour forcer le mot de passe a être changé a la prochaine connexion.

## Installation de l'application

Télécharge l’appli avec l’outil git

```bash
cd /opt
git clone http://forum.crpdl.fr/git/webapp-mdp-iaca.git
chown www-data:www-data /opt/webapp-mdp-iaca -R
```

En première installation, le fichier `./inc/user_config.php` n'existe pas. Il faut se connecter à l'interface web en `admin` avec le mdp `admin` pour le créer via la page de configuration.

Pour mettre à jour l’appli, utilise

```bash
cd /opt/webapp-mdp-iaca
git pull
```

A partir de là, l'application est déjà utilisable via l'adresse http://srv-mdpiaca/

Le paragraphe suivant ajoute une connexion chiffrée entre le serveur et l'utilisateur (l'outil IACA communique en clair, mais les échanges restent confinés dans le vlan serveur)

## Utilisation

L’utilisateur pointe son fureteur sur l’adresse https://srv-mdpiaca:4343.
Il choisit sa classe, puis clique sur le bouton reset en face du nom de l'élève.

Techniquement :
- La connexion s’effectue vers le serveur Mdp avec un chiffrement SSL.
- Le serveur Mdp obtient la liste d’élèves depuis le serveur IACA par le protocole LDAP.
- Lors du changement de mot de passe, le serveur Mdp l’envoie au serveur IACA via la commande TcpCom.

L'appli dispose de 3 niveaux d'accès (configurable):
- un mode élève. Il peut seulement changer son mot de passe, avec une bafouille pour expliquer comment en choisir un, et aussi voir son identifiant ENI.
- un mode prof. Ils peuvent réinitialiser le mdp d'un élève en 2-3 clics. + la page pour modifier le leur.
- un mode amd. Pour reconfigurer l'appli, voir le journal des actions utilisateurs, et lister les comptes élèves inactifs.

## Configuration de SSL

### Certificat SSL

Génère une autorité racine (ou bien utilise http://srv-dc1/certsrv)

```bash
mkdir /opt/pki
openssl req -newkey rsa:2048 -nodes -keyform PEM \
         -keyout /opt/pki/selfsigned-ca.key -x509 -days 3650 \
         -outform PEM -out /opt/pki/selfsigned-ca.crt
```

Crée la clé privée du serveur

```bash
openssl genrsa -out /etc/ssl/private/apache-selfsign.key 2048
```

Crée la requête CSR pour apache et la fait signer par l’autorité

```bash
openssl req -new -out selfsigned.csr \
 -key /etc/ssl/private/apache-selfsign.key

openssl x509 -req -in selfsigned.csr -set_serial 100 -days 3650 \
 -CA /opt/pki/selfsigned-ca.crt -CAkey /opt/pki/selfsigned-ca.key \
 -outform PEM -out /etc/ssl/certs/apache-selfsign.crt
```

Ajoute une clé éphémère au certificat
```
cd /etc/ssl/certs/
curl https://ssl-config.mozilla.org/ffdhe2048.txt >> apache-selfsign.crt
```

### Apache

Crée le fichier `/etc/apache2/sites-available/vhost-ssl.conf` pour définir les certificats à utiliser et l’emplacement de l’application.

```apache
# generated 2022-01-06, Mozilla Guideline v5.6
# this configuration requires mod_ssl
<VirtualHost *:4343>
           SSLEngine on
           DocumentRoot /opt/webapp-mdp-iaca

           #cd /etc/ssl/certs/
           #curl https://ssl-config.mozilla.org/ffdhe2048.txt >> apache-selfsign.crt
           SSLCertificateFile /etc/ssl/certs/apache-selfsign.crt
           SSLCertificateKeyFile /etc/ssl/private/apache-selfsign.key

           # enable HTTP/2, if available
           Protocols h2 http/1.1
</VirtualHost>
```

Crée le fichier `/etc/apache2/conf-available/parametres-ssl.conf`

> Attention, la ligne 'SSLCipherSuite' ne doit pas être tronquée, c'est seulement ici pour l'affichage.

```apache
# generated 2022-01-06, Mozilla Guideline v5.6
# intermediate configuration
SSLProtocol             all -SSLv3 -TLSv1 -TLSv1.1
SSLCipherSuite          ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:
	ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:
	ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:
	DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384
SSLHonorCipherOrder     off
SSLSessionTickets       off

```

Modifie le fichier `/etc/apache2/ports.conf` car les ACLs ne permettent pas la connexion profs vers serveurs sur le port SSL 443.

```apache
<ifmodule ssl_module>
           Listen 4343
         </ifmodule>
```

Modifie ces lignes dans `/etc/apache2/sites-available/000-default.conf` pour rediriger si http

```apache
<VirtualHost *:80>
           RewriteEngine On
           RewriteCond %{HTTPS} off
           RewriteRule ^(.*)$ https://%{HTTP_HOST}:4343%{REQUEST_URI} [R=301,L]
```

Active la conf avec les commandes

```bash
# modules apache
a2enmod ssl
a2enmod rewrite

# fichier de configuration
a2enconf parametres-ssl
a2ensite vhost-ssl

systemctl restart apache2
```

Vérifie le fonctionnement

```bash
openssl s_client -connect host.domain.com:4343&
```

### Petit panda roux

Réglage à effectuer dans la GPO de configuration du navigateur Firefox.

* Pour ne pas passer par le proxy :

Entre **srv-mdpiaca** dans le champ **No proxy for**, sous
_Configuration ordinateur > modèle administration > mozilla firefox > Paramètres du proxy_

* Pour éviter l’alerte de sécurité lié au certificat auto-signé :

Se connecter à l’adresse https://srv-mdpiaca:4343, cliquer sur **afficher le certificat** pour l’enregistrer sous
_\\___<NomDuDomaine>___.local\NetLogon\srv-mdpiaca.pem._

Ensuite, sous _Configuration ordinateur > modèle d’administration > mozilla firefox > Certificates_ ouvre **Installation des Certificats**, coche **Activé**, clique sur **Afficher…** et renseigne la valeur :

```
\\___<NomDuDomaine>____.local\NetLogon\srv-mdpiaca.pem
```

## Notes

La page affiche "Le compte n'a été utilisé" alors que ce n'est pas le cas :

> L'appli interroge l'attribut "logoncount" dans l'active directory.
> Cet attribut n’est pas répliqué et est conservé sur chaque contrôleur de domaine du domaine.
> Pour obtenir une valeur précise pour le nombre total de tentatives d’ouverture de session réussies de l’utilisateur dans le domaine, chaque contrôleur de domaine du domaine doit être interrogé et la somme des valeurs doit être utilisée.
> *Source : https://docs.microsoft.com/fr-fr/windows/win32/adschema/a-logoncount*
