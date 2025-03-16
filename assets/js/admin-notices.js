'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const btnNagDismiss = document.querySelector('.wpp-dismiss-performance-notice');
    const btnNagRemind = document.querySelector('.wpp-remind-performance-notice');

    if ( btnNagDismiss && btnNagRemind ) {
        btnNagDismiss.addEventListener('click', (e) => {
            e.preventDefault();
            handleNagClick(e, '1');
        });

        btnNagRemind.addEventListener('click', (e) => {
            e.preventDefault();
            handleNagClick(e, '-1');
        });
    }

    function handleNagClick(e, dismissValue) {
        const me = e.target;

        btnNagRemind.classList.add('disabled');
        btnNagDismiss.classList.add('disabled');

        me.parentNode.querySelector('.spinner').classList.add('is-active');

        ajax(
            'POST',
            ajaxurl,
            `action=wpp_handle_performance_notice&dismiss=${dismissValue}&token=${wpp_admin_notices_params.nonce_performance_nag}`,
            (response) => handleNotice(response, me)
        );
    }

    function handleNotice(response, btn) {
        const json = JSON.parse(response);

        if ( json.status === 'success' ) {
            const notice = btn.parentNode.parentNode;
            notice.parentNode.removeChild(notice);
        } else {
            alert('Something went wrong, please try again later');
        }

        btnNagRemind.classList.remove('disabled');
        btnNagDismiss.classList.remove('disabled');
        btn.parentNode.querySelector('.spinner').classList.remove('is-active');
    }

    function ajax(method, url, params, callback) {
        const validMethods = ['GET', 'POST'];
        const headers = {
            'X-Requested-With': 'XMLHttpRequest'
        };

        if ( ! validMethods.includes(method) ) {
            method = 'GET';
        }

        if ( method === 'POST' ) {
            headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        fetch(url + (method === 'GET' ? '?' + params : ''), {
            method,
            headers,
            body: method === 'POST' ? params : null
        })
        .then(response => {
            if ( ! response.ok ) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(data => callback(data))
        .catch(error => console.error('Fetch error:', error));
    }
});