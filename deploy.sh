#!/bin/bash

# Navegue até o diretório do projeto
cd /home/juvhost1/api-sport-reserve.juvhost.com

# Execute comandos de deploy, por exemplo:
# Instalar dependências do Composer
composer install --no-dev --optimize-autoloader

# Executar migrações
php artisan migrate --force

# Limpar e cachear configuração
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Reiniciar o servidor web (exemplo para Nginx)
# sudo systemctl restart nginx

# Reiniciar o PHP-FPM (se necessário)
# sudo systemctl restart php7.4-fpm