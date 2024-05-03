import { src, dest, watch, series } from "gulp";
import autoprefixer from "gulp-autoprefixer";
import cleanCss from "gulp-clean-css";
import minify from "gulp-minify";
import rename from "gulp-rename";
import { deleteAsync as del } from "del";

const paths = {
  styles: {
    src: "./admin/src/css/*.css",
    dest: "./admin/css",
  },
  scripts: {
    src: "./admin/src/js/*.js",
    dest: "./admin/js",
  },
};

function styles() {
  return src(paths.styles.src)
    .pipe(
      autoprefixer({
        overrideBrowserslist: ["last 10 versions", "> 1% in GR", "ie 11"],
      })
    )
    .pipe(cleanCss())
    .pipe(
      rename({
        suffix: ".min",
      })
    )
    .pipe(dest(paths.styles.dest));
}

function scripts() {
  return src(paths.scripts.src)
    .pipe(
      minify({
        ext: {
          min: ".min.js",
        },
        ignoreFiles: ["*.min.js"],
        noSource: true,
      })
    )
    .pipe(dest(paths.scripts.dest));
}

function cleanDist() {
  return del("dist/**", { force: true });
}

function copyToDist() {
  return src([
    "./**",
    "!./package.json",
    "!./package-lock.json",
    "!./LICENSE",
    "!./README.md",
    "!./gulpfile.js",
    "!./node_modules/**",
    "!./dist/**",
    "!./.github/**",
    "!./.git/**",
    "!./.wordpress-org/**",
    "!./admin/src/**",
  ]).pipe(dest("./dist"));
}

function watchAll() {
  watch(paths.styles.src, styles);
  watch(paths.scripts.src, scripts);
}

const build = series(styles, scripts);
const archive = series(styles, scripts, cleanDist, copyToDist);

export { build, archive, watchAll as watch };

export default watchAll;
