const defaultConfig = require("@wordpress/scripts/config/webpack.config");
const path = require('path');

module.exports = {
    ...defaultConfig,
    entry: {
        'block-wpp-widget': './src/Block/Widget/widget.js'
    },
    output: {
        path: path.join(__dirname, './assets/js/blocks'),
        filename: '[name].js'
    }
}