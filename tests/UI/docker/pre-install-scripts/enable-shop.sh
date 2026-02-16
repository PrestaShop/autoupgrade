#!/bin/bash
apt-get update
apt-get install -y --no-install-recommends xmlstarlet
xmlstarlet ed --inplace -u '/entity_configuration/entities/configuration[@id="PS_SHOP_ENABLE"]/value' -v 1 /var/www/html/install/data/xml/configuration.xml
