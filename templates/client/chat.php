<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Чат с мастером — Team</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/demo.css">
    <script src="/assets/chat.js" defer></script>
</head>
<body>
    <main class="workspace workspace--client">
        <aside class="sidebar sidebar--client">
            <div class="sidebar__brand">
                <img src="/assets/logo.svg" alt="Team" width="154" height="90">
                <p>Оцифровка и монтаж фильмов</p>
            </div>

            <div class="sidebar__client-copy">
                <p class="sidebar__label">Ваш личный чат</p>
                <h1>Всё о заказе — в одном месте.</h1>
                <p>Напишите мастеру и дождитесь ответа здесь.</p>
            </div>

            <div class="sidebar__account">
                <div>
                    <span>Вы вошли как</span>
                    <strong><?= htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
                <form method="post" action="/logout">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <button class="logout-button" type="submit">Выйти</button>
                </form>
            </div>
        </aside>

        <?php require dirname(__DIR__) . '/chat/thread.php'; ?>
    </main>
</body>
</html>
