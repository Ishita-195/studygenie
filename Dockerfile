# StudyGenie — single-container deploy (Apache/PHP + Flask AI service + MariaDB)
#
# Everything runs in ONE container so the PHP UI and the Python service can share
# the /var/www/html/uploads folder (the Python side reads uploaded files from disk
# by name), exactly like the original XAMPP setup. supervisord keeps all three
# processes alive; Apache is the only one exposed, on $PORT.
FROM php:8.2-apache

ENV DEBIAN_FRONTEND=noninteractive \
    PYTHONUNBUFFERED=1

# ── System packages: Python, MariaDB, supervisor ─────────────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
        python3 python3-venv \
        mariadb-server mariadb-client \
        supervisor \
    && rm -rf /var/lib/apt/lists/*

# ── PHP extensions the app needs ─────────────────────────────────────────────
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# ── Python dependencies in an isolated venv ──────────────────────────────────
# A venv keeps pip away from Debian's system-managed packages (e.g.
# typing_extensions, which ships without pip metadata). Installing into the
# system Python otherwise fails with "uninstall-no-record-file".
ENV VIRTUAL_ENV=/opt/venv
RUN python3 -m venv "$VIRTUAL_ENV"
ENV PATH="$VIRTUAL_ENV/bin:$PATH"
COPY python/requirements-deploy.txt /tmp/requirements.txt
RUN pip install --no-cache-dir --upgrade pip \
    && pip install --no-cache-dir -r /tmp/requirements.txt

# ── Application code ─────────────────────────────────────────────────────────
COPY . /var/www/html/
RUN mkdir -p /var/www/html/uploads /var/www/html/uploads/.cache \
    && chown -R www-data:www-data /var/www/html/uploads

# ── Apache + MariaDB + supervisor configuration ──────────────────────────────
RUN a2enmod rewrite headers
COPY docker/php-uploads.ini     /usr/local/etc/php/conf.d/uploads.ini
COPY docker/apache-vhost.conf   /etc/apache2/sites-available/000-default.conf
COPY docker/mariadb-lowmem.cnf  /etc/mysql/mariadb.conf.d/99-lowmem.cnf
COPY docker/mpm-lowmem.conf     /etc/apache2/mods-available/mpm_prefork.conf
COPY docker/supervisord.conf    /etc/supervisor/conf.d/studygenie.conf
COPY docker/entrypoint.sh       /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Render/most PaaS inject $PORT; default to 10000 for local runs.
ENV PORT=10000
EXPOSE 10000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
