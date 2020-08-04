FROM webdevops/php-nginx:7.4

WORKDIR /app

RUN apt-get update && apt-get install -y \
        vim

RUN apt-get update && \
    apt-get install -y --no-install-recommends gnupg && \
    curl -sL https://deb.nodesource.com/setup_12.x | bash - && \
    apt-get update && \
    apt-get install -y --no-install-recommends nodejs && \
    curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - && \
    echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list && \
    apt-get update && \
    apt-get install -y --no-install-recommends yarn && \
    npm install -g npm

COPY installwkhtmltopdf.sh /usr/local/installwkhtmltopdf.sh
RUN cd /usr/local && mkdir -p bin && bash installwkhtmltopdf.sh /usr/local /usr/local && rm installwkhtmltopdf.sh

# Copy existing application directory contents
COPY . /app