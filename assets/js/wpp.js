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
            let link_styles = document.createElement('style'),
                dummy_link = document.createElement('a');

            wpp_list.parentNode.appendChild(dummy_link);

            let dummy_link_styles = getComputedStyle(dummy_link);
            link_styles.innerHTML = '.wpp-list li a {color: '+ dummy_link_styles.color +'}';

            wpp_list.parentNode.removeChild(dummy_link);

            let wpp_list_sr = wpp_list.attachShadow({mode: "open"});

            wpp_list_sr.append(link_styles);

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

if (
    "undefined" !== typeof wpp_params 
    && wpp_params.ID > 0
) {
    var do_request = true;

    if ( '1' == wpp_params.sampling_active ) {
        var num = Math.floor(Math.random() * wpp_params.sampling_rate) + 1;
        do_request = ( 1 === num );
    }

    if ( do_request ) {
        WordPressPopularPosts.post(
            wpp_params.ajax_url,
            "_wpnonce=" + wpp_params.token + "&wpp_id=" + wpp_params.ID + "&sampling=" + wpp_params.sampling_active + "&sampling_rate=" + wpp_params.sampling_rate,
            function( response ){
                wpp_params.debug&&window.console&&window.console.log&&window.console.log(JSON.parse(response));
            }
        );
    }
}
