ARG BASE_VERSION

FROM prestashop/base:$BASE_VERSION
LABEL maintainer="PrestaShop Core Team <coreteam@prestashop.com>"

ARG VERSION
ENV PS_VERSION $VERSION

# Get PrestaShop
ADD https://github.com/PrestaShop/PrestaShop/releases/download/${PS_VERSION}/prestashop_${PS_VERSION}.zip /tmp/prestashop.zip

# Extract
RUN mkdir -p /tmp/data-ps \
	&& unzip -q /tmp/prestashop.zip -d /tmp/data-ps/ \
	&& bash /tmp/ps-extractor.sh /tmp/data-ps \
	&& rm /tmp/prestashop.zip
