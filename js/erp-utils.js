/**
 * SISTEMA ERP - UTILIDADES JAVASCRIPT MOBILE-FIRST
 * Funcionalidades para melhorar a UX em dispositivos móveis
 */

class ERPUtils {
    constructor() {
        this.init();
    }

    init() {
        this.setupMobileUtils();
        this.setupFormValidation();
        this.setupTooltips();
        this.setupConfirmations();
        this.setupLoading();
        this.setupOfflineMode();
    }

    // =======================================================================
    // UTILITÁRIOS MOBILE
    // =======================================================================

    setupMobileUtils() {
        // Melhorar dropdowns em mobile
        if (this.isMobile()) {
            this.enhanceMobileDropdowns();
        }

        // Auto-resize textareas
        document.querySelectorAll('textarea').forEach(textarea => {
            textarea.addEventListener('input', this.autoResize);
        });

        // Smooth scroll para âncoras
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(anchor.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    }

    isMobile() {
        return window.innerWidth <= 768;
    }

    enhanceMobileDropdowns() {
        // Converter dropdowns em modais em mobile para melhor UX
        document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
            toggle.addEventListener('shown.bs.dropdown', () => {
                if (this.isMobile()) {
                    const dropdownMenu = toggle.nextElementSibling;
                    dropdownMenu.style.position = 'fixed';
                    dropdownMenu.style.top = '50%';
                    dropdownMenu.style.left = '50%';
                    dropdownMenu.style.transform = 'translate(-50%, -50%)';
                    dropdownMenu.style.zIndex = '9999';
                    dropdownMenu.style.maxHeight = '70vh';
                    dropdownMenu.style.overflow = 'auto';
                }
            });
        });
    }

    autoResize(e) {
        e.target.style.height = 'auto';
        e.target.style.height = e.target.scrollHeight + 'px';
    }

    // =======================================================================
    // VALIDAÇÃO DE FORMULÁRIOS
    // =======================================================================

    setupFormValidation() {
        // Validação em tempo real
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('input', (e) => {
                this.validateField(e.target);
            });

            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        });

        // Máscaras de input
        this.setupInputMasks();
    }

    validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        let message = '';

        // Validação por tipo
        switch (field.type) {
            case 'email':
                isValid = this.validateEmail(value);
                message = isValid ? '' : 'E-mail inválido';
                break;
            case 'tel':
                isValid = this.validatePhone(value);
                message = isValid ? '' : 'Telefone inválido';
                break;
            default:
                if (field.hasAttribute('required') && !value) {
                    isValid = false;
                    message = 'Campo obrigatório';
                }
        }

        // Validações especiais por name
        if (field.name === 'cnpj') {
            isValid = this.validateCNPJ(value);
            message = isValid ? '' : 'CNPJ inválido';
        }

        if (field.name === 'cpf') {
            isValid = this.validateCPF(value);
            message = isValid ? '' : 'CPF inválido';
        }

        // Aplicar visual
        this.applyValidationStyle(field, isValid, message);
        return isValid;
    }

    validateForm(form) {
        let isValid = true;
        const fields = form.querySelectorAll('input, select, textarea');
        
        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    applyValidationStyle(field, isValid, message) {
        field.classList.remove('is-valid', 'is-invalid');
        
        const feedback = field.parentNode.querySelector('.invalid-feedback');
        if (feedback) feedback.remove();

        if (!isValid) {
            field.classList.add('is-invalid');
            const div = document.createElement('div');
            div.className = 'invalid-feedback';
            div.textContent = message;
            field.parentNode.appendChild(div);
        } else if (field.value.trim()) {
            field.classList.add('is-valid');
        }
    }

    setupInputMasks() {
        // Máscara para CNPJ
        document.querySelectorAll('input[name="cnpj"]').forEach(input => {
            input.addEventListener('input', (e) => {
                let value = e.target.value.replace(/\D/g, '');
                value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
                e.target.value = value;
            });
        });

        // Máscara para CPF
        document.querySelectorAll('input[name="cpf"]').forEach(input => {
            input.addEventListener('input', (e) => {
                let value = e.target.value.replace(/\D/g, '');
                value = value.replace(/^(\d{3})(\d)/, '$1.$2');
                value = value.replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3');
                value = value.replace(/\.(\d{3})(\d)/, '.$1-$2');
                e.target.value = value;
            });
        });

        // Máscara para telefone
        document.querySelectorAll('input[name="telefone"]').forEach(input => {
            input.addEventListener('input', (e) => {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length <= 10) {
                    value = value.replace(/^(\d{2})(\d)/, '($1) $2');
                    value = value.replace(/(\d{4})(\d)/, '$1-$2');
                } else {
                    value = value.replace(/^(\d{2})(\d)/, '($1) $2');
                    value = value.replace(/(\d{5})(\d)/, '$1-$2');
                }
                e.target.value = value;
            });
        });

        // Máscara para CEP
        document.querySelectorAll('input[name="cep"]').forEach(input => {
            input.addEventListener('input', (e) => {
                let value = e.target.value.replace(/\D/g, '');
                value = value.replace(/^(\d{5})(\d)/, '$1-$2');
                e.target.value = value;
            });
        });
    }

    validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    validatePhone(phone) {
        const cleaned = phone.replace(/\D/g, '');
        return cleaned.length >= 10 && cleaned.length <= 11;
    }

    validateCNPJ(cnpj) {
        cnpj = cnpj.replace(/\D/g, '');
        if (cnpj.length !== 14) return false;
        
        // Validação básica - implementar algoritmo completo se necessário
        return !/^(\d)\1{13}$/.test(cnpj);
    }

    validateCPF(cpf) {
        cpf = cpf.replace(/\D/g, '');
        if (cpf.length !== 11) return false;
        
        // Validação básica - implementar algoritmo completo se necessário
        return !/^(\d)\1{10}$/.test(cpf);
    }

    // =======================================================================
    // TOOLTIPS E CONFIRMAÇÕES
    // =======================================================================

    setupTooltips() {
        // Inicializar tooltips do Bootstrap
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    setupConfirmations() {
        // Confirmações elegantes para ações perigosas
        document.querySelectorAll('[data-confirm]').forEach(element => {
            element.addEventListener('click', (e) => {
                e.preventDefault();
                const message = element.getAttribute('data-confirm');
                this.showConfirmModal(message, () => {
                    // Executar ação original
                    if (element.tagName === 'A') {
                        window.location.href = element.href;
                    } else if (element.type === 'submit') {
                        element.closest('form').submit();
                    }
                });
            });
        });
    }

    showConfirmModal(message, onConfirm) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                            Confirmação
                        </h5>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg me-1"></i>Cancelar
                        </button>
                        <button type="button" class="btn btn-danger" id="confirmBtn">
                            <i class="bi bi-check-lg me-1"></i>Confirmar
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const bsModal = new bootstrap.Modal(modal);
        
        modal.querySelector('#confirmBtn').addEventListener('click', () => {
            bsModal.hide();
            onConfirm();
        });

        modal.addEventListener('hidden.bs.modal', () => {
            document.body.removeChild(modal);
        });

        bsModal.show();
    }

    // =======================================================================
    // LOADING E FEEDBACK
    // =======================================================================

    setupLoading() {
        // Auto loading para formulários
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', () => {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    this.setButtonLoading(submitBtn, true);
                }
            });
        });

        // Loading para links importantes
        document.querySelectorAll('a[data-loading]').forEach(link => {
            link.addEventListener('click', () => {
                this.showLoadingOverlay();
            });
        });
    }

    setButtonLoading(button, loading) {
        if (loading) {
            button.disabled = true;
            button.classList.add('btn-loading');
            const originalText = button.innerHTML;
            button.setAttribute('data-original-text', originalText);
            button.innerHTML = '<span class="loading-spinner me-2"></span>Processando...';
        } else {
            button.disabled = false;
            button.classList.remove('btn-loading');
            const originalText = button.getAttribute('data-original-text');
            if (originalText) {
                button.innerHTML = originalText;
            }
        }
    }

    showLoadingOverlay(show = true) {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.style.display = show ? 'flex' : 'none';
        }
    }

    // =======================================================================
    // MODO OFFLINE
    // =======================================================================

    setupOfflineMode() {
        window.addEventListener('online', () => {
            this.showToast('Conexão restaurada!', 'success');
        });

        window.addEventListener('offline', () => {
            this.showToast('Sem conexão com a internet', 'warning');
        });
    }

    // =======================================================================
    // NOTIFICAÇÕES TOAST
    // =======================================================================

    showToast(message, type = 'info', duration = 3000) {
        const toastContainer = this.getToastContainer();
        
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0 fade show`;
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-${this.getToastIcon(type)} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        toastContainer.appendChild(toast);

        const bsToast = new bootstrap.Toast(toast, {
            delay: duration
        });

        bsToast.show();

        toast.addEventListener('hidden.bs.toast', () => {
            toastContainer.removeChild(toast);
        });
    }

    getToastContainer() {
        let container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }
        return container;
    }

    getToastIcon(type) {
        const icons = {
            success: 'check-circle-fill',
            danger: 'exclamation-triangle-fill',
            warning: 'exclamation-triangle-fill',
            info: 'info-circle-fill'
        };
        return icons[type] || icons.info;
    }

    // =======================================================================
    // UTILITÁRIOS ESPECÍFICOS DO ERP
    // =======================================================================

    formatCurrency(value) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value);
    }

    formatNumber(value, decimals = 2) {
        return new Intl.NumberFormat('pt-BR', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(value);
    }

    formatDate(date) {
        return new Intl.DateTimeFormat('pt-BR').format(new Date(date));
    }

    copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            this.showToast('Copiado para a área de transferência!', 'success');
        }).catch(() => {
            this.showToast('Erro ao copiar', 'danger');
        });
    }
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.erpUtils = new ERPUtils();
    
    // Remover loading overlay inicial
    setTimeout(() => {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    }, 500);
});

// Exportar para uso global
window.ERPUtils = ERPUtils;
