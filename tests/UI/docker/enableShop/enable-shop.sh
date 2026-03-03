#!/bin/bash
mysql -h prestashop-mysql -uprestashop -pprestashop prestashop -e "UPDATE ps_configuration SET value=1 WHERE name='PS_SHOP_ENABLE';"

if [ $? -eq 0 ]; then
  echo "✅ The shop is enabled"
else
  echo "❌ The shop is not enabled!"
fi
