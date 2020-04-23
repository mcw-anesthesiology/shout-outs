/* eslint-env node */

module.exports = api => {
	api.cache(true);

	return {
		presets: [
			['@babel/preset-env', {
				targets: {
					browsers: [
						'> 1%',
						'last 2 versions',
						'ie>=11'
					]
				},
				debug: false,
				modules: false,
				corejs: 3,
				useBuiltIns: 'usage'
			}]
		],
		plugins: [
			'@babel/plugin-transform-shorthand-properties',
			'@babel/plugin-proposal-object-rest-spread',
			'@babel/plugin-proposal-optional-chaining'
		]
	};
};
