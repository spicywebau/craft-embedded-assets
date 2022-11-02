const path = require('path')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const TerserPlugin = require('terser-webpack-plugin')

module.exports = {
  entry: {
    main: path.resolve(__dirname, 'src/assets/main/src/scripts/main.ts'),
    preview: path.resolve(__dirname, 'src/assets/preview/src/scripts/preview.ts')
  },
  output: {
    path: path.resolve(__dirname, 'src/assets/'),
    filename: '[name]/dist/scripts/[name].js'
  },
  externals: {
    jquery: 'jQuery',
    craft: 'Craft',
    garnish: 'Garnish'
  },
  resolve: {
    extensions: ['.ts', '.tsx']
  },
  optimization: {
    minimize: true,
    minimizer: [new TerserPlugin()]
  },
  module: {
    rules: [
      {
        use: ['ts-loader'],
        include: [
          path.resolve(__dirname, 'src/assets/main/src'),
          path.resolve(__dirname, 'src/assets/preview/src')
        ],
        test: /\.tsx?$/
      },
      {
        use: ['source-map-loader'],
        enforce: 'pre',
        test: /\.js$/
      },
      {
        use: [MiniCssExtractPlugin.loader, 'css-loader'],
        test: /\.css$/
      },
      {
        use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader'],
        test: /\.scss$/
      },
      {
        test: /\.(png|svg)$/,
        type: 'asset/inline'
      }
    ]
  },
  devtool: 'source-map',
  plugins: [new MiniCssExtractPlugin({
    filename: '[name]/dist/styles/[name].css'
  })]
}
