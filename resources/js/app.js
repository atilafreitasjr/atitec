

import Alpine from 'alpinejs';

import './budget-estimator';
import './cep';
import './form-sections';
import './masks';
import './password';
import './rich-text';
import './atendimento-ia';

window.Alpine = Alpine;

// Switch de tema da home pública: envia a escolha ao servidor assim que muda.
document.addEventListener('change', (event) => {
  const toggle = event.target.closest('[data-site-theme-switch]')

  if (toggle) {
    toggle.form?.requestSubmit()
  }
})

Alpine.start();
