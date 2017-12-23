const path = require('path')
const WorkboxPlugin = require('workbox-webpack-plugin')
const DIST_DIR = 'public/compiled'

module.exports = {
    entry: './index.js',
    output: {
        filename: 'bundle.js',
        path: path.resolve(__dirname, DIST_DIR)
    },
    plugins: [
        new WorkboxPlugin({
            globDirectory: DIST_DIR,
            globPatterns: ['**/*.{js,css}'],
            swDest: path.join(DIST_DIR, 'sw.js')
        })
    ]
}