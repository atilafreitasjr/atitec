const STORAGE_KEY = 'atitec_atendimento_ia_token';

const STATUS_LABELS = {
  bot: 'Assistente virtual · online',
  encaminhado: 'Dúvida encaminhada · aguardando equipe',
  em_atendimento: 'Equipe ATITEC atendendo',
  respondido: 'Resposta da equipe ATITEC',
  encerrado: 'Atendimento encerrado',
};

function initAtendimentoIa() {
  const root = document.querySelector('[data-atendimento-ia]');

  if (!root) return;

  const panel = root.querySelector('[data-ia-panel]');
  const launcher = root.querySelector('[data-ia-launcher]');
  const closeButton = root.querySelector('[data-ia-close]');
  const idleAvatar = root.querySelector('[data-ia-avatar-idle]');
  const activeAvatar = root.querySelector('[data-ia-avatar-active]');
  const ping = root.querySelector('[data-ia-ping]');
  const messagesEl = root.querySelector('[data-ia-messages]');
  const typingEl = root.querySelector('[data-ia-typing]');
  const statusEl = root.querySelector('[data-ia-status-label]');
  const form = root.querySelector('[data-ia-form]');
  const input = root.querySelector('[data-ia-input]');
  const sendButton = root.querySelector('[data-ia-send]');
  const contactEl = root.querySelector('[data-ia-contact]');
  const contactIntro = root.querySelector('[data-ia-contact-intro]');
  const contactForm = root.querySelector('[data-ia-contact-form]');
  const contactSubmit = root.querySelector('[data-ia-contact-submit]');
  const contactSkip = root.querySelector('[data-ia-contact-skip]');

  if (!panel || !launcher || !form || !input) return;

  const urls = {
    message: root.dataset.messageUrl,
    contact: root.dataset.contactUrl,
    conversation: root.dataset.conversationBase,
  };

  const config = {
    welcome: root.dataset.welcome || 'Olá! Como posso ajudar?',
    quickReplies: parseJson(root.dataset.quickReplies, []),
    prefill: parseJson(root.dataset.prefill, {}),
    maxLength: Number.parseInt(root.dataset.maxLength || '2000', 10),
  };

  const state = {
    token: storageGet(),
    open: false,
    busy: false,
    contactBusy: false,
    status: 'bot',
    renderedIds: new Set(),
    pollTimer: null,
  };

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  function parseJson(value, fallback) {
    try {
      const parsed = JSON.parse(value || '');

      return parsed ?? fallback;
    } catch {
      return fallback;
    }
  }

  function storageGet() {
    try {
      return window.localStorage.getItem(STORAGE_KEY);
    } catch {
      return null;
    }
  }

  function storageSet(token) {
    try {
      window.localStorage.setItem(STORAGE_KEY, token);
    } catch {
      // Navegação privada pode bloquear o armazenamento; a conversa segue na sessão.
    }
  }

  function storageClear() {
    try {
      window.localStorage.removeItem(STORAGE_KEY);
    } catch {
      // ignora
    }
  }

  function scrollToBottom() {
    window.requestAnimationFrame(() => {
      messagesEl.scrollTop = messagesEl.scrollHeight;
    });
  }

  function setTyping(visible) {
    typingEl?.classList.toggle('hidden', !visible);
    typingEl?.classList.toggle('flex', visible);
    scrollToBottom();
  }

  function setBusy(visible) {
    state.busy = visible;
    sendButton && (sendButton.disabled = visible);
    input.disabled = visible;
  }

  function setOpen(next) {
    state.open = next;
    panel.classList.toggle('hidden', !next);
    launcher.setAttribute('aria-expanded', String(next));
    idleAvatar?.classList.toggle('hidden', next);
    activeAvatar?.classList.toggle('hidden', !next);
    ping?.classList.toggle('hidden', next);

    if (next) {
      syncConversation();
      window.setTimeout(() => input.focus(), 80);
    }
  }

  function updateStatus(status, label) {
    state.status = status || 'bot';

    if (statusEl) {
      statusEl.textContent = label || STATUS_LABELS[state.status] || STATUS_LABELS.bot;
    }
  }

  function addMessage(role, { html = '', text = '', id = null } = {}) {
    if (id !== null) {
      if (state.renderedIds.has(id)) return;
      state.renderedIds.add(id);
    }

    const row = document.createElement('div');
    row.className = role === 'user' ? 'flex justify-end' : 'flex justify-start';

    const bubble = document.createElement('div');
    bubble.className = role === 'user'
      ? 'max-w-[86%] rounded-2xl rounded-br-sm bg-[#FFFF5D] px-3.5 py-2.5 text-sm leading-relaxed text-black shadow'
      : 'max-w-[90%] rounded-2xl rounded-bl-sm border border-white/10 bg-[#1A1D21] px-3.5 py-2.5 text-sm leading-relaxed text-[#F3FFDC]';

    if (role === 'human') {
      bubble.classList.add('border-[#25D366]/40');

      const label = document.createElement('p');
      label.className = 'mb-1 text-[10px] font-semibold uppercase tracking-wider text-[#25D366]';
      label.textContent = 'Equipe ATITEC';
      bubble.appendChild(label);
    }

    const content = document.createElement('div');

    if (role === 'user') {
      content.className = 'whitespace-pre-wrap break-words';
      content.textContent = text;
    } else {
      content.className = 'atendimento-ia-prose break-words';

      if (html) {
        content.innerHTML = html;
      } else {
        content.textContent = text;
      }
    }

    bubble.appendChild(content);
    row.appendChild(bubble);
    messagesEl.appendChild(row);
    scrollToBottom();
  }

  function addNotice(message) {
    const notice = document.createElement('div');
    notice.className = 'mx-auto max-w-[92%] rounded-lg border border-[#FFFF5D]/25 bg-[#FFFF5D]/10 px-3 py-2 text-center text-[11px] leading-relaxed text-[#FFFF5D]';
    notice.textContent = message;
    messagesEl.appendChild(notice);
    scrollToBottom();
  }

  function hideQuickReplies() {
    messagesEl.querySelector('[data-ia-quick]')?.remove();
  }

  function renderQuickReplies() {
    if (!Array.isArray(config.quickReplies) || config.quickReplies.length === 0) return;

    const wrap = document.createElement('div');
    wrap.dataset.iaQuick = '1';
    wrap.className = 'flex flex-wrap gap-1.5 pt-1';

    config.quickReplies.forEach((question) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'rounded-full border border-[#FFFF5D]/30 bg-[#FFFF5D]/5 px-2.5 py-1.5 text-left text-[11px] font-medium text-[#FFFF5D] transition hover:bg-[#FFFF5D] hover:text-black';
      button.textContent = question;
      button.addEventListener('click', () => {
        hideQuickReplies();
        send(question);
      });
      wrap.appendChild(button);
    });

    messagesEl.appendChild(wrap);
    scrollToBottom();
  }

  function showWelcome() {
    messagesEl.innerHTML = '';
    state.renderedIds.clear();
    addMessage('assistant', { text: config.welcome });
    renderQuickReplies();
    updateStatus('bot');
  }

  function showContactForm(protocol, intro) {
    if (!contactEl || !contactForm) return;

    contactEl.classList.remove('hidden');
    form.classList.add('hidden');
    panel.classList.add('ia-contact-open');

    if (contactIntro) {
      contactIntro.textContent = intro
        || `Sua dúvida${protocol ? ` (${protocol})` : ''} foi registrada. Informe seu contato para a equipe da ATITEC retornar.`;
    }

    const fields = ['name', 'email', 'phone'];
    fields.forEach((field) => {
      const element = contactForm.querySelector(`[name="${field}"]`);

      if (element && !element.value && config.prefill[field]) {
        element.value = config.prefill[field];
      }
    });
  }

  function hideContactForm() {
    contactEl?.classList.add('hidden');
    form.classList.remove('hidden');
    panel.classList.remove('ia-contact-open');
  }

  async function postJson(url, payload) {
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify(payload),
    });

    let data = {};

    try {
      data = await response.json();
    } catch {
      data = {};
    }

    if (!response.ok) {
      const error = new Error(data.message || 'Não foi possível concluir a solicitação.');
      error.status = response.status;
      error.data = data;
      throw error;
    }

    return data;
  }

  async function getJson(url) {
    const response = await fetch(url, {
      headers: { Accept: 'application/json' },
    });

    let data = {};

    try {
      data = await response.json();
    } catch {
      data = {};
    }

    if (!response.ok) {
      const error = new Error(data.message || 'Conversa não encontrada.');
      error.status = response.status;
      throw error;
    }

    return data;
  }

  async function send(rawMessage) {
    const message = String(rawMessage || '').trim();

    if (!message || state.busy) return;

    hideQuickReplies();
    addMessage('user', { text: message });
    input.value = '';
    resizeInput();
    setBusy(true);
    setTyping(true);

    try {
      const data = await postJson(urls.message, { token: state.token, message });

      if (data.token) {
        state.token = data.token;
        storageSet(data.token);
      }

      if (data.reply_html || data.reply_text) {
        addMessage('assistant', { html: data.reply_html, text: data.reply_text });
      }

      updateStatus(data.status, data.status_label);

      if (data.escalated) {
        if (data.needs_contact) {
          showContactForm(data.protocol);
        } else {
          addNotice(`Sua dúvida foi registrada (${data.protocol}). A equipe da ATITEC foi avisada e retornará pelo contato informado.`);
        }

        startPolling();
      }
    } catch (error) {
      if (error.data?.token) {
        state.token = error.data.token;
        storageSet(error.data.token);
      }

      addMessage('assistant', {
        text: error.data?.message || `Não consegui responder agora. Tente novamente em instantes ou fale com a ATITEC pelo WhatsApp ${root.dataset.whatsapp || ''}`.trim(),
      });
    } finally {
      setBusy(false);
      setTyping(false);
      input.focus();
    }
  }

  async function syncConversation() {
    if (!state.token) {
      if (messagesEl.children.length === 0) showWelcome();
      return;
    }

    try {
      const data = await getJson(`${urls.conversation}/${encodeURIComponent(state.token)}`);

      if (Array.isArray(data.messages)) {
        hideQuickReplies();
        data.messages.forEach((message) => {
          addMessage(message.role, {
            html: message.html,
            text: message.text,
            id: message.id,
          });
        });
      }

      updateStatus(data.status, data.status_label);

      if (data.needs_contact) {
        showContactForm(data.protocol);
      }

      if (data.status && data.status !== 'bot') {
        startPolling();
      }
    } catch (error) {
      if (error.status === 404) {
        state.token = null;
        storageClear();
        showWelcome();

        return;
      }

      if (messagesEl.children.length === 0) {
        showWelcome();
        addNotice('Não consegui carregar a conversa anterior. Você pode continuar por aqui.');
      }
    }
  }

  function startPolling() {
    if (state.pollTimer) return;

    state.pollTimer = window.setInterval(() => {
      if (!state.open || !state.token) return;

      syncConversation();
    }, 25000);
  }

  function resizeInput() {
    input.style.height = 'auto';
    input.style.height = `${Math.min(input.scrollHeight, 112)}px`;
  }

  launcher.addEventListener('click', () => setOpen(!state.open));
  closeButton?.addEventListener('click', () => setOpen(false));

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && state.open) setOpen(false);
  });

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    send(input.value);
  });

  input.addEventListener('input', resizeInput);

  input.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' && !event.shiftKey) {
      event.preventDefault();
      form.requestSubmit();
    }
  });

  contactSkip?.addEventListener('click', () => {
    hideContactForm();
    input.focus();
  });

  contactForm?.addEventListener('submit', async (event) => {
    event.preventDefault();

    if (state.contactBusy || !state.token) return;

    state.contactBusy = true;

    if (contactSubmit) contactSubmit.disabled = true;

    const formData = new FormData(contactForm);

    try {
      const data = await postJson(urls.contact, {
        token: state.token,
        name: formData.get('name'),
        email: formData.get('email'),
        phone: formData.get('phone'),
      });

      hideContactForm();
      addNotice(`Dúvida registrada (${data.protocol}). A equipe da ATITEC foi avisada e retornará pelo contato informado.`);
      startPolling();
    } catch (error) {
      if (contactIntro) {
        contactIntro.textContent = error.data?.message || 'Não foi possível registrar seu contato. Tente novamente.';
      }
    } finally {
      state.contactBusy = false;

      if (contactSubmit) contactSubmit.disabled = false;
    }
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initAtendimentoIa);
} else {
  initAtendimentoIa();
}
