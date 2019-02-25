const path = require('path')
const UglifyJsPlugin = require('uglifyjs-webpack-plugin')

module.exports = {
	entry: {
		main: path.resolve(__dirname, '../src/main.js'),
		preview: path.resolve(__dirname, '../src/preview.js'),
	},
	output: {
		path: path.resolve(__dirname, '../../src/resources/'),
		filename: '[name].js',
	},
	externals: {
		jquery: 'jQuery',
		craft: 'Craft',
		garnish: 'Garnish',
	},
	plugins: [
		new UglifyJsPlugin(),
	],
	module: {
		rules: [
			{
				test: /\.jsx?$/,
				exclude: /node_modules/,
				use: {
					loader: 'babel-loader',
					options: { presets: ['env'] },
				},
			},
			{
				test: /\.scss$/,
				use: [
					{ loader: 'style-loader' },
					{ loader: 'css-loader' },
					{ loader: 'sass-loader' },
				],
			},
			{
				test: /\.(png|svg)$/,
				use: { loader: 'url-loader' },
			},
		],
	},
}
