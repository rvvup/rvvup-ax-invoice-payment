echo "Running against local volume"
cd /bitnami/magento/

/rvvup/scripts/rebuild-magento.sh
bin/magento config:set payment/rvvup_ax_integration/active 1
