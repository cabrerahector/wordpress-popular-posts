const WPPChart = (() => {
    'use strict';

    /**
     * Private functions and variables
     */

    let chart = null,
        element = null,
        cvs = null;

    const defaults = {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                {
                    label: '',
                    fill: true,
                    lineTension: 0.2,
                    borderWidth: 3,
                    backgroundColor: 'rgba(221, 66, 66, 0.8)',
                    borderColor: '#881111',
                    borderCapStyle: 'butt',
                    borderDash: [],
                    borderDashOffset: 0.0,
                    borderJoinStyle: 'miter',
                    pointBorderColor: '#881111',
                    pointBackgroundColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 4,
                    pointHoverBackgroundColor: '#881111',
                    pointHoverBorderColor: '#881111',
                    pointHoverBorderWidth: 3,
                    pointRadius: 3,
                    pointHitRadius: 10,
                    data: [],
                },
                {
                    label: '',
                    fill: true,
                    lineTension: 0.2,
                    borderWidth: 3,
                    backgroundColor: 'rgba(136, 17, 17, 0.3)',
                    borderColor: '#a80000',
                    borderCapStyle: 'butt',
                    borderDash: [],
                    borderDashOffset: 0.0,
                    borderJoinStyle: 'miter',
                    pointBorderColor: '#a80000',
                    pointBackgroundColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 4,
                    pointHoverBackgroundColor: '#a80000',
                    pointHoverBorderColor: '#a80000',
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
                    right: 0,
                    bottom: 5,
                    left: 0
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            lineHeight: 1,
                            size: 10
                        },
                        color: '#23282d',
                        autoSkip: false,
                        maxRotation: 90,
                        minRotation: 90
                    }
                },
                y: {
                    grid: {
                        display: true,
                        drawBorder: false
                    },
                    ticks: {
                        display: true
                    }
                }
            }
        },
    },
    canRender = !! window.CanvasRenderingContext2D;

    // Source: http://stackoverflow.com/a/5624139
    const HexToRGB = ( hex ) => {
        const shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;

        hex = hex.replace(shorthandRegex, ( _m, r, g, b ) => {
            return r + r + g + g + b + b;
        });

        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);

        return result ? {
            r: parseInt( result[1], 16 ),
            g: parseInt( result[2], 16 ),
            b: parseInt( result[3], 16 )
        } : null;
    };

    /**
     * Public functions
     */

    const init = (container) => {
        if ( ! canRender ) {
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

    const populate = (data) => {
        if ( chart ) {
            chart.destroy();
        }

        const totalComments = data.datasets[0].data.reduce(
            (accumulator, currentValue) => accumulator + parseInt(currentValue, 10),
            0,
        );

        const totalViews = data.datasets[1].data.reduce(
            (accumulator, currentValue) => accumulator + parseInt(currentValue, 10),
            0,
        );

        const config = defaults;

        config.options.scales.y.grid.display = ! ( totalComments <= 0 && totalViews <= 0 );
        config.options.scales.y.ticks.display = ! ( totalComments <= 0 && totalViews <= 0 );

        config.data.labels = data.labels;
        config.data.datasets[0].label = data.datasets[0].label;
        config.data.datasets[0].data = data.datasets[0].data;
        config.data.datasets[1].label = data.datasets[1].label;
        config.data.datasets[1].data = data.datasets[1].data;

        const colors_arr = wpp_chart_params.colors.slice(-2);

        const rgb_comments = HexToRGB(colors_arr[0]);
        config.data.datasets[1].backgroundColor = `rgba(${rgb_comments.r}, ${rgb_comments.g}, ${rgb_comments.b}, 0.9)`;
        config.data.datasets[1].borderColor = colors_arr[0];
        config.data.datasets[1].pointBorderColor = colors_arr[0];
        config.data.datasets[1].pointHoverBackgroundColor = colors_arr[0];
        config.data.datasets[1].pointHoverBorderColor = colors_arr[0];

        const rgb_views = HexToRGB(colors_arr[1]);
        config.data.datasets[0].backgroundColor = `rgba(${rgb_views.r}, ${rgb_views.g}, ${rgb_views.b},  0.7)`;
        config.data.datasets[0].borderColor = colors_arr[1];
        config.data.datasets[0].pointBorderColor = colors_arr[1];
        config.data.datasets[0].pointHoverBackgroundColor = colors_arr[1];
        config.data.datasets[0].pointHoverBorderColor = colors_arr[1];

        chart = new Chart(cvs, config);
    };

    /**
     * Provide access to public methods
     */

    return {
        init,
        populate,
        canRender: () => canRender
    };
})();
