#!/bin/bash

# Navegue até o diretório do projeto
cd /home/juvhost1/api-sport-reserve.juvhost.com

# Obter alterações do repositorio
git pull origin main

# Execute comandos de deploy, por exemplo:
# Instalar dependências do Composer
/opt/composer/composer install --no-dev --optimize-autoloader

# Executar migrações
php artisan migrate --force

# Limpar e cachear configuração
# php artisan config:cache
# php artisan route:cache
# php artisan view:cache
php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan optimize:clear
# Reiniciar o servidor web (exemplo para Nginx)
# sudo systemctl restart nginx

# Reiniciar o PHP-FPM (se necessário)
# sudo systemctl restart php7.4-fpm

php artisan l5-swagger:generate
