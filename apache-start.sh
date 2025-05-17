#!/bin/bash

# Default ke 8080 jika PORT tidak tersedia
PORT=${PORT:-8080}

# Ubah Apache listen port
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/" /etc/apache2/sites-enabled/000-default.conf

# Start Apache
apache2-foreground
