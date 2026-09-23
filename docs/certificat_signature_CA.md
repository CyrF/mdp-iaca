# Générer un certificat SSL

Firefox est configuré pour s'appuyer sur le magasin de certificat windows,
donc on évite l'alerte de sécurité en signant le certificat serveur par l'authorité de certification du domaine.


## Clé privée

Sur le serveur, crée une nouvelle clé privée pour le serveur : `openssl genrsa -out nginx.key 2048`.
Copier ce fichier `nginx.key` dans `/docker/mdp-iaca/nginx/certs/`


## Demande de certificat CSR

Sur le serveur, crée un fichier de config `srv-mdpiaca.cnf`
```ini
[ req ]
default_bits       = 2048
distinguished_name = req_distinguished_name
req_extensions     = req_ext
prompt             = no

[ req_distinguished_name ]
C  = FR
ST = PAYS DE LA LOIRE
L  = NANTES
O  = LYCEE CONTOSO
CN = srv-mdpiaca

[ req_ext ]
subjectAltName = @alt_names

[ alt_names ]
DNS.1 = srv-mdpiaca
DNS.2 = srv-mdpiaca:4343
```

puis générer un CSR (Certificate Signing Request)
```
openssl req -new -key nginx.key -out srv-mdpiaca.csr -config srv-mdpiaca.cnf
```

> Vérifier le contenu de la CSR générée `openssl req -text -noout -verify -in srv-mdpiaca.csr`

Faire signer le certificat par la CA :
- aller sur https://srv-dc01/certsrv/certrqxt.asp
- coller le contenu de srv-mdpiaca.csr
- choisir le modèle "serveur web"
- télécharger la chaine de certificats, codé en base 64


## convertir PKCS -> CRT

Windows exporte les certificats au format PKCS. Nginx a besoin d'un couple clé publique/clé privée.
Utiliser la commande suivante pour la conversion (ici le fichier est nommé _ContosoCA_mdpiaca_pkchain.p7b_):

```
openssl pkcs7 -inform DER -print_certs -in ContosoCA_mdpiaca_pkchain.p7b -out nginx.crt
```

> les 2 fichiers KEY et CRT sont au format base64, avec l'entete `-----BEGIN CERTIFICATE-----`

Optionnellement, ajoute une clé éphémère au certificat (diffie-hellman) pour augmenter la sécurité :

```bash
curl https://ssl-config.mozilla.org/ffdhe2048.txt >> nginx.crt
```

Copier ce fichier `nginx.crt` dans `/docker/mdp-iaca/nginx/certs/`
