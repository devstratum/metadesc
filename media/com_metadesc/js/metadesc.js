/**
 * @package         Metadesc
 * @version         1.54.2
 * @author          Sergey Osipov <info@devstratum.ru>
 * @website         https://devstratum.ru
 * @copyright       Copyright (c) 2022 Sergey Osipov. All Rights Reserved
 * @license         GNU General Public License v2.0
 * @report          https://github.com/devstratum/metadesc/issues
 */

document.addEventListener('DOMContentLoaded', function() {
    let modal_metadesc = document.getElementById('modal-metadesc');
    let metadescModal = new bootstrap.Modal(modal_metadesc, {});
    let modal_dialog = modal_metadesc.querySelector('.modal-dialog');
    let modal_title = modal_metadesc.querySelector('.metadesc-form__title');
    let modal_group = modal_metadesc.querySelector('.metadesc-form__group');
    let modal_alert = modal_metadesc.querySelector('.metadesc-form__alert');
    let modal_progress = modal_metadesc.querySelector('.metadesc-form__progress');
    let button_apply = modal_metadesc.querySelector('.button-apply');
    let button_cancel = modal_metadesc.querySelector('.button-cancel');
    let object_type, object_id;

    let textarea = '<label for="form_description">' + Joomla.JText._('COM_METADESC_FIELD_DESCRIPTION') + '</label>' +
        '<textarea name="description" id="form_description" class="form-control" cols="30" rows="4" maxlength="160"></textarea>';

    modal_dialog.classList.add('modal-dialog-centered');

    modal_metadesc.addEventListener('show.bs.modal', function(event) {
        modal_alert.innerHTML = '';

        let button = event.relatedTarget;
        object_id = button.getAttribute('data-bs-id');
        object_type = button.getAttribute('data-bs-type');

        let metadesc_row = document.getElementById('metadesc_' + object_id);
        let metadesc_title = metadesc_row.querySelector('.metadesc-title');
        let metadesc_description = metadesc_row.querySelector('.metadesc-description');

        modal_title.textContent = metadesc_title.textContent;

        modal_group.innerHTML = textarea;
        let modal_textarea = modal_group.querySelector('textarea');
        if (typeof metadesc_description === 'object' && metadesc_description !== null) {
            metadesc_description.classList.remove('update');
            modal_textarea.value = metadesc_description.querySelector('.text').textContent;
        }
        shortAndSweet(modal_textarea, {
            counterLabel: Joomla.JText._('COM_METADESC_FIELD_DESCRIPTION_COUNT')
        });
    });

    button_cancel.addEventListener('click', function() {
        metadescModal.hide();
    });

    button_apply.addEventListener('click', function() {
        let object_descr = modal_group.querySelector('textarea').value;
        metadescRequest(object_id, object_type, object_descr);
    });

    function metadescAlert(message) {
        let icon = '';
        switch (message.type) {
            case 'success':
                icon = '<span class="icon icon-check-circle"></span>';
                break;
            case 'warning':
                icon = '<span class="icon icon-warning"></span>';
                break;
            case 'danger':
                icon = '<span class="icon icon-warning-circle"></span>';
                break;
        }
        modal_alert.innerHTML = '<div class="alert alert-' + message.type + '">' + icon + '&nbsp;' + message.text + '</div>';
    }

    function metadescUpdate(data) {
        if (data.length !== 0 && data.description) {
            let description = String(data.description);

            let metadesc_row = document.getElementById('metadesc_' + object_id);
            let metadesc_badge = metadesc_row.querySelector('.metadesc-badge');
            let metadesc_count = metadesc_row.querySelector('.metadesc-count');
            let metadesc_description = metadesc_row.querySelector('.metadesc-description');

            metadesc_count.textContent = String(description.length);
            metadesc_badge.classList.remove('bg-danger');
            metadesc_badge.classList.add('bg-success');
            metadesc_description.classList.remove('hidden');
            metadesc_description.classList.add('update');
            metadesc_description.querySelector('.text').textContent = description;
        }

        if (data.length !== 0 && data.checkout) {
            let metadesc_row = document.getElementById('metadesc_' + object_id);
            let metadesc_button = metadesc_row.querySelector('.metadesc-button');
            let metadesc_cheockout = metadesc_row.querySelector('.metadesc-cheockout');

            metadesc_button.classList.add('hidden');
            metadesc_cheockout.classList.remove('hidden');
        }
    }

    function metadescRequest(id, type, description) {
        Joomla.request({
            url: 'index.php?option=com_metadesc&task=metadesc.' + type + '&id=' + id,
            method: 'POST',
            headers: {
                'Cache-Control' : 'no-cache',
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({
                'id' : id,
                'description' : description
            }),
            onBefore: function() {
                modal_alert.innerHTML = '';
                button_apply.disabled = true;
                modal_progress.classList.add('active');
            },
            onSuccess: function(response) {
                let data = JSON.parse(response);
                metadescAlert(data.message);
                metadescUpdate(data.data);
            },
            onError: function() {
                console.log('ajax error');
            },
            onComplete: function() {
                button_apply.disabled = false;
                modal_progress.classList.remove('active');
            }
        });
    }
});