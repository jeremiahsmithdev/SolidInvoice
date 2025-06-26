<<<<<<< HEAD
# Clear Cache
php bin/console cache:clear
php bin/console cache:clear --env=prod

php bin/console doctrine:cache:clear-metadata
php bin/console doctrine:cache:clear-query
php bin/console doctrine:cache:clear-result

# LOGS
# Clear cache
rm -rf /Users/admin/dev/Quoting/SolidInvoiceClone/var/cache/dev/twig/*

# Database
sqlite3 config/env/db/solidinvoice.db

