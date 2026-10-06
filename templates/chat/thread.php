<?php if (!$conversation instanceof \Team\Chat\Conversation): ?>
    <section class="thread thread--empty">
        <div class="thread__empty-state">
            <span aria-hidden="true">●</span>
            <h2>Диалогов пока нет</h2>
            <p>Когда появится клиент, здесь можно будет начать переписку.</p>
        </div>
    </section>
<?php else: ?>
    <section
        class="thread"
        data-chat
        data-conversation-id="<?= $conversation->id ?>"
        data-current-user-id="<?= $user->id ?>"
    >
        <header class="thread__header">
            <div>
                <p class="thread__presence"><span aria-hidden="true"></span> Личный чат</p>
                <h2><?= htmlspecialchars($peerName, ENT_QUOTES, 'UTF-8') ?></h2>
            </div>
            <p class="thread__polling-state" data-polling-state>Обновляется автоматически</p>
        </header>

        <div class="message-list" data-message-list aria-live="polite" aria-busy="true">
            <p class="message-list__empty" data-empty-state>Загружаем переписку…</p>
        </div>

        <p class="thread__error" data-chat-error role="alert" hidden></p>

        <form
            class="composer"
            data-message-form
            action="/api/conversations/<?= $conversation->id ?>/messages"
            method="post"
        >
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <label class="visually-hidden" for="message-body">Сообщение</label>
            <textarea
                class="composer__input"
                id="message-body"
                name="body"
                rows="1"
                maxlength="10000"
                placeholder="Напишите сообщение"
                data-message-input
                required
            ></textarea>
            <button class="composer__submit" type="submit" data-message-submit>Отправить</button>
        </form>
    </section>
<?php endif; ?>
