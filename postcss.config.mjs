import purgecssModule from "@fullhuman/postcss-purgecss";

const purgecss = purgecssModule.default;

export default {
  plugins: [
    purgecss({
      content: [
        "./home.php",
        "./admin/*.php",
        "./links/*.php",
        "./studentcode/*.php",
        "./studentresults/*.php",
        "./status/*.php",
        "./study/*.php",
        "./static/js/**/*.js",
      ],
      defaultExtractor: (content) => content.match(/[\w-/:]+(?<!:)/g) || [],
    }),
  ],
};
