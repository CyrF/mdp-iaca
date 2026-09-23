# docker sur serveur debian

## prérequis VM

télécharger l'iso de debian 13 minimale ("netinst") et la copier dans le dépôt ISO.

Créer une VM avec les paramètres ci-dessous, y associer l’iso de debian.

- Nom de VM : RNE-MDPIACA dans le dossier Hors_sauvegarde?
- Processeur : 1 vCPU
- Mémoire : 2 Go
- Espace disque : 16 Go dans la LUN0xxxx
- Réseau : VMXNET3 sur vlan serveur, @IP 10.xxx.xxx.xxx


## Installation du système

Au démarrage, choisir graphical install. Choisir la langue/pays/clavier.

- Nommer la machine : SRV-MDPIACA
- Choisir le mdp root : xxxxxxxxxxx
- Créer un compte local : lokaalbeheerder  / xxxxxxxxx
- partitionnement : assisté un disque entier / tout dans 1 partition
- proxy http : laisser vide
- sélection des logiciels : uniquement serveur ssh, utilitaires systèmes usuels

Le système redémarre. Se connecter en root, car la commande sudo n’est pas encore installée.


## configuration du système

installer les outils requis pour le script

```bash
apt update && apt dist-upgrade
apt install curl ca-certificates sudo open-vm-tools git
```


## Installation de docker

installer docker avec son script automatique :

```bash
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh
# ajouter l'utilisateur qui va administrer docker dans le groupe
usermod --append --groups docker  lokaalbeheerder 
```

créer le fichier **/etc/docker/daemon.json**, pour 
- S'assurer que docker n'utilise pas une plage d'ip en conflit avec le reste du réseau (par défaut 172.18.0.0/16)
- activer la rotation des logs

> [!WARNING]
> Les networks existants devront être supprimés/recréés pour utiliser le nouveau pool.

```json
{
  "bip" : "192.168.63.1/24",
  "default-address-pools" : [
    { "base" : "192.168.64.0/18", "size" : 25 },
    { "base" : "192.168.128.0/18", "size" : 27 }
  ],
  "log-driver": "json-file",
  "log-opts": {
    "max-size": "10m",
    "max-file": "3"
  }
}
```

> [!NOTE]
> un reboot est requis pour appliquer ces changements.
