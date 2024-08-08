echo "Running setup.sh"
/rvvup/scripts/configure-base-store.sh;
/rvvup/scripts/setup-rvvup.sh;
/rvvup/scripts/setup-upgrade.sh;
/rvvup/scripts/configure-plugins.sh;
/rvvup/scripts/fix-perms.sh;
/opt/bitnami/scripts/magento/run.sh;
