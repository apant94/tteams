(() => {
    const chat = document.querySelector('[data-chat]');

    if (!(chat instanceof HTMLElement)) {
        return;
    }

    const conversationId = chat.dataset.conversationId;
    const currentUserId = Number(chat.dataset.currentUserId);
    const messageList = chat.querySelector('[data-message-list]');
    const emptyState = chat.querySelector('[data-empty-state]');
    const errorBox = chat.querySelector('[data-chat-error]');
    const pollingState = chat.querySelector('[data-polling-state]');
    const form = chat.querySelector('[data-message-form]');
    const input = chat.querySelector('[data-message-input]');
    const submit = chat.querySelector('[data-message-submit]');

    if (
        !conversationId
        || !(messageList instanceof HTMLElement)
        || !(form instanceof HTMLFormElement)
        || !(input instanceof HTMLTextAreaElement)
        || !(submit instanceof HTMLButtonElement)
    ) {
        return;
    }

    const renderedIds = new Set();
    let lastMessageId = 0;
    let pollingTimer;

    const formatTime = new Intl.DateTimeFormat('ru-RU', {
        hour: '2-digit',
        minute: '2-digit',
    });

    const showError = (message = '') => {
        if (!(errorBox instanceof HTMLElement)) {
            return;
        }

        errorBox.textContent = message;
        errorBox.hidden = message === '';
    };

    const setPollingState = (message) => {
        if (pollingState instanceof HTMLElement) {
            pollingState.textContent = message;
        }
    };

    const renderMessage = (message, forceScroll = false) => {
        if (renderedIds.has(message.id)) {
            return;
        }

        const shouldScroll = forceScroll
            || messageList.scrollHeight - messageList.scrollTop - messageList.clientHeight < 120;
        const item = document.createElement('article');
        const body = document.createElement('p');
        const time = document.createElement('time');
        const isOwn = Number(message.senderId) === currentUserId;

        item.className = `message${isOwn ? ' message--own' : ''}`;
        body.className = 'message__body';
        body.textContent = String(message.body);
        time.className = 'message__time';
        time.dateTime = String(message.createdAt);

        const createdAt = new Date(message.createdAt);
        time.textContent = Number.isNaN(createdAt.getTime())
            ? ''
            : formatTime.format(createdAt);

        item.append(body, time);
        messageList.append(item);
        renderedIds.add(message.id);
        lastMessageId = Math.max(lastMessageId, Number(message.id));

        if (emptyState instanceof HTMLElement) {
            emptyState.remove();
        }

        if (shouldScroll) {
            messageList.scrollTop = messageList.scrollHeight;
        }
    };

    const responseData = async (response) => {
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Не удалось выполнить запрос.');
        }

        return data;
    };

    const loadMessages = async (initial = false) => {
        try {
            setPollingState('Проверяем новые сообщения…');

            const response = await fetch(
                `/api/conversations/${conversationId}/messages?after_id=${lastMessageId}`,
                {
                    headers: { Accept: 'application/json' },
                    cache: 'no-store',
                },
            );
            const data = await responseData(response);

            data.messages.forEach((message) => renderMessage(message, initial));

            if (initial && data.messages.length === 0 && emptyState instanceof HTMLElement) {
                emptyState.textContent = 'Сообщений пока нет. Начните переписку.';
            }

            messageList.setAttribute('aria-busy', 'false');
            setPollingState('Обновляется автоматически');
            showError();
        } catch (error) {
            messageList.setAttribute('aria-busy', 'false');
            setPollingState('Нет соединения');
            showError(error instanceof Error ? error.message : 'Не удалось загрузить сообщения.');
        }
    };

    const schedulePolling = () => {
        window.clearTimeout(pollingTimer);
        pollingTimer = window.setTimeout(async () => {
            if (!document.hidden) {
                await loadMessages();
            }

            schedulePolling();
        }, 3000);
    };

    const resizeInput = () => {
        input.style.height = 'auto';
        input.style.height = `${Math.min(input.scrollHeight, 150)}px`;
    };

    input.addEventListener('input', resizeInput);
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            form.requestSubmit();
        }
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (input.value.trim() === '') {
            return;
        }

        submit.disabled = true;
        showError();

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: new FormData(form),
            });
            const data = await responseData(response);

            renderMessage(data.message, true);
            form.reset();
            resizeInput();
            input.focus();
        } catch (error) {
            showError(error instanceof Error ? error.message : 'Не удалось отправить сообщение.');
        } finally {
            submit.disabled = false;
        }
    });

    loadMessages(true).finally(schedulePolling);
})();
