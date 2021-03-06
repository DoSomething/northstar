const { colors } = require('tailwindcss/defaultTheme');

module.exports = {
  theme: {
    screens: {
      xsm: '360px',
      sm: '480px',
      md: '760px',
      lg: '960px',
      xl: '1060px',
      xxl: '1280px',
    },
    colors: {
      transparent: colors.transparent,
      black: colors.black,
      white: colors.white,
      gray: colors.gray,
      blue: {
        '100': '#5145ff',
        '200': '#4a3ef7',
        '300': '#4337de',
        '400': '#3b31c4',
        '500': '#332baa',
        '600': '#2c2491',
        '700': '#241e78',
        '800': '#1c185e',
        '900': '#151145',
      },
      orange: {
        '100': '#ff6e4a',
        '200': '#ff6640',
        '300': '#ff5e36',
        '400': '#ff562b',
        '500': '#ff4d22',
        '600': '#e5461e',
        '700': '#cc3e1b',
        '800': '#b33617',
        '900': '#992f14',
      },
      purple: {
        '100': '#c861ff',
        '200': '#c354ff',
        '300': '#be49fc',
        '400': '#ab42e3',
        '500': '#983ac9',
        '600': '#8433b0',
        '700': '#712c96',
        '800': '#5e247d',
        '900': '#4b1d63',
      },
      teal: {
        '100': '#8cfff9',
        '200': '#66fff7',
        '300': '#40fff5',
        '400': '#35fcf2',
        '500': '#30e3da',
        '600': '#2ac9c1',
        '700': '#25b0a9',
        '800': '#209691',
        '900': '#1a7d78',
      },
      yellow: {
        '100': '#ffe894',
        '200': '#ffe27a',
        '300': '#ffdd61',
        '400': '#ffd747',
        '500': '#fcce2e',
        '600': '#e3bb29',
        '700': '#c9a624',
        '800': '#b09120',
        '900': '#967c1b',
      },
    },
    fontFamily: {
      'source-sans': [
        '"Source Sans Pro"',
        '"Helvetica Neue"',
        'Helvetica',
        'Arial',
        'sans-serif',
      ],
      'league-gothic': [
        '"League Gothic"',
        'Impact',
        '"Franklin Gothic Bold"',
        '"Arial Black"',
        'sans-serif',
      ],
    },
    // @TODO: these font sizes are subject to change; requires approval from Luke.
    fontSize: {
      xs: '0.5rem',
      sm: '0.75rem',
      base: '1.125rem',
      lg: '1.375rem',
      xl: '1.688rem',
      '2xl': '2.531rem',
      '3xl': '3.797rem',
      '4xl': '5.695rem',
      '5xl': '8.543rem',
    },
    extend: {
      padding: {
        '1/4': '25%',
        '1/3': '33%',
        '1/2': '50%',
      },
    },
  },
  corePlugins: {
    // Avoid conflict with Forge's 'container' pattern:
    container: false,
  },
  variants: {},
  plugins: [],
};
