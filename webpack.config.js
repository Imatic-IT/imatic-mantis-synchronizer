const path = require('path');

const BUILD_DIR = path.resolve(__dirname, 'files/js/min');
const APP_DIR = path.resolve(__dirname, 'files/js');

module.exports = {
    entry: {
        app: `${APP_DIR}/app.js`,
        webhook: `${APP_DIR}/webhook.js`,
    },
    devtool: 'source-map',
    output: {
        path: BUILD_DIR,
        filename: '[name].min.js', // Použitie [name] pre dynamický názov výstupného súboru
    },
};