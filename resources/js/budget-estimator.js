/**
 * Estimador de orçamento do site (/orcamento).
 *
 * Lê os parâmetros (hora técnica, margens) dos data-* do formulário e as
 * horas/custos dos data-* das opções; mostra a estimativa preliminar e
 * sugere a faixa de investimento em torno do valor calculado. O servidor
 * recalcula os mesmos valores (App\Support\BudgetEstimator) ao salvar.
 */

function formatBrl(value) {
  return 'R$ ' + Number(value).toLocaleString('pt-BR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  })
}

function roundTo(value, step, mode) {
  const fn = mode === 'ceil' ? Math.ceil : Math.floor

  return fn(value / step) * step
}

function collectEstimate(form) {
  const typeSelect = form.querySelector('[name="project_type"]')
  const typeOption = typeSelect && typeSelect.value ? typeSelect.selectedOptions[0] : null

  if (!typeOption) {
    return null
  }

  let scopeHours = 0
  let scopeMonthly = 0

  form.querySelectorAll('[name="requirements[]"]:checked').forEach((box) => {
    scopeHours += parseFloat(box.dataset.hours || '0')
    scopeMonthly += parseFloat(box.dataset.monthly || '0')
  })

  let setup = 0
  let infraMonthly = 0

  form.querySelectorAll('[name="infrastructure[]"]:checked').forEach((box) => {
    setup += parseFloat(box.dataset.setup || '0')
    infraMonthly += parseFloat(box.dataset.monthly || '0')
  })

  const deadlineSelect = form.querySelector('[name="deadline"]')
  const deadlineOption = deadlineSelect && deadlineSelect.value ? deadlineSelect.selectedOptions[0] : null
  const multiplier = deadlineOption ? parseFloat(deadlineOption.dataset.multiplier || '1') : 1

  const rate = parseFloat(form.dataset.hourlyRate || '0')
  const hours = parseFloat(typeOption.dataset.hours || '0') + scopeHours
  const development = hours * rate * multiplier

  return {
    hours,
    development,
    multiplier,
    setup,
    monthly: scopeMonthly + infraMonthly,
    total: development + setup,
  }
}

function bracketEdges(total) {
  const low = Math.max(500, roundTo(total * 0.65, 500, 'floor'))
  const mid = Math.max(low + 500, roundTo(total, 500, 'ceil'))

  return {
    low,
    mid,
    high: Math.max(mid + 500, roundTo(total * 1.5, 500, 'ceil')),
  }
}

function updateBrackets(select, total) {
  if (!select) {
    return
  }

  const { low, mid, high } = bracketEdges(total)
  const options = [
    'Até ' + formatBrl(low),
    formatBrl(low) + ' a ' + formatBrl(mid),
    formatBrl(mid) + ' a ' + formatBrl(high),
    'Acima de ' + formatBrl(high),
    'Ainda não sei, quero discutir',
  ]

  const previous = select.value
  const suggestedIndex = total <= low ? 0 : (total <= mid ? 1 : (total <= high ? 2 : 3))
  const values = options.map((label) => label.charAt(0).toLowerCase() + label.slice(1))

  select.innerHTML = ''

  options.forEach((label, index) => {
    const el = document.createElement('option')
    el.value = values[index]
    el.textContent = label
    select.appendChild(el)
  })

  select.value = values.includes(previous) ? previous : values[suggestedIndex]
}

function updatePanel(form, data) {
  const panel = form.querySelector('[data-estimate-panel]')

  if (!panel) {
    return
  }

  panel.classList.remove('hidden')

  const min = roundTo(data.total * parseFloat(form.dataset.minFactor || '0.8'), parseInt(form.dataset.roundTo || '100', 10), 'floor')
  const max = roundTo(data.total * parseFloat(form.dataset.maxFactor || '1.25'), parseInt(form.dataset.roundTo || '100', 10), 'ceil')

  form.querySelector('[data-estimate-range]').textContent = formatBrl(min) + ' a ' + formatBrl(max)

  let breakdown = data.hours.toLocaleString('pt-BR') + ' h de desenvolvimento (' + formatBrl(data.development) + ')'

  if (data.multiplier > 1) {
    breakdown += ' · prazo ×' + data.multiplier.toLocaleString('pt-BR')
  }

  if (data.setup > 0) {
    breakdown += ' + ' + formatBrl(data.setup) + ' de implantação'
  }

  form.querySelector('[data-estimate-breakdown]').textContent = breakdown

  const monthlyEl = form.querySelector('[data-estimate-monthly]')

  if (data.monthly > 0) {
    monthlyEl.textContent = 'Serviços recorrentes estimados: ' + formatBrl(data.monthly) + ' por mês'
    monthlyEl.classList.remove('hidden')
  } else {
    monthlyEl.classList.add('hidden')
  }

  updateBrackets(form.querySelector('[name="budget_range"]'), data.total)
}

function refresh(form) {
  const data = collectEstimate(form)
  const panel = form.querySelector('[data-estimate-panel]')

  if (!data || data.hours <= 0) {
    if (panel) {
      panel.classList.add('hidden')
    }

    return
  }

  updatePanel(form, data)
}

function initEstimators() {
  document.querySelectorAll('form[data-budget-estimator]').forEach((form) => {
    if (form.dataset.estimatorReady) {
      return
    }

    form.addEventListener('change', () => refresh(form))
    form.dataset.estimatorReady = 'true'

    refresh(form)
  })
}

if (!window.__atitecBudgetEstimator) {
  window.__atitecBudgetEstimator = true

  document.addEventListener('DOMContentLoaded', initEstimators)
}
