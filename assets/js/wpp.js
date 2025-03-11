'use strict';

const wpp_params = document.currentScript.dataset;
const WordPressPopularPosts = (() => {
    const noop = () => {};

    const get = (url, params, callback = noop, additional_headers) => {
        ajax('GET', url, params, callback, additional_headers);
    };

    const post = (url, params, callback = noop, additional_headers) => {
        ajax('POST', url, params, callback, additional_headers);
    };

    const ajax = (method, url, params, callback, additional_headers) => {
        const valid_methods = ['GET', 'POST'];
        const headers = {
            'X-Requested-With': 'XMLHttpRequest',
            ...additional_headers
        };

        if ( ! valid_methods.includes(method) ) {
            method = 'GET';
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
    };

    const theme = (wpp_list) => {
        const base_styles = document.createElement('style'),
            dummy_list = document.createElement('ul');

        dummy_list.innerHTML = '<li><a href="#"></a></li>';
        wpp_list.parentNode.appendChild(dummy_list);

        const dummy_list_item_styles = getComputedStyle(dummy_list.querySelector('li')),
            dummy_link_item_styles = getComputedStyle(dummy_list.querySelector('li a'));

        base_styles.innerHTML = `.wpp-list li {font-size: ${dummy_list_item_styles.fontSize}}`;
        base_styles.innerHTML += `.wpp-list li a {color: ${dummy_link_item_styles.color}}`;

        wpp_list.parentNode.removeChild(dummy_list);

        const wpp_list_sr = wpp_list.attachShadow({mode: "open"});

        wpp_list_sr.append(base_styles);

        while (wpp_list.firstElementChild) {
            wpp_list_sr.append(wpp_list.firstElementChild);
        }
    };

    return {
        get,
        post,
        ajax,
        theme
    };

})();

(() => {
    if ( ! Object.keys(wpp_params).length ) {
        console.error('WPP params not found, if you are using a JS minifier tool please add wpp.min.js to its exclusion list');
        return;
    }

    const post_id = Number(wpp_params.postId);
    let do_request = true;

    if ( post_id ) {
        if ( '1' == wpp_params.sampling ) {
            const num = Math.floor(Math.random() * wpp_params.samplingRate) + 1;
            do_request = ( 1 === num );
        }

        if ( 'boolean' === typeof window.wpp_do_request ) {
            do_request = window.wpp_do_request;
        }

        if ( do_request ) {
            WordPressPopularPosts.post(
                `${wpp_params.apiUrl}/v2/views/${post_id}`,
                `sampling=${wpp_params.sampling}&sampling_rate=${wpp_params.samplingRate}`,
                ( response ) => {
                    if ( wpp_params.debug && window.console ) {
                        console.log(JSON.parse(response));
                    }
                },
                {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-WP-Nonce': wpp_params.token
                }
            );
        }
    }
})();

document.addEventListener('DOMContentLoaded', () => {
    if ( ! Object.keys(wpp_params).length ) {
        return;
    }

    const widget_placeholders = document.querySelectorAll('.wpp-widget-block-placeholder, .wpp-shortcode-placeholder');
    widget_placeholders.forEach((widget_placeholder) => fetchWidget(widget_placeholder));

    const sr = document.querySelectorAll('.popular-posts-sr');
    sr.forEach((s) => WordPressPopularPosts.theme(s));

    function fetchWidget(widget_placeholder) {
        let params = '';
        const json_tag = widget_placeholder.parentNode.querySelector('script[type="application/json"]');

        if ( json_tag ) {
            const args = JSON.parse(json_tag.textContent.replace(/[\n\r]/g, ''));
            params = JSON.stringify(args);
        }

        WordPressPopularPosts.post(
            `${wpp_params.apiUrl}/v2/widget?is_single=${wpp_params.postId}${wpp_params.lang ? `&lang=${wpp_params.lang}` : ''}`,
            params,
            response => renderWidget(response, widget_placeholder),
            {
                'Content-Type': 'application/json',
                'X-WP-Nonce': wpp_params.token
            }
        );
    }

    function renderWidget(response, widget_placeholder) {
        widget_placeholder.insertAdjacentHTML('afterend', JSON.parse(response).widget);

        const parent = widget_placeholder.parentNode,
            sr = parent.querySelector('.popular-posts-sr'),
            json_tag = parent.querySelector('script[type="application/json"]');

        if ( json_tag ) {
            parent.removeChild(json_tag);
        }

        parent.removeChild(widget_placeholder);
        parent.classList.add('wpp-ajax');

        if ( sr ) {
            WordPressPopularPosts.theme(sr);
        }

        const event = new Event('wpp-onload', { bubbles: true, cancelable: false });
        parent.dispatchEvent(event);
    }
});
