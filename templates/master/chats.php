<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Чаты клиентов — Team</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/demo.css">
    <script src="/assets/chat.js" defer></script>
</head>
<body>
    <main class="workspace workspace--admin">
        <aside class="sidebar sidebar--admin">
            <div class="sidebar__brand sidebar__brand--admin">
                <img src="/assets/logo.svg" alt="Team" width="154" height="90">
                <div>
                    <p>Рабочее место</p>
                    <h1>Клиенты</h1>
                </div>
            </div>

            <nav class="conversation-list" aria-label="Чаты клиентов">
                <?php if ($conversations === []): ?>
                    <p class="conversation-list__empty">Клиенты появятся здесь после создания приглашения.</p>
                <?php endif; ?>

                <?php foreach ($conversations as $item): ?>
                    <?php $isSelected = $conversation?->id === $item->id; ?>
                    <a
                        class="conversation-card<?= $isSelected ? ' conversation-card--selected' : '' ?>"
                        href="/admin/chats/<?= $item->id ?>"
                        <?= $isSelected ? 'aria-current="page"' : '' ?>
                    >
                        <span class="conversation-card__signal" aria-hidden="true"></span>
                        <span class="conversation-card__copy">
                            <strong><?= htmlspecialchars($item->clientName, ENT_QUOTES, 'UTF-8') ?></strong>
                            <span><?= htmlspecialchars($item->lastMessage ?? 'Сообщений пока нет', ENT_QUOTES, 'UTF-8') ?></span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="sidebar__account">
                <div>
                    <span>Администратор</span>
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
