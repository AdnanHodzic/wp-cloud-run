# wp-cloud-run (WordPress app) Dockerfile
ARG WP_VERSION=latest
FROM wordpress:$WP_VERSION

WORKDIR /var/www/html

# copy wp-content (plugins, themes, backups) and set ownership
COPY --chown=www-data:www-data ./wp-app/wp-content/ai1wm-backups /var/www/html/wp-content/ai1wm-backups
COPY --chown=www-data:www-data ./wp-app/wp-content/plugins /var/www/html/wp-content/plugins
COPY --chown=www-data:www-data ./wp-app/wp-content/themes /var/www/html/wp-content/themes

# copy configuration files (robots.txt)
COPY --chown=www-data:www-data ./wp-app/robots.txt /var/www/html/