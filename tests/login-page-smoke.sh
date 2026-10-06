#!/usr/bin/env bash

set -euo pipefail

test -f public/index.php
test -f public/assets/styles.css
test -f public/assets/logo.svg
test -f templates/auth/login.php

grep -q "bootstrap/app.php" public/index.php
grep -q "auth/login.php" src/Auth/AuthController.php
grep -q 'lang="ru"' templates/auth/login.php
grep -q 'type="password"' templates/auth/login.php
grep -q 'autocomplete="current-password"' templates/auth/login.php
grep -q 'action="/login"' templates/auth/login.php
grep -q 'name="_csrf"' templates/auth/login.php
grep -q 'Войдите по паролю' templates/auth/login.php
grep -q 'Оцифровка' templates/auth/login.php
grep -q 'font-family: "Inter"' public/assets/styles.css
grep -q '@media (max-width: 600px)' public/assets/styles.css
test -f templates/client/chat.php
test -f templates/master/chats.php
test -f templates/chat/thread.php
test -f public/assets/chat.js
grep -q 'data-message-form' templates/chat/thread.php
grep -q 'setTimeout' public/assets/chat.js

echo "Login page smoke test passed"
