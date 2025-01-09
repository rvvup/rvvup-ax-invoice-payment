echo "Running post-magento-setup.sh"

cd /bitnami/magento

/rvvup/scripts/rebuild-magento.sh

bin/magento config:set payment/rvvup_ax_integration/active 1
