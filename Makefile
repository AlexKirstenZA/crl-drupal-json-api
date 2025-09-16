start:
	ddev start

stop:
	ddev stop

remove:
	ddev delete

install:
	make start
	make composer-install
	make drupal-site-install
	make local-settings
	make login

composer-install:
	ddev composer install

drupal-site-install:
	ddev drush site:install minimal --existing-config --account-name=admin --account-pass=admin -y

local-settings:
	cp web/sites/example.settings.local.php web/sites/default/settings.local.php
	echo "Created \`web/sites/default/settings.local.php\` from \`web/sites/example.settings.local.php\`"

login:
	ddev drush uli

config-export:
	ddev drush cex

config-import:
	ddev drush cim -y
