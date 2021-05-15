export default {
	input: './assets/scripts/index.js',
	output: [
		{
			file: 'dist/scripts/main.c.js',
			format: 'cjs'
		},
		{
			file: 'dist/scripts/main.umd.js',
			format: 'umd'
		},
		{
			file: 'dist/scripts/main.js',
			format: 'es'
		},
		{
			file: 'dist/scripts/main.iife.js',
			format: 'iife'
		},
	]
}
