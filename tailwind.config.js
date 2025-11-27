/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.html",
        "./resources/**/*.js",
        "./resources/**/*.jsx",
        "./resources/**/*.ts",
        "./resources/**/*.tsx",
        "./resources/**/*.blade.php",
    ],
    theme: {
        extend: {
            fontFamily: {
				sans: ['Inter','sans-serif']
			},
			colors: {
				'bcblue' : '#699DB1',
				'bcblue-dark' : '#4d8094',
				'bcblue-light' : '#87b0c0',
				'bcred' : '#a02a34',
				'bcred-light' : '#D35D67',
				'bcred-dark' : '#87111B',
				'bcyellow' : '#fac13f',
				'bcyellow-dark' : '#E1A826',
				'bcyellow-light' : '#FFDB59',
				'bcbrown' : '#6e6134',
				'bcbrown-light' : '#887B4E',
				'bcbrown-dark' : '#55481B'


			}
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
		require('@tailwindcss/aspect-ratio')
    ],
};
