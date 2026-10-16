const RULE_TESTS = {
    min: (value, arg) => value.length >= Number(arg),
    max: (value, arg) => value.length <= Number(arg),
    letters: (value) => /[a-zA-Z]/.test(value),
    mixed: (value) => /[a-z]/.test(value) && /[A-Z]/.test(value),
    numbers: (value) => /\d/.test(value),
    symbols: (value) => /[^a-zA-Z0-9]/.test(value),
}

function ruleMet(rule, value) {
    const [name, arg] = rule.split(':')
    const test = RULE_TESTS[name]

    return test ? test(value, arg) : null
}

function toggleClass(element, name, condition) {
    if (element) {
        element.classList.toggle(name, condition)
    }
}

function rulesList(input) {
    const wrapper = input.closest('[data-password-wrapper]')

    return wrapper?.querySelector('[data-password-rules]') ?? null
}

function setRulesVisible(input, visible) {
    const list = rulesList(input)

    if (!list) {
        return
    }

    toggleClass(list, 'hidden', !visible)
    toggleClass(list, 'd-none', !visible)
}

function refreshRules(input) {
    const list = rulesList(input)

    if (!list) {
        return
    }

    list.querySelectorAll('[data-rule]').forEach((item) => {
        const met = ruleMet(item.dataset.rule, input.value)

        if (met === null) {
            return
        }

        toggleClass(item, 'text-green-600', met)
        toggleClass(item, 'text-success', met)
        toggleClass(item, 'text-gray-400', !met)
        toggleClass(item, 'text-muted', !met)

        const icon = item.querySelector('[data-rule-icon]')
        const dot = item.querySelector('[data-rule-dot]')

        toggleClass(icon, 'hidden', !met)
        toggleClass(icon, 'd-none', !met)
        toggleClass(dot, 'hidden', met)
        toggleClass(dot, 'd-none', met)
    })
}

function setPasswordState(input, show) {
    input.type = show ? 'text' : 'password'

    const wrapper = input.closest('[data-password-wrapper]')
    const button = wrapper?.querySelector('[data-password-toggle]')

    if (!button) {
        return
    }

    button.setAttribute('aria-label', show ? 'Ocultar senha' : 'Mostrar senha')
    button.setAttribute('aria-pressed', show ? 'true' : 'false')

    // Ícone reflete o estado do campo: olho cortado = oculto; olho aberto = visível.
    const open = button.querySelector('[data-eye-open]')
    const closed = button.querySelector('[data-eye-closed]')

    toggleClass(open, 'hidden', !show)
    toggleClass(open, 'd-none', !show)
    toggleClass(closed, 'hidden', show)
    toggleClass(closed, 'd-none', show)
}

function togglePassword(button) {
    const wrapper = button.closest('[data-password-wrapper]')
    const input = wrapper?.querySelector('[data-password-field]')

    if (input) {
        setPasswordState(input, input.type === 'password')
    }
}

function initPasswordFields() {
    document.querySelectorAll('[data-password-field]').forEach((input) => {
        // Garante o padrão oculto ao carregar (inclusive ao voltar via bfcache).
        setPasswordState(input, false)
        refreshRules(input)
        setRulesVisible(input, input.value !== '')
    })
}

if (!window.__atitecPasswordInputs) {
    window.__atitecPasswordInputs = true

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-password-toggle]')

        if (button) {
            togglePassword(button)
        }
    })

    // As regras ficam ocultas por padrão: aparecem ao focar ou digitar.
    document.addEventListener('focusin', (event) => {
        const input = event.target.closest('[data-password-field]')

        if (input) {
            setRulesVisible(input, true)
        }
    })

    document.addEventListener('focusout', (event) => {
        const input = event.target.closest('[data-password-field]')

        if (input) {
            setRulesVisible(input, input.value !== '')
        }
    })

    document.addEventListener('input', (event) => {
        const input = event.target.closest('[data-password-field]')

        if (input) {
            refreshRules(input)
            setRulesVisible(input, input.value !== '' || document.activeElement === input)
        }
    })

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPasswordFields)
    } else {
        initPasswordFields()
    }

    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            initPasswordFields()
        }
    })
}

export { initPasswordFields }
