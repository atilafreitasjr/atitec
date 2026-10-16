/**
 * Barra de formatação Markdown para textareas (site público).
 *
 * Uso: envolva a textarea num container [data-markdown-editor] e adicione
 * botões [data-md-action="bold|italic|heading|ul|ol|quote|code|link"].
 * Os recursos usam document.execCommand('insertText') para preservar o
 * desfazer (Ctrl+Z) nativo da textarea.
 */

function insertText(textarea, start, end, text) {
  textarea.focus()
  textarea.setSelectionRange(start, end)

  const before = textarea.value

  try {
    document.execCommand('insertText', false, text)
  } catch (e) {
    // segue para o fallback abaixo
  }

  if (textarea.value === before && text !== '') {
    textarea.setRangeText(text, start, end, 'end')
    textarea.dispatchEvent(new Event('input', { bubbles: true }))
  }
}

function wrapSelection(textarea, before, after, placeholder) {
  const start = textarea.selectionStart
  const end = textarea.selectionEnd
  const selected = textarea.value.slice(start, end) || placeholder
  const text = before + selected + after

  insertText(textarea, start, end, text)

  const innerStart = start + before.length
  textarea.setSelectionRange(innerStart, innerStart + selected.length)
}

function insertLink(textarea) {
  const start = textarea.selectionStart
  const end = textarea.selectionEnd
  const selected = textarea.value.slice(start, end) || 'texto do link'
  const text = '[' + selected + '](url)'

  insertText(textarea, start, end, text)

  const urlStart = start + selected.length + 3
  textarea.setSelectionRange(urlStart, urlStart + 3)
}

function toggleLinePrefix(textarea, prefix) {
  const value = textarea.value
  const start = value.lastIndexOf('\n', textarea.selectionStart - 1) + 1
  let end = value.indexOf('\n', textarea.selectionEnd)

  if (end === -1) {
    end = value.length
  }

  const lines = value.slice(start, end).split('\n')
  const meaningful = lines.filter((line) => line.trim() !== '')
  const allPrefixed = meaningful.length > 0 && meaningful.every((line) => line.startsWith(prefix))

  const text = lines.map((line) => {
    if (line.trim() === '') {
      return line
    }

    if (line.startsWith(prefix)) {
      return allPrefixed ? line.slice(prefix.length) : line
    }

    return allPrefixed ? line : prefix + line
  }).join('\n')

  insertText(textarea, start, end, text)
  textarea.setSelectionRange(start, start + text.length)
}

const ACTIONS = {
  bold: (textarea) => wrapSelection(textarea, '**', '**', 'texto em negrito'),
  italic: (textarea) => wrapSelection(textarea, '_', '_', 'texto em itálico'),
  heading: (textarea) => toggleLinePrefix(textarea, '## '),
  ul: (textarea) => toggleLinePrefix(textarea, '- '),
  ol: (textarea) => toggleLinePrefix(textarea, '1. '),
  quote: (textarea) => toggleLinePrefix(textarea, '> '),
  code: (textarea) => wrapSelection(textarea, '`', '`', 'código'),
  link: (textarea) => insertLink(textarea),
}

function initMarkdownEditors() {
  document.querySelectorAll('[data-markdown-editor]').forEach((editor) => {
    if (editor.dataset.markdownReady) {
      return
    }

    const textarea = editor.querySelector('textarea')

    if (!textarea) {
      return
    }

    editor.querySelectorAll('[data-md-action]').forEach((button) => {
      button.addEventListener('click', () => {
        const action = ACTIONS[button.dataset.mdAction]

        if (action) {
          action(textarea)
        }
      })
    })

    editor.dataset.markdownReady = 'true'
  })
}

if (!window.__atitecRichText) {
  window.__atitecRichText = true

  document.addEventListener('DOMContentLoaded', initMarkdownEditors)
}
