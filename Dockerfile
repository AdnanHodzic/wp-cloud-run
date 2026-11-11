# wp-cloud-run (WordPress app) Dockerfile
ARG WP_VERSION=latest
FROM wordpress:$WP_VERSION

WORKDIR /var/www/html

# copy wp-content (plugins, themes, backups) and set ownership
COPY --chown=www-data:www-data ./wp-app/wp-content/ai1wm-backups /var/www/html/wp-content/ai1wm-backups
COPY --chown=www-data:www-data ./wp-app/wp-content/plugins /var/www/html/wp-content/plugins
COPY --chown=www-data:www-data ./wp-app/wp-content/themes /var/www/html/wp-content/themes
COPY --chown=www-data:www-data ./wp-app/wp-content/mu-plugins /var/www/html/wp-content/mu-plugins

# copy configuration files (robots.txt, uploads.ini)
COPY --chown=www-data:www-data ./wp-app/robots.txt /var/www/html/
COPY ./wp-app/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# apply WordPress secret keys and salts if .cloud-run-salts.env is present and APPLY_SALTS=true
ARG APPLY_SALTS=true
COPY .cloud-run-salts.en[v] /tmp/
RUN if [ "$APPLY_SALTS" = "true" ] && [ -s /tmp/.cloud-run-salts.env ]; then \
      php -r ' \
        $cfg = file_get_contents("/usr/src/wordpress/wp-config-docker.php"); \
        foreach (file("/tmp/.cloud-run-salts.env", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) { \
          if ($line[0] === "#" || strpos($line, "=") === false) continue; \
          [$k, $v] = explode("=", $line, 2); \
          $k = trim($k); $v = trim($v); \
          if ($v === "" || $v === "change-me") continue; \
          $cfg = preg_replace( \
            "/getenv_docker\(\x27" . preg_quote($k, "/") . "\x27,\s*\x27put your unique phrase here\x27\)/", \
            "getenv_docker(\x27" . $k . "\x27, " . var_export($v, true) . ")", \
            $cfg \
          ); \
        } \
        file_put_contents("/usr/src/wordpress/wp-config-docker.php", $cfg); \
      ' && php -l /usr/src/wordpress/wp-config-docker.php >/dev/null; \
    fi && rm -f /tmp/.cloud-run-salts.env
