# LDAP over SSL

Coté controleur de domaine, installer un certificat en suivant ce tuto :

https://www.it-connect.fr/active-directory-configurer-ldaps-certificat-autosigne/

Le certificat exporté dans le tuto est au format binaire, il faut le convertir :

```bash
# copier auparavant le fichier sur la machine linux
cd /docker/mdp-iaca
openssl x509 -pubkey -in Cert-LDAPS-contoso.local-Public.cer > ldaps-contoso-public.pub

# ...ou recuperation avec openssl et conversion
echo \
  | openssl s_client -connect lab-dc01.contoso.local:636 2>/dev/null \
  | openssl x509 -pubkey 2>/dev/null \
  > ldaps-contoso-public.pub
```

> le nom de fichier a utiliser est sous la forme `ldaps-NOMDEDOMAINE-public.pub`

## Modification de la configuration

Remplacer l'IP pour le FQDN du serveur dans le fichier de configuration ".env" :

```ini
# Pour LDAPS, il faut le protocole et le nom DNS complet qui a été mis dans le certificat
AD_ServerIP = "ldaps://lab-dc01.contoso.local"
```

Il faut ensuite indiquer à docker d'utiliser ce certificat.
Le fichier "compose.yaml" sera écrasé en cas de mise à jour, donc créer plutôt un "compose.ldaps.yaml" :

```yaml
services:  # Modif dans le container PHP
  backend:
    environment:
      # Force l'utilisation de LDAP over SSL, et indique le chemin du certificat
      - LDAPTLS_CACERT=/run/secrets/ldaps.cer
      - LDAPTLS_REQCERT=require
    secrets:
      - ldaps.cer

secrets:  # Monte le certificat pour qu'il soit accessible dans le conteneur
  ldaps.cer:
    file: ./ldaps-${AD_Domain?contoso}-public.pub
```

Redémarrer les containers docker, mais il faut maintenant lui faire charger le nouveau fichier :

```bash
docker compose -f compose.yaml -f compose.ldaps.yaml up -d
```

## Status

- [x] Le script test-config doit être modifié: il bloque sur port TCP_LDAP down.
- [x] "getaddrinfo failed Name does not resolve" : ajout dns
- [x] "x509 certificate routines::no certificate or crl found" : conversion de format
- [x] test de connexion OK en modifiant le script test-config
- [x] connexion en Web OK : le mode anonyme ne semble plus possible ? 
- [ ] Error Binding to LDAP: 80090308: LdapErr: DSID-0C09050E, comment: AcceptSecurityContext error, data 532, v4f7c

