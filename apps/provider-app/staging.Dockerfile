FROM laravelphp/vapor:php74

RUN apk add --update nodejs
RUN echo "NODE Version:" && node --version

RUN \
 curl -L https://download.newrelic.com/php_agent/release/newrelic-php5-9.15.0.293-linux-musl.tar.gz | tar -C /tmp -zx && \
   NR_INSTALL_USE_CP_NOT_LN=1 NR_INSTALL_SILENT=1 /tmp/newrelic-php5-*/newrelic-install install \
    && sed -i \
              -e "s/newrelic.enabled =.*/newrelic.enabled = 1/" \
              -e "s/newrelic.license =.*/newrelic.license = a2df9e2aa52acc304169f18420dc9c28FFFFNRAL/" \
              -e "s/newrelic.appname =.*/newrelic.appname = CPM Provider Staging/" \
              /usr/local/etc/php/conf.d/newrelic.ini

COPY ./php/conf.d/php_staging.ini /usr/local/etc/php/conf.d/php_staging.ini

COPY . /var/task