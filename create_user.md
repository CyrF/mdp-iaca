# creation de compte utilisateur ad avec php

```php
<?php
$ACCOUNTDISABLE = 2;
$NORMAL_ACCOUNT = 512;
$DONT_EXPIRE_PASSWORD = 65536;

function unicode_pwd($pass) {
	$newPassword = "\"$pass\"";
	$len = strlen($newPassword);
	$encodedPwd = '';
	for($i=0;$i<$len;$i++) {$encodedPwd .= "{$newPassword{$i}}\000";}
	return $encodedPwd;
}

$eleveid = 'exam02';
$elevepwd = unicode_pwd('1$uper_mot-DE-passe!');
$elevename = 'Jim NASTYK';
$profname = 'Agathe FEELING';
$dn = "CN=$eleveid,OU=EXAMEN,OU=Users,OU=Site par défaut,OU=IACA,DC=lyc-idn-44,DC=local";

$new_user = array(
	'cn'		=> $eleveid,
	'displayName'	=> $elevename,
	'sAMAccountName'=> $eleveid,
	'objectclass'	=> ['top', 'person', 'organizationalPerson', 'user'],
	'UserAccountControl' => $NORMAL_ACCOUNT,
	'unicodepwd'	=> $elevepwd,
	'description'	=> "Compte créé par $profname",
	'accountExpires'=> '133627032000000000', // dateconversion?
);

ldap_add($ldap_conn, $dn, $new_user);
```


## attributs ldap

obligatoire:
-------
cn (common name)	= exam02
instanceType		= 0x4 (write)
objectCategory		= CN=Person,CN=Schema,CN=Configuration,DC=lyc-idn-44,DC=local
objectClass		= top; person; organizationalPerson; user
objectSid		= S-1-5-21-...
ntsecuritydescriptor
sAMAccountName		= EXAM02

facultatifs:
------
accountExpires		= 133627032000000000 (13mai2024)
comment			= IACA;EXAMEN;;EXAMEN;¹“¢™²³‰ˆ;;5;;;;exam.xam02;4;0;Site par défaut;
compagny		= PUBLIC
description		= Compte créé par IACA
displayName 		= XAM02 EXAM
distinguishedName	= CN=EXAM02,OU=EXAMEN,OU=Users,OU=Site par défaut,OU=IACA,DC=lyc-idn-44,DC=local
givenName		= EXAM
whenChanged		= 1111...
whenCreated		= 1111...
pwdLastSet		= 1111...
sAMAccountType		= 805306368 (NORMAL_USER_ACCOUNT)
sAMAccountControl	= 0x10200 (NORMAL_ACCOUNT | DONT_EXPIRE_PASSWORD)
userPrincipalName	= EXAM02@0442765s.paysdelaloire.education

https://learn.microsoft.com/en-us/troubleshoot/windows-server/active-directory/useraccountcontrol-manipulate-account-properties
