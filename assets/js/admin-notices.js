document.addEventListener('DOMContentLoaded', function(){
    // Performance Nag event handlers
    let btn_nag_dismiss = document.querySelector('.wpp-dismiss-performance-notice'),
        btn_nag_remind =  document.querySelector('.wpp-remind-performance-notice');

    btn_nag_dismiss.addEventListener('click', function(e){
        e.preventDefault();

        let me = e.target;

        me.classList.add('disabled');
        btn_nag_remind.classList.add('disabled');

        me.parentNode.querySelector('.spinner').classList.add('is-active');

        ajax(
            'POST',
            ajaxurl,
            'action=wpp_handle_performance_notice&dismiss=1&token=' + wpp_admin_notices_params.nonce_performance_nag,
            function(response) {
                let json = JSON.parse(response);

                if ( 'success' == json.status ) {
                    let notice = me.parentNode.parentNode;
                    notice.parentNode.removeChild(notice);
                } else {
                    alert('Something went wrong, please try again later');
                }

                me.classList.remove('disabled');
                btn_nag_remind.classList.remove('disabled');
                me.parentNode.querySelector('.spinner').classList.remove('is-active');
            }
        );
    });

    btn_nag_remind.addEventListener('click', function(e){
        e.preventDefault();

        let me = e.target;

        me.classList.add('disabled');
        btn_nag_dismiss.classList.add('disabled');

        me.parentNode.querySelector('.spinner').classList.add('is-active');

        ajax(
            'POST',
            ajaxurl,
            'action=wpp_handle_performance_notice&dismiss=-1&token=' + wpp_admin_notices_params.nonce_performance_nag,
            function(response) {
                let json = JSON.parse(response);

                if ( 'success' == json.status ) {
                    let notice = me.parentNode.parentNode;
                    notice.parentNode.removeChild(notice);
                } else {
                    alert('Something went wrong, please try again later');
                }

                me.classList.remove('disabled');
                btn_nag_remind.classList.remove('disabled');
                me.parentNode.querySelector('.spinner').classList.remove('is-active');
            }
        );
    });

    /** Helper functions */

    function ajax(method, url, params, callback) {
        let xhr = new XMLHttpRequest(),
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
    }
});