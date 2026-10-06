#!/usr/bin/env bash

set -euo pipefail

find public src bootstrap config templates tests -name '*.php' -print0 \
  | xargs -0 -n1 php -l >/dev/null

bash tests/login-page-smoke.sh
php -d zend.assertions=1 -d assert.exception=1 tests/router-test.php
php -d zend.assertions=1 -d assert.exception=1 tests/database-test.php
php -d zend.assertions=1 -d assert.exception=1 tests/migrator-test.php
php -d zend.assertions=1 -d assert.exception=1 tests/auth-service-test.php

rendered_page="$({ REQUEST_METHOD=GET REQUEST_URI=/ php public/index.php; })"

grep -q 'Войдите по паролю' <<<"$rendered_page"
grep -q 'Оцифровка' <<<"$rendered_page"

echo "All tests passed"
