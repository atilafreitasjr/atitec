const VIACEP_URL = 'https://viacep.com.br/ws'
const BRASILAPI_URL = 'https://brasilapi.com.br/api/cep/v1'

const controllers = new WeakMap()

function digitsOnly(value) {
    return String(value).replace(/\D+/g, '')
}

function scopeOf(input) {
    return input.closest('[data-cep-target]') || input.form
}

function setStatus(input, message, state) {
    const status = scopeOf(input)?.querySelector('[data-cep-status]')

    if (!status) {
        return
    }

    const neutral = state !== 'error' && state !== 'success' && state !== 'warning'

    status.textContent = message ?? ''
    status.classList.toggle('text-red-600', state === 'error')
    status.classList.toggle('text-danger', state === 'error')
    status.classList.toggle('text-green-600', state === 'success')
    status.classList.toggle('text-success', state === 'success')
    status.classList.toggle('text-amber-600', state === 'warning')
    status.classList.toggle('text-warning', state === 'warning')
    status.classList.toggle('text-gray-500', neutral)
    status.classList.toggle('text-muted', neutral)
}

function pendingFields(scope) {
    const keys = []

    scope.querySelectorAll('[data-cep-field]').forEach((field) => {
        if (field.required && String(field.value).trim() === '' && !keys.includes(field.dataset.cepField)) {
            keys.push(field.dataset.cepField)
        }
    })

    return keys
}

/** Mostra o status e registra quais campos ainda faltam preencher (some quando completa). */
function setAddressStatus(input, message, state) {
    const pending = pendingFields(scopeOf(input))

    if (pending.length) {
        input.dataset.cepPending = pending.join(',')
    } else {
        delete input.dataset.cepPending
    }

    setStatus(input, message, state)
}

function clearPendingStatus(input) {
    const pending = input.dataset.cepPending

    if (!pending) {
        return
    }

    const scope = scopeOf(input)
    const remaining = pending.split(',').filter((key) => {
        const field = scope.querySelector(`[data-cep-field="${key}"]`)

        return field ? String(field.value).trim() === '' : false
    })

    if (remaining.length === 0) {
        delete input.dataset.cepPending
        setStatus(input, '')
    } else {
        input.dataset.cepPending = remaining.join(',')
    }
}

function fillFields(scope, data) {
    scope.querySelectorAll('[data-cep-field]').forEach((field) => {
        const value = data[field.dataset.cepField]

        if (!value) {
            return
        }

        field.value = field.dataset.cepField === 'uf' ? value.toUpperCase() : value
        field.dispatchEvent(new Event('input', { bubbles: true }))
        field.dispatchEvent(new Event('change', { bubbles: true }))
    })
}

function normalizeBrasilApi(data) {
    return {
        logradouro: data.street,
        bairro: data.neighborhood,
        localidade: data.city,
        uf: data.state,
    }
}

async function fetchCep(cep, signal) {
    try {
        const response = await fetch(`${VIACEP_URL}/${cep}/json/`, { signal })

        if (response.ok) {
            const data = await response.json()

            if (!data.erro) {
                return data
            }
        }
    } catch (error) {
        if (error.name === 'AbortError') {
            throw error
        }
    }

    // ViaCEP indisponível ou CEP ausente na base: tenta o BrasilAPI (agrega várias bases).
    try {
        const response = await fetch(`${BRASILAPI_URL}/${cep}`, { signal })

        if (response.status === 404) {
            return null
        }

        if (response.ok) {
            return normalizeBrasilApi(await response.json())
        }
    } catch (error) {
        if (error.name === 'AbortError') {
            throw error
        }
    }

    throw new Error('CEP lookup failed')
}

async function lookup(input) {
    const scope = scopeOf(input)

    if (!scope) {
        return
    }

    const cep = digitsOnly(input.value)

    if (cep.length !== 8) {
        delete input.dataset.cepLoaded
        delete input.dataset.cepPending
        setStatus(input, '')
        return
    }

    if (input.dataset.cepLoaded === cep) {
        return
    }

    // 00000-000, 11111-111 etc. não são CEPs reais.
    if (/^(\d)\1{7}$/.test(cep)) {
        delete input.dataset.cepLoaded
        setAddressStatus(input, 'CEP não encontrado. Preencha o endereço manualmente.', 'error')
        return
    }

    controllers.get(input)?.abort()

    const controller = new AbortController()
    controllers.set(input, controller)

    delete input.dataset.cepPending
    setStatus(input, 'Buscando endereço…')

    try {
        const data = await fetchCep(cep, controller.signal)

        if (!data) {
            delete input.dataset.cepLoaded
            setAddressStatus(input, 'CEP não encontrado. Preencha o endereço manualmente.', 'error')
            return
        }

        fillFields(scope, data)

        input.dataset.cepLoaded = cep

        if (!data.logradouro && !data.bairro && pendingFields(scope).length) {
            setAddressStatus(input, 'CEP geral da cidade — informe a rua e o bairro manualmente.', 'warning')
        } else {
            setAddressStatus(input, 'Endereço preenchido pelo CEP.', 'success')
        }
    } catch (error) {
        if (error.name === 'AbortError') {
            return
        }

        setAddressStatus(input, 'Não foi possível buscar o CEP. Preencha manualmente.', 'error')
    }
}

if (!window.__atitecCepLookup) {
    window.__atitecCepLookup = true

    document.addEventListener('input', (event) => {
        const input = event.target.closest('[data-cep-lookup]')

        if (input) {
            lookup(input)
            return
        }

        // Preencheu manualmente um campo do endereço? Some o aviso pendente.
        const field = event.target.closest?.('[data-cep-field]')

        if (field) {
            const pendingInput = scopeOf(field)?.querySelector('[data-cep-lookup][data-cep-pending]')

            if (pendingInput) {
                clearPendingStatus(pendingInput)
            }
        }
    })

    // Cobre preenchimento por autofill/colagem sem evento de input.
    document.addEventListener('focusout', (event) => {
        const input = event.target.closest?.('[data-cep-lookup]')

        if (input && digitsOnly(input.value).length === 8) {
            lookup(input)
        }
    })
}

export { lookup }
