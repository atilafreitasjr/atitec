/**
 * Formulário de fatura (admin): ao escolher o projeto, carrega as tarefas
 * concluídas e ainda não faturadas (mais as já vinculadas, ao editar) para
 * seleção. As tarefas marcadas são vinculadas à fatura no salvar e saem da
 * lista das próximas faturas.
 */

function brl(value) {
  return 'R$ ' + Number(value || 0).toLocaleString('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

function selectedTotal(container) {
  return Array.from(container.querySelectorAll('.invoice-task-checkbox:checked')).reduce(
    (sum, box) => sum + parseFloat(box.dataset.cost || '0'),
    0,
  )
}

function refresh(container) {
  const boxes = Array.from(container.querySelectorAll('.invoice-task-checkbox'))
  const toggleAll = container.querySelector('[data-invoice-tasks-toggle-all]')
  const total = container.querySelector('[data-invoice-tasks-total]')

  if (total) {
    total.textContent = brl(selectedTotal(container))
  }

  if (toggleAll) {
    toggleAll.checked = boxes.length > 0 && boxes.every((box) => box.checked)
    toggleAll.indeterminate = !toggleAll.checked && boxes.some((box) => box.checked)
  }
}

function syncAmount(container) {
  const total = selectedTotal(container)
  const display = container.closest('form')?.querySelector('[data-mask="money"]')

  if (total <= 0 || !display) {
    return
  }

  display.value = total.toFixed(2)
  display.dispatchEvent(new Event('input', { bubbles: true }))
}

function escapeHtml(value) {
  return String(value ?? '').replace(/[&<>"']/g, (char) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
  })[char])
}

function render(container, tasks) {
  const list = container.querySelector('[data-invoice-tasks-list]')

  if (!list) {
    return
  }

  const marker = document.createElement('input')
  marker.type = 'hidden'
  marker.name = 'tasks_synced'
  marker.value = '1'

  if (tasks.length === 0) {
    list.innerHTML = '<p class="text-muted small mb-0">Não há tarefas concluídas com custo realizado aguardando faturamento neste projeto.</p>'
    list.appendChild(marker)
    refresh(container)

    return
  }

  const rows = tasks.map((task) => `
    <tr>
      <td><input type="checkbox" class="form-check-input invoice-task-checkbox" name="task_ids[]" value="${task.id}" data-cost="${task.cost}" ${task.linked ? 'checked' : ''} aria-label="Cobrar ${escapeHtml(task.title)}"></td>
      <td>${escapeHtml(task.title)}</td>
      <td class="small text-muted">${escapeHtml(task.deliverable || '—')}</td>
      <td class="text-end">${Number(task.hours).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
      <td class="text-end">${brl(task.rate)}</td>
      <td class="text-end">${brl(task.cost)}</td>
    </tr>`).join('')

  list.innerHTML = `
    <div class="table-responsive">
      <table class="table table-sm align-middle mb-2">
        <thead><tr>
          <th style="width:36px"><input type="checkbox" class="form-check-input" data-invoice-tasks-toggle-all aria-label="Selecionar todas"></th>
          <th>Tarefa</th>
          <th>Entrega</th>
          <th class="text-end">h realizadas</th>
          <th class="text-end">Valor/h</th>
          <th class="text-end">Custo</th>
        </tr></thead>
        <tbody>${rows}</tbody>
      </table>
    </div>
    <p class="small text-muted mb-0">Selecionadas: <strong data-invoice-tasks-total>R$ 0,00</strong> — o valor é sugerido no campo <em>Valor (R$)</em> e pode ser ajustado.</p>`

  list.appendChild(marker)

  const toggleAll = list.querySelector('[data-invoice-tasks-toggle-all]')

  if (toggleAll) {
    toggleAll.addEventListener('change', () => {
      list.querySelectorAll('.invoice-task-checkbox').forEach((box) => {
        box.checked = toggleAll.checked
      })
      refresh(container)
      syncAmount(container)
    })
  }

  list.querySelectorAll('.invoice-task-checkbox').forEach((box) => {
    box.addEventListener('change', () => {
      refresh(container)
      syncAmount(container)
    })
  })

  refresh(container)
}

async function load(container) {
  const form = container.closest('form')
  const select = form?.querySelector('[name="project_id"]')
  const list = container.querySelector('[data-invoice-tasks-list]')
  const urlTemplate = container.dataset.fetchUrl

  if (!form || !select || !list || !urlTemplate) {
    return
  }

  const projectId = select.value

  if (!projectId) {
    list.innerHTML = '<p class="text-muted small mb-0">Escolha um projeto acima para listar as tarefas faturáveis.</p>'

    return
  }

  const invoiceId = container.dataset.invoiceId
  let url = urlTemplate.replace('__PROJECT__', projectId)

  if (invoiceId) {
    url += (url.includes('?') ? '&' : '?') + 'invoice=' + invoiceId
  }

  list.innerHTML = '<p class="text-muted small mb-0">Carregando tarefas...</p>'

  try {
    const response = await fetch(url, { headers: { Accept: 'application/json' } })

    if (!response.ok) {
      throw new Error('HTTP ' + response.status)
    }

    const data = await response.json()
    render(container, Array.isArray(data.tasks) ? data.tasks : [])
  } catch (e) {
    list.innerHTML = '<p class="text-danger small mb-0">Não foi possível carregar as tarefas do projeto. Atualize a página e tente novamente.</p>'
  }
}

if (!window.__atitecInvoiceTasks) {
  window.__atitecInvoiceTasks = true

  document.addEventListener('change', (event) => {
    const select = event.target.closest('form [name="project_id"]')

    if (!select) {
      return
    }

    const container = select.form?.querySelector('[data-invoice-tasks]')

    if (container) {
      load(container)
    }
  })

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-invoice-tasks]').forEach(load)
  })
}
