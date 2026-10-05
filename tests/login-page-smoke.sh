#!/usr/bin/env bash

set -euo pipefail

test -f public/index.php
test -f public/assets/styles.css
test -f public/assets/logo.svg
test -f templates/auth/login.php

grep -q "templates/auth/login.php" public/index.php
grep -q 'lang="ru"' templates/auth/login.php
grep -q 'type="password"' templates/auth/login.php
grep -q 'autocomplete="current-password"' templates/auth/login.php
grep -q 'Войдите по паролю' templates/auth/login.php
grep -q 'Оцифровка' templates/auth/login.php
grep -q 'font-family: "Inter"' public/assets/styles.css
grep -q '@media (max-width: 600px)' public/assets/styles.css

echo "Login page smoke test passed"
