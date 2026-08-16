'use strict';

// Os comportamentos gerais da interface serão adicionados nas próximas etapas.

const somenteNumeros = (valor, limite) => valor.replace(/\D/g, '').slice(0, limite);

const formatarCpf = (valor) => {
    const numeros = somenteNumeros(valor, 11);

    return numeros
        .replace(/^(\d{3})(\d)/, '$1.$2')
        .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
        .replace(/\.(\d{3})(\d)/, '.$1-$2');
};

const formatarTelefone = (valor) => {
    const numeros = somenteNumeros(valor, 11);

    return numeros
        .replace(/^(\d{2})(\d)/, '($1) $2')
        .replace(/(\d{5})(\d)/, '$1-$2');
};

const aplicarMascara = (seletor, formatador) => {
    const campo = document.querySelector(seletor);

    if (!campo) {
        return;
    }

    const atualizar = () => {
        campo.value = formatador(campo.value);
    };

    campo.addEventListener('input', atualizar);
    atualizar();
};

aplicarMascara('#cpf', formatarCpf);
aplicarMascara('#telefone', formatarTelefone);

const formatarCnpj = (valor) => {
    const numeros = somenteNumeros(valor, 14);
    return numeros
        .replace(/^(\d{2})(\d)/, '$1.$2')
        .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
        .replace(/\.(\d{3})(\d)/, '.$1/$2')
        .replace(/(\d{4})(\d)/, '$1-$2');
};

const formatarTelefoneEmpresa = (valor) => {
    const numeros = somenteNumeros(valor, 11);
    if (numeros.length <= 10) {
        return numeros.replace(/^(\d{2})(\d)/, '($1) $2').replace(/(\d{4})(\d)/, '$1-$2');
    }
    return formatarTelefone(numeros);
};

aplicarMascara('#cnpj', formatarCnpj);
aplicarMascara('#telefone_empresa', formatarTelefoneEmpresa);

const formatarCep = (valor) => somenteNumeros(valor, 8).replace(/^(\d{5})(\d)/, '$1-$2');
aplicarMascara('#cep', formatarCep);

const campoCep = document.querySelector('#cep');
const campoLogradouro = document.querySelector('#logradouro');
const campoBairro = document.querySelector('#bairro');
const campoCidade = document.querySelector('#cidade_id');
const campoNumero = document.querySelector('#numero');
const statusCep = document.querySelector('#cep_status');

if (campoCep && campoLogradouro && campoBairro && campoCidade) {
    let ultimoCepConsultado = '';
    let consultaAtual = null;

    const informarStatusCep = (mensagem, classe = 'text-secondary') => {
        if (!statusCep) return;
        statusCep.textContent = mensagem;
        statusCep.className = `form-text ${classe}`;
    };

    const consultarCep = async () => {
        const cep = somenteNumeros(campoCep.value, 8);

        if (cep.length !== 8 || cep === ultimoCepConsultado) {
            if (cep.length !== 8) informarStatusCep('');
            return;
        }

        consultaAtual?.abort();
        consultaAtual = new AbortController();
        ultimoCepConsultado = cep;
        informarStatusCep('Consultando CEP...');

        try {
            const resposta = await fetch(`https://viacep.com.br/ws/${cep}/json/`, {
                signal: consultaAtual.signal
            });
            if (!resposta.ok) throw new Error('Falha ao consultar o CEP.');

            const endereco = await resposta.json();
            if (endereco.erro) {
                informarStatusCep('CEP não encontrado. Confira o número digitado.', 'text-danger');
                return;
            }

            campoLogradouro.value = endereco.logradouro || campoLogradouro.value;
            campoBairro.value = endereco.bairro || campoBairro.value;

            const opcaoCidade = [...campoCidade.options].find(
                (opcao) => opcao.dataset.ibge === String(endereco.ibge)
            );

            if (opcaoCidade) {
                campoCidade.value = opcaoCidade.value;
                informarStatusCep('Endereço localizado. Complete o número e o complemento.', 'text-success');
            } else {
                informarStatusCep('Endereço localizado, mas a cidade não está na região atendida.', 'text-warning');
            }

            campoNumero?.focus();
        } catch (erro) {
            if (erro.name !== 'AbortError') {
                ultimoCepConsultado = '';
                informarStatusCep('Não foi possível consultar agora. Preencha o endereço manualmente.', 'text-danger');
            }
        }
    };

    campoCep.addEventListener('input', consultarCep);
    campoCep.addEventListener('blur', consultarCep);
}

document.querySelectorAll('[data-current-toggle]').forEach((campoAtual) => {
    const campoData = document.querySelector(campoAtual.dataset.target);

    if (!campoData) {
        return;
    }

    const atualizarDataFinal = () => {
        campoData.disabled = campoAtual.checked;
        campoData.required = !campoAtual.checked;

        if (campoAtual.checked) {
            campoData.value = '';
        }
    };

    campoAtual.addEventListener('change', atualizarDataFinal);
    atualizarDataFinal();
});
