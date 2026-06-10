import { nodeResolve } from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import postcss from 'rollup-plugin-postcss';
import terser from '@rollup/plugin-terser';

const isProduction = process.env.NODE_ENV === 'production';
const fileName = isProduction ? 'venobox-wp-bundle.min' : 'venobox-wp-bundle';

export default {
  input: 'src/main.js',
  output: {
    // Usiamo dir: '.' per far sì che i percorsi definiti sotto siano relativi alla root del plugin
    dir: '.',
    entryFileNames: `js/${fileName}.js`,
    format: 'iife',
    name: 'VenoBoxWPBundle',
    sourcemap: false
  },
  plugins: [
    nodeResolve(),
    commonjs(),
    postcss({
      // Ora questo percorso sarà relativo alla root (perché dir è '.')
      extract: `css/${fileName}.css`, 
      minimize: isProduction,
    }),
    isProduction && terser()
  ]
};
