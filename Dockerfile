FROM dunglas/frankenphp

RUN install-php-extensions \
  pdo_pgsql \
  @composer

ARG USER=1000

RUN \
  useradd ${USER}; \
  setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp; \
  chown -R ${USER}:${USER} /data/caddy && chown -R ${USER}:${USER} /config/caddy; \
  mkdir /config/psysh && chown -R ${USER}:${USER} /config/psysh

USER ${USER}