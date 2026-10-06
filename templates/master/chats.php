<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Чаты клиентов — Team</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/demo.css">
</head>
<body>
    <main class="demo-page">
        <header class="demo-header">
            <img src="/assets/logo.svg" alt="Team" width="154" height="90">
            <form method="post" action="/logout">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <button class="demo-button" type="submit">Выйти</button>
            </form>
        </header>

        <section class="demo-card">
            <p class="demo-card__label">Панель администратора</p>
            <h1 class="demo-card__title">Чаты клиентов</h1>
            <p class="demo-card__text">
                Вы вошли как <?= htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8') ?>.
                На следующем этапе здесь появится список клиентов и их переписка.
            </p>
        </section>
    </main>
</body>
</html>
