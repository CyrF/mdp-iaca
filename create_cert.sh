#!/bin/bash

mkdir ./nginx/certs

openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
	-keyout ./nginx/certs/nginx.key \
	-out ./nginx/certs/nginx.crt

