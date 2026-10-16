const MASK_LENGTHS = {
    cpf: 11,
    cnpj: 14,
    cep: 8,
}

function digitsOnly(value) {
    return String(value).replace(/\D+/g, '')
}

function errorListFor(field) {
    let container = field.parentElement

    for (let depth = 0; depth < 3 && container; depth++) {
        const list = [...container.children].find((element) => element.matches('ul.text-red-600'))

        if (list) {
            return list
        }

        if (container.hasAttribute('data-form-section')) {
            break
        }

        container = container.parentElement
    }

    return null
}

function fieldBefore(element) {
    let node = element.previousElementSibling

    while (node) {
        if (node.matches('input, select, textarea')) {
            return node
        }

        const nested = node.querySelector?.('input, select, textarea')

        if (nested) {
            return nested
        }

        node = node.previousElementSibling
    }

    return null
}

function tagServerErrors(section) {
    section.querySelectorAll('ul.text-red-600').forEach((list) => {
        const field = fieldBefore(list)

        if (field) {
            field.dataset.serverError = '1'
        }
    })
}

function sectionFields(section) {
    return [...section.querySelectorAll('input, select, textarea')].filter((field) =>
        field.required &&
        !field.disabled &&
        field.type !== 'hidden' &&
        field.type !== 'checkbox' &&
        field.offsetParent !== null,
    )
}

function fieldValid(field, section) {
    if (String(field.value).trim() === '') {
        return false
    }

    if (field.willValidate && !field.validity.valid) {
        return false
    }

    const mask = field.dataset.mask

    if (mask === 'phone') {
        if (!/^\d{10,11}$/.test(digitsOnly(field.value))) {
            return false
        }
    } else if (mask && MASK_LENGTHS[mask] && digitsOnly(field.value).length !== MASK_LENGTHS[mask]) {
        return false
    }

    if (field.name.endsWith('_confirmation')) {
        const base = section.querySelector(`[name="${field.name.slice(0, -'_confirmation'.length)}"]`)

        if (base && base.value !== field.value) {
            return false
        }
    }

    if (field.hasAttribute('data-password-field')) {
        const rules = field.closest('[data-password-wrapper]')?.querySelectorAll('[data-rule]')

        if (rules?.length && ![...rules].every((item) => item.classList.contains('text-green-600'))) {
            return false
        }
    }

    return true
}

function evaluate(section) {
    const title = section.querySelector('[data-section-title]')

    if (!title) {
        return
    }

    const check = title.querySelector('[data-section-check]')
    const fields = sectionFields(section)
    const hasServerError = section.querySelector('[data-server-error]') !== null
    const complete = !hasServerError && fields.length > 0 && fields.every((field) => fieldValid(field, section))

    title.classList.toggle('text-green-600', complete)
    title.classList.toggle('text-gray-500', !complete)

    if (check) {
        check.classList.toggle('hidden', !complete)
    }
}

function schedule(section) {
    setTimeout(() => evaluate(section), 0)
}

function initSections() {
    document.querySelectorAll('[data-form-section]').forEach((section) => {
        tagServerErrors(section)
        evaluate(section)
    })
}

function clearFieldError(field) {
    if (!field.dataset.serverError) {
        return
    }

    delete field.dataset.serverError
    errorListFor(field)?.setAttribute('hidden', '')
}

if (!window.__atitecFormSections) {
    window.__atitecFormSections = true

    const handle = (event) => {
        const field = event.target.closest?.('input, select, textarea')

        if (!field) {
            return
        }

        const section = field.closest('[data-form-section]')

        if (!section) {
            return
        }

        clearFieldError(field)
        schedule(section)
    }

    document.addEventListener('input', handle)
    document.addEventListener('change', handle)

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSections)
    } else {
        initSections()
    }

    document.addEventListener('alpine:initialized', () => {
        document.querySelectorAll('[data-form-section]').forEach(schedule)
    })
}

export { evaluate, initSections }
