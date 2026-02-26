(function () {
  'use strict';

  const qs = (s, ctx = document) => ctx.querySelector(s);
  const qsa = (s, ctx = document) => [...ctx.querySelectorAll(s)];


  const DISEASE_DATA = Object.freeze({
    dengue: {
      title: 'Dengue',
      summary: 'Transmissão pelo mosquito Aedes aegypti.',
      symptoms: ['Febre alta', 'Dor atrás dos olhos', 'Dores nas articulações'],
      prevention: ['Eliminar água parada', 'Usar repelente'],
      when: 'Procure a UPA em caso de manchas vermelhas ou sangramentos.'
    },
    zika: {
      title: 'Zika',
      summary: 'Transmitida pelo Aedes; cuidado redobrado com gestantes.',
      symptoms: ['Febre baixa', 'Manchas vermelhas que coçam', 'Olhos vermelhos'],
      prevention: ['Telas em janelas', 'Combate ao mosquito'],
      when: 'Gestantes devem procurar o médico ao notar manchas na pele.'
    },
    lepto: {
      title: 'Leptospirose',
      summary: 'Contágio por urina de rato em água ou lama.',
      symptoms: ['Febre', 'Dor forte na batata da perna', 'Dor de cabeça'],
      prevention: ['Evitar contato com água de enchente', 'Manter lixo fechado'],
      when: 'Procure socorro se houver febre após contato com alagamentos.'
    },
    covid: {
      title: 'COVID-19',
      summary: 'Doença respiratória viral.',
      symptoms: ['Tosse', 'Febre', 'Cansaço', 'Perda de olfato'],
      prevention: ['Vacinação em dia', 'Ambientes ventilados'],
      when: 'Vá ao hospital se sentir falta de ar.'
    },
    gripe: {
      title: 'Gripe (Influenza)',
      summary: 'Infecção viral sazonal.',
      symptoms: ['Coriza', 'Dor de garganta', 'Febre e dores no corpo'],
      prevention: ['Vacina anual', 'Lavar as mãos'],
      when: 'Idosos e crianças devem ser monitorados em caso de febre alta.'
    }
  });

  function populateList(el, items) {
    if (!el) return;
    el.innerHTML = (items || []).map(item => `<li>${item}</li>`).join('');
  }

  function openDiseaseModal(key) {
    const modalEl = qs('#diseaseModal');
    const data = DISEASE_DATA[key];

    if (!data) {
      console.error(`Erro: Dados para a doença "${key}" não encontrados no JS.`);
      return;
    }

    // Preenche o Modal com os dados
    qs('#diseaseTitle').textContent = data.title;
    qs('#diseaseSummary').textContent = data.summary;
    qs('#diseaseWhen').textContent = data.when;
    populateList(qs('#diseaseSymptoms'), data.symptoms);
    populateList(qs('#diseasePrevention'), data.prevention);

    // Abre o Modal do Bootstrap
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
  }

  document.addEventListener('DOMContentLoaded', () => {
    // Configura os botões
    qsa('.btn-know').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const diseaseKey = btn.getAttribute('data-disease');
        openDiseaseModal(diseaseKey);
      });
    });
  });
})();

// Lógica para Envio de Denúncia de Descarte de Lixo
const formLixo = document.querySelector('#formDenunciaLixo');

if (formLixo) {
  formLixo.addEventListener('submit', async (e) => {
    e.preventDefault();

    // Feedback visual: desativa o botão enquanto envia
    const btnSubmit = formLixo.querySelector('button[type="submit"]');
    const originalText = btnSubmit.textContent;
    btnSubmit.disabled = true;
    btnSubmit.textContent = 'A enviar...';

    const formData = new FormData(formLixo);

    try {
      const response = await fetch('./api/denuncias.php', {
        method: 'POST',
        body: formData
      });

      const result = await response.json();

      if (result.success) {
        alert('Sucesso! A sua denúncia de descarte de lixo foi registada.');
        formLixo.reset();

        // Fecha o modal automaticamente após o sucesso
        const modalElement = document.getElementById('denunciaModal');
        const modalInstance = bootstrap.Modal.getInstance(modalElement);
        if (modalInstance) modalInstance.hide();
      } else {
        alert('Erro: ' + (result.error || 'Falha ao processar denúncia.'));
      }
    } catch (error) {
      console.error('Erro na comunicação com a API:', error);
      alert('Erro de conexão. Verifique se o servidor está a correr.');
    } finally {
      // Restaura o botão
      btnSubmit.disabled = false;
      btnSubmit.textContent = originalText;
    }
  });
}