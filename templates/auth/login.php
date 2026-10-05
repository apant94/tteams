<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Вход в личный чат с мастером по оцифровке и монтажу фильмов">
    <title>Вход — Team</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/styles.css">
</head>
<body>
    <main class="login-page">
        <section class="login-intro" aria-labelledby="intro-title">
            <img class="login-intro__logo" src="/assets/logo.svg" alt="Team" width="231" height="135">

            <div class="login-intro__copy">
                <h1 class="login-intro__title" id="intro-title">
                    Оцифровка<br>
                    и монтаж фильмов<br>
                    в одном чате
                </h1>

                <p class="login-intro__description">
                    Переписка с мастером, статус заказа и готовые файлы. Всё здесь, без мессенджеров, которые тормозят.
                </p>
            </div>
        </section>

        <section class="login-panel" aria-labelledby="login-title">
            <form class="login-form" method="post" action="">
                <div class="login-form__fields">
                    <h2 class="login-form__title" id="login-title">Войдите по паролю</h2>

                    <label class="visually-hidden" for="password">Пароль</label>
                    <input
                        class="login-form__input"
                        id="password"
                        name="password"
                        type="password"
                        placeholder="Введите пароль"
                        autocomplete="current-password"
                        required
                        autofocus
                    >
                </div>

                <button class="login-form__submit" type="submit">Войти</button>
            </form>
        </section>
    </main>
</body>
</html>
