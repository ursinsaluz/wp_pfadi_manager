#!/usr/bin/env bash

if [ $# -lt 3 ]; then
	echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-db-create]"
	exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4:-localhost}
WP_VERSION=${5:-latest}
SKIP_DB_CREATE=${6:-false}

if [ $SKIP_DB_CREATE == "false" ]; then
	mysqladmin -u ${DB_USER} -p${DB_PASS} -h ${DB_HOST} create ${DB_NAME} --force
fi

if [ $? -ne 0 ]; then
	echo "Could not create database."
	exit 1
fi

TMPDIR=${TMPDIR:-/tmp}
TESTS_DIR=${TMPDIR}/wordpress-tests-lib
WP_CORE_DIR=${TMPDIR}/wordpress

if [ ! -d $WP_CORE_DIR ]; then
	mkdir -p $WP_CORE_DIR
fi

if [ ! -f $WP_CORE_DIR/wp-settings.php ]; then
	if [ $WP_VERSION == "latest" ]; then
		curl -L https://wordpress.org/latest.tar.gz | tar xz -C $WP_CORE_DIR --strip-components=1
	else
		curl -L https://wordpress.org/wordpress-${WP_VERSION}.tar.gz | tar xz -C $WP_CORE_DIR --strip-components=1
	fi
fi

if [ ! -d $TESTS_DIR ]; then
	mkdir -p $TESTS_DIR
fi

if [ ! -f $TESTS_DIR/includes/functions.php ]; then
	echo "Downloading WordPress tests using Git..."
	# Create a temporary directory for the clone
	mkdir -p $TESTS_DIR/tmp-wp-develop
	
	# Clone with sparse checkout to minimize download size
	git clone --depth 1 --filter=blob:none --sparse https://github.com/WordPress/wordpress-develop.git $TESTS_DIR/tmp-wp-develop
	
	# Checkout only the required directories
	cd $TESTS_DIR/tmp-wp-develop
	git sparse-checkout set tests/phpunit/includes tests/phpunit/data
	
	# Move the directories to the expected location
	mv tests/phpunit/includes $TESTS_DIR/includes
	mv tests/phpunit/data $TESTS_DIR/data
	
	# Cleanup
	cd ..
	rm -rf $TESTS_DIR/tmp-wp-develop
fi

if [ ! -f $TESTS_DIR/wp-tests-config.php ]; then
	cat > $TESTS_DIR/wp-tests-config.php <<EOF
<?php
define( 'ABSPATH', '$WP_CORE_DIR/' );
define( 'DB_NAME', '$DB_NAME' );
define( 'DB_USER', '$DB_USER' );
define( 'DB_PASSWORD', '$DB_PASS' );
define( 'DB_HOST', '$DB_HOST' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

\$table_prefix  = 'wptests_';

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );

define( 'WP_PHP_BINARY', 'php' );

define( 'WPLANG', '' );
EOF
fi
