FROM prestashop/base:7.1-apache
LABEL maintainer="PrestaShop Core Team <coreteam@prestashop.com>"

ARG VERSION
ENV PS_VERSION $VERSION

# Get PrestaShop
ADD https://www.prestashop.com/download/old/prestashop_${PS_VERSION}.zip /tmp/prestashop.zip

# Extract
RUN mkdir -p /tmp/data-ps \
	&& unzip -q /tmp/prestashop.zip -d /tmp/data-ps/ \
	&& bash /tmp/ps-extractor.sh /tmp/data-ps \
	&& rm /tmp/prestashop.zip


# Define _PS_ADMIN_DIR_
RUN echo "<?php define('_PS_ADMIN_DIR_', 'admin-dev');" >> /var/www/html/config/defines_custom.inc.php

RUN chown www-data:www-data -R /var/www/html
