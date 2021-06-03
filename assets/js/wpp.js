var wpp_params = null;
var WordPressPopularPosts = (function(){

    "use strict";

    var noop = function(){};
    var supportsShadowDOMV1 = !! HTMLElement.prototype.attachShadow;

    var get = function( url, params, callback ){
        callback = ( 'function' === typeof callback ) ? callback : noop;
        ajax( "GET", url, params, callback );
    };

    var post = function( url, params, callback ){
        callback = ( 'function' === typeof callback ) ? callback : noop;
        ajax( "POST", url, params, callback );
    };

    var ajax = function( method, url, params, callback ){
        /* Create XMLHttpRequest object and set variables */
        var xhr = new XMLHttpRequest(),
        target = url,
        args = params,
        valid_methods = ["GET", "POST"];
        method = -1 != valid_methods.indexOf( method ) ? method : "GET";
        /* Set request method and target URL */
        xhr.open( method, target + ( "GET" == method ? '?' + args : '' ), true );
        /* Set request headers */
        if ( "POST" == method ) {
            xhr.setRequestHeader( "Content-type", "application/x-www-form-urlencoded" );
        }
        xhr.setRequestHeader( "X-Requested-With","XMLHttpRequest" );
        /* Hook into onreadystatechange */
        xhr.onreadystatechange = function() {
            if ( 4 === xhr.readyState && 200 <= xhr.status && 300 > xhr.status ) {
                if ( 'function' === typeof callback ) {
                    callback.call( undefined, xhr.response );
                }
            }
        };
        /* Send request */
        xhr.send( ( "POST" == method ? args : null ) );
    };

    var theme = function(wpp_list) {
        if ( supportsShadowDOMV1 ) {
            let base_styles = document.createElement('style'),
                dummy_list = document.createElement('ul');

            dummy_list.innerHTML = '<li><a href="#"></a></li>';
            wpp_list.parentNode.appendChild(dummy_list);

            let dummy_list_item_styles = getComputedStyle(dummy_list.querySelector('li')),
                dummy_link_item_styles = getComputedStyle(dummy_list.querySelector('li a'));

            base_styles.innerHTML = '.wpp-list li {font-size: '+ dummy_list_item_styles.fontSize +'}';
            base_styles.innerHTML += '.wpp-list li a {color: '+ dummy_link_item_styles.color +'}';

            wpp_list.parentNode.removeChild(dummy_list);

            let wpp_list_sr = wpp_list.attachShadow({mode: "open"});

            wpp_list_sr.append(base_styles);

            while(wpp_list.firstElementChild) {
                wpp_list_sr.append(wpp_list.firstElementChild);
            }
        }
    };

    return {
        get: get,
        post: post,
        ajax: ajax,
        theme: theme
    };

})();

(function(){
    try {
        var wpp_json = document.querySelector("script#wpp-json"),
            do_request = true;

        wpp_params = JSON.parse(wpp_json.textContent);

        if ( wpp_params.ID ) {
            if ( '1' == wpp_params.sampling_active ) {
                var num = Math.floor(Math.random() * wpp_params.sampling_rate) + 1;
                do_request = ( 1 === num );
            }

            if ( do_request ) {
                WordPressPopularPosts.post(
                    wpp_params.ajax_url,
                    "_wpnonce=" + wpp_params.token + "&wpp_id=" + wpp_params.ID + "&sampling=" + wpp_params.sampling_active + "&sampling_rate=" + wpp_params.sampling_rate,
                    function( response ) {
                        wpp_params.debug&&window.console&&window.console.log&&window.console.log(JSON.parse(response));
                    }
                );
            }
        }
    } catch (err) {
        console.error("WPP: Couldn't read JSON data");
    }
})();

document.addEventListener('DOMContentLoaded', function() {
    var widget_placeholders = document.querySelectorAll('.wpp-widget-placeholder');

    var w = 0;

    while ( w < widget_placeholders.length ) {
        fetchWidget(widget_placeholders[w]);
        w++;
    }

    var sr = document.querySelectorAll('.popular-posts-sr');

    if ( sr.length ) {
        for( var s = 0; s < sr.length; s++ ) {
            WordPressPopularPosts.theme(sr[s]);
        }
    }

    function fetchWidget(widget_placeholder) {
        WordPressPopularPosts.get(
            wpp_params.ajax_url + '/widget/' + widget_placeholder.getAttribute('data-widget-id').split('-')[1],
            'is_single=' + wpp_params.ID + ( wpp_params.lang ? '&lang=' + wpp_params.lang : '' ),
            function(response) {
                widget_placeholder.insertAdjacentHTML('afterend', JSON.parse(response).widget);

                let parent = widget_placeholder.parentNode,
                    sr = parent.querySelector('.popular-posts-sr');

                parent.removeChild(widget_placeholder);

                if ( sr ) {
                    WordPressPopularPosts.theme(sr);
                }

                let event = null;

                if ( 'function' === typeof(Event) ) {
                    event = new Event("wpp-onload", {"bubbles": true, "cancelable": false});
                } /* Fallback for older browsers */
                else {
                    if ( document.createEvent ) {
                        event = document.createEvent('Event');
                        event.initEvent("wpp-onload", true, false);
                    }
                }

                if ( event ) {
                    parent.dispatchEvent(event);
                }
            }
        );
    }
});
