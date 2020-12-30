FROM laravelphp/vapor:php74

RUN \
 curl -L https://download.newrelic.com/php_agent/release/newrelic-php5-9.15.0.293-linux-musl.tar.gz | tar -C /tmp -zx && \
   NR_INSTALL_USE_CP_NOT_LN=1 NR_INSTALL_SILENT=1 /tmp/newrelic-php5-*/newrelic-install install \
    && sed -i \
              -e "s/newrelic.enabled =.*/newrelic.enabled = ${NEW_RELIC_ENABLED}/" \
              -e "s/newrelic.license =.*/newrelic.license = ${NEW_RELIC_LICENSE_KEY}/" \
              -e "s/newrelic.appname =.*/newrelic.appname = ${NEW_RELIC_APP_NAME}/" \
              /usr/local/etc/php/conf.d/newrelic.ini

COPY ./php/conf.d/* /usr/local/etc/php/conf.d/

COPY . /var/task