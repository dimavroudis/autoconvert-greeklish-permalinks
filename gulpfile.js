const gulp = require("gulp");

// CSS related plugins.
const autoprefixer = require("gulp-autoprefixer");
const cleanCss = require("gulp-clean-css");

// JS related plugins.
const minify = require("gulp-minify");

// Utility related plugins.
const rename = require("gulp-rename");
const del = require("del");

// File paths/globs
const CSSsrc = "./admin/src/css/*.css";
const JSsrc = "./admin/src/js/*.js";

function styles() {
  return gulp
    .src(CSSsrc)
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
    .pipe(gulp.dest("./admin/css"));
}

function scripts() {
  return gulp
    .src(JSsrc)
    .pipe(
      minify({
        ext: {
          min: ".min.js",
        },
        ignoreFiles: ["*.min.js"],
        noSource: true,
      })
    )
    .pipe(gulp.dest("./admin/js"));
}

function cleanDist() {
  return del("dist/**", { force: true });
}

function copyToDist() {
  return gulp
    .src([
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
    ])
    .pipe(gulp.dest("./dist"));
}

function watch() {
  gulp.watch(CSSsrc, styles);
  gulp.watch(JSsrc, scripts);
}

const build = gulp.series(styles, scripts);
const archive = gulp.series(styles, scripts, cleanDist, copyToDist);

exports.build = build;
exports.archive = archive;
exports.watch = watch;
exports.default = watch;
