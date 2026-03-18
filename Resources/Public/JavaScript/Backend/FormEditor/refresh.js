import DocumentService from "@typo3/core/document-service.js";
import Modal from '@typo3/backend/modal.js';
import FormEngine from '@typo3/backend/form-engine.js';

class Refresh {
    constructor() {
        DocumentService.ready().then(() => {
            this.initializeControls();
        });
    }

    initializeControls() {
        const controls = document.querySelectorAll('.mautic-refresh-control');
        controls.forEach(control => {
            control.addEventListener('click', (e) => this.handleControlClick(e));
        });
    }

    handleControlClick(e) {
        e.preventDefault();

        const control = e.currentTarget;
        const tableName = control.dataset.tableName;
        const action = control.dataset.action;

        if (!tableName || !action) {
            console.error('Mautic Refresh Control: Missing data attributes');
            return false;
        }

        Modal.advanced({
            title: TYPO3.lang["FormEngine.refreshRequiredTitle"] || 'Please confirm',
            content: TYPO3.lang["FormEngine.refreshRequiredContent"] || 'Do you want to reload the data? This will require to save the form.',
            severity: Modal.types.warning,
            buttons: [
                {
                    text: TYPO3.lang['button.cancel'] || 'Cancel',
                    btnClass: 'btn-default',
                    trigger: () => {
                        Modal.currentModal.hideModal();
                    }
                },
                {
                    text: TYPO3.lang['button.ok'] || 'OK',
                    btnClass: 'btn-warning',
                    trigger: () => {
                        const input = document.createElement("input");
                        input.type = "hidden";
                        input.name = `${tableName}[${action}]`;
                        input.value = '1';
                        document.getElementsByName(FormEngine.formName).item(0).appendChild(input);
                        FormEngine.saveDocument();
                        Modal.currentModal.hideModal();
                    }
                }
            ]
        });

        return false;
    }
}

export default new Refresh();
