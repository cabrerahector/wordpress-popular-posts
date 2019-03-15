var WPPChart = (function() {
    "use strict";

    /**
     * Private functions and variables
     */

    var defaults = {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                {
                    label: "",
                    fill: true,
                    lineTension: 0.2,
                    borderWidth: 3,
                    backgroundColor: "rgba(221, 66, 66, 0.8)",
                    borderColor: "#881111",
                    borderCapStyle: 'butt',
                    borderDash: [],
                    borderDashOffset: 0.0,
                    borderJoinStyle: 'miter',
                    pointBorderColor: "#881111",
                    pointBackgroundColor: "#fff",
                    pointBorderWidth: 2,
                    pointHoverRadius: 4,
                    pointHoverBackgroundColor: "#881111",
                    pointHoverBorderColor: "#881111",
                    pointHoverBorderWidth: 3,
                    pointRadius: 3,
                    pointHitRadius: 10,
                    data: [],
                },
                {
                    label: "",
                    fill: true,
                    lineTension: 0.2,
                    borderWidth: 3,
                    backgroundColor: "rgba(136, 17, 17, 0.3)",
                    borderColor: "#a80000",
                    borderCapStyle: 'butt',
                    borderDash: [],
                    borderDashOffset: 0.0,
                    borderJoinStyle: 'miter',
                    pointBorderColor: "#a80000",
                    pointBackgroundColor: "#fff",
                    pointBorderWidth: 2,
                    pointHoverRadius: 4,
                    pointHoverBackgroundColor: "#a80000",
                    pointHoverBorderColor: "#a80000",
                    pointHoverBorderWidth: 3,
                    pointRadius: 3,
                    pointHitRadius: 10,
                    data: [],
                }
            ]
        },
        options: {
            legend: {
                display: true,
                labels: {
                    fontColor: '#23282d',
                    fontFamily: '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif',
                    fontSize: 12
                }
            },
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: 2,
                    right: 5,
                    bottom: 0,
                    left: 5
                }
            },
            scales: {
                xAxes: [{
                    display: true,
                    gridLines: {
                        display: false,
                    },
                    ticks: {
                        fontSize: 10,
                        fontColor: '#23282d',
                        autoSkip: false,
                        maxRotation: 90,
                        minRotation: 90
                    }
                }],
                yAxes: [{
                    display: false
                }]
            }
        }
    },
    chart = null,
    canRender = !! window.CanvasRenderingContext2D,
    element = null,
    cvs = null;

    var canRender = function(){
        return canRender;
    };

    // Source: http://stackoverflow.com/a/5624139
    var HexToRGB = function( hex ){
        var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;

        hex = hex.replace(shorthandRegex, function( m, r, g, b ) {
            return r + r + g + g + b + b;
        });

        var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);

        return result ? {
            r: parseInt( result[1], 16 ),
            g: parseInt( result[2], 16 ),
            b: parseInt( result[3], 16 )
        } : null;
    };

    /**
     * Public functions
     */

    var init = function(container, options){
        if ( ! canRender() ) {
            throw new Error('Your browser is too old, WPPChart cannot create its data chart.');
        }

        if ( 'undefined' == typeof container ) {
            throw new Error('Please tell WPPChart where to inject the chart.');
        }

        element = document.getElementById(container);

        if ( ! element ) {
            throw new Error('WPPChart cannot find ' + container);
        }

        if ( 'undefined' == typeof Chart ) {
            throw new Error('ChartJS library not found');
        }

        cvs = document.createElement('canvas');
        element.appendChild(cvs);
    };

    var populate = function(data){
        if ( chart ) {
            chart.destroy();
        }

        var config = defaults;

        config.data.labels = data.labels;
        config.data.datasets[0].label = data.datasets[0].label;
        config.data.datasets[0].data = data.datasets[0].data;
        config.data.datasets[1].label = data.datasets[1].label;
        config.data.datasets[1].data = data.datasets[1].data;


        var rgb_comments = HexToRGB(wpp_chart_params.colors[2]);
        config.data.datasets[1].backgroundColor = "rgba(" + rgb_comments.r + ", " + rgb_comments.g + ", " + rgb_comments.b + ", 0.9)";
        config.data.datasets[1].borderColor = wpp_chart_params.colors[2];
        config.data.datasets[1].pointBorderColor = wpp_chart_params.colors[2];
        config.data.datasets[1].pointHoverBackgroundColor = wpp_chart_params.colors[2];
        config.data.datasets[1].pointHoverBorderColor = wpp_chart_params.colors[2];

        var rgb_views = HexToRGB(wpp_chart_params.colors[3]);
        config.data.datasets[0].backgroundColor = "rgba(" + rgb_views.r + ", " + rgb_views.g + ", " + rgb_views.b + ",  0.7)";
        config.data.datasets[0].borderColor = wpp_chart_params.colors[3];
        config.data.datasets[0].pointBorderColor = wpp_chart_params.colors[3];
        config.data.datasets[0].pointHoverBackgroundColor = wpp_chart_params.colors[3];
        config.data.datasets[0].pointHoverBorderColor = wpp_chart_params.colors[3];

        chart = new Chart(cvs, config);
    };

    /**
     * Provide access to public methods
     */

    return {
        init: init,
        populate: populate,
        canRender: canRender
    };
})();
