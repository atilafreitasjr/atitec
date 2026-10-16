const PATTERNS = {
    cpf: '###.###.###-##',
    cnpj: '##.###.###/####-##',
    cep: '#####-###',
}

const MAX_DIGITS = {
    cpf: 11,
    cnpj: 14,
    cep: 8,
}

function digitsOnly(value) {
    return String(value).replace(/\D+/g, '')
}

function applyPattern(digits, pattern) {
    let result = ''
    let index = 0

    for (const token of pattern) {
        if (index >= digits.length) {
            break
        }

        if (token === '#') {
            result += digits[index++]
        } else if (digits.length > index) {
            result += token
        }
    }

    return result
}

function formatPhone(value) {
    let digits = digitsOnly(value)

    if (digits.length > 11 && digits.startsWith('55')) {
        digits = digits.slice(2)
    }

    digits = digits.slice(0, 11)

    if (digits === '') {
        return ''
    }

    if (digits.length <= 2) {
        return `(${digits}`
    }

    if (digits.length <= 6) {
        return `(${digits.slice(0, 2)}) ${digits.slice(2)}`
    }

    if (digits.length <= 10) {
        return `(${digits.slice(0, 2)}) ${digits.slice(2, 6)}-${digits.slice(6)}`
    }

    return `(${digits.slice(0, 2)}) ${digits.slice(2, 7)}-${digits.slice(7)}`
}

function formatMoney(value) {
    const digits = digitsOnly(value).slice(0, 15)

    if (digits === '') {
        return ''
    }

    const padded = digits.padStart(3, '0')
    const reais = padded.slice(0, -2).replace(/\B(?=(\d{3})+(?!\d))/g, '.')

    return `${reais},${padded.slice(-2)}`
}

function moneyToRaw(value) {
    const digits = digitsOnly(value)

    return digits === '' ? '' : (Number(digits) / 100).toFixed(2)
}

function formatValue(mask, value) {
    if (mask === 'money') {
        return formatMoney(value)
    }

    if (mask === 'phone') {
        return formatPhone(value)
    }

    const pattern = PATTERNS[mask]

    if (!pattern) {
        return value
    }

    return applyPattern(digitsOnly(value).slice(0, MAX_DIGITS[mask]), pattern)
}

function rawField(input) {
    const name = input.dataset.maskRaw

    if (!name || !input.form) {
        return null
    }

    const field = input.form.elements.namedItem(name)

    return field instanceof HTMLInputElement ? field : null
}

function maskEnabled(input) {
    const condition = input.dataset.maskWhen

    if (!condition) {
        return true
    }

    const [selector, ...values] = condition.split('|')
    const control = input.closest('form')?.querySelector(selector)

    return !!control && values.includes(control.value)
}

function updateMask(input) {
    const mask = input.dataset.mask

    if (!mask || !maskEnabled(input)) {
        return
    }

    input.value = formatValue(mask, input.value)

    const raw = rawField(input)

    if (raw) {
        raw.value = moneyToRaw(input.value)
    }
}

function initMasks() {
    document.querySelectorAll('[data-mask]').forEach((input) => {
        const raw = rawField(input)

        // Campos de moeda guardam o número puro num input oculto.
        if (input.dataset.mask === 'money' && raw && input.value === '' && raw.value !== '') {
            input.value = formatMoney(raw.value)
        }

        if (input.value !== '' && maskEnabled(input)) {
            input.value = formatValue(input.dataset.mask, input.value)
        }

        if (raw) {
            raw.value = moneyToRaw(input.value)
        }
    })
}

if (!window.__atitecMasks) {
    window.__atitecMasks = true

    document.addEventListener('input', (event) => {
        const input = event.target.closest('[data-mask]')

        if (input) {
            updateMask(input)

            return
        }

        // Campo controlador (ex.: tipo do canal) mudou: reavalia as máscaras condicionais.
        event.target.closest('form')?.querySelectorAll('[data-mask][data-mask-when]').forEach(updateMask)
    })

    document.addEventListener('change', (event) => {
        event.target.closest('form')?.querySelectorAll('[data-mask][data-mask-when]').forEach(updateMask)
    })

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMasks)
    } else {
        initMasks()
    }
}

export { formatValue, formatMoney, formatPhone, initMasks }
