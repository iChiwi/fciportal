import purgecssModule from '@fullhuman/postcss-purgecss';

const purgecss = purgecssModule.default;

export default {
  plugins: [
    purgecss({
      content: [
        './home.php',
        './admin/*.php',
        './gpa-calc/*.php',
        './links/*.php',
        './lookup/*.php',
        './results/*.php',
        './status/*.php',
        './student/*.php',
        './static/js/**/*.js'
      ],
      defaultExtractor: content => content.match(/[\w-/:]+(?<!:)/g) || [],
    }),
  ],
};
