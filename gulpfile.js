const gulp = require('gulp')

// CSS related plugins.
const autoprefixer = require('gulp-autoprefixer')

// Utility related plugins.
const notify = require('gulp-notify');
const minify = require('gulp-minify');
const cleanCss = require('gulp-clean-css');
const rename = require("gulp-rename");
const CSSsrc = './admin/src/css/*.css';
const JSsrc = './admin/src/js/*.js';
const PHPsrc = ['./**/*.php', './*.php', '!./dist/**/*.php'];

function styles() {
    return gulp
        .src(CSSsrc)
        .pipe(
            autoprefixer({
                overrideBrowserslist: ['last 10 versions', '> 1% in GR', 'ie 11'],
            })
        )
        .pipe(cleanCss())
        .pipe(rename({
            suffix: ".min"
        }))
        .pipe(gulp.dest('./admin/css'))

        .pipe(
            notify({
                message: 'CSS Optimized',
                onLast: true,
            })
        )
}

function scripts() {
    return gulp
        .src(JSsrc)
        .pipe(minify({
            ext: {
                min: '.min.js'
            },
            ignoreFiles: ['*.min.js'],
            noSource: true
        }))
        .pipe(gulp.dest('./admin/js'))
        .pipe(
            notify({
                message: 'JS Optimized',
                onLast: true,
            })
        )
}

function buildVersion() {
    return gulp
        .src(['./**', '!./package.json', '!./package-lock.json', '!./LICENSE', '!./README.md', '!./gulpfile.js', '!./node_modules/**', '!./docs/**', '!./dist/**', '!./assets/**', '!./admin/src/**'])
        .pipe(gulp.dest('./dist'))
        .pipe(
            notify({
                message: 'Build Complete',
                onLast: true,
            })
        )
}

function watch() {
    gulp.watch(CSSsrc, styles);
    gulp.watch(JSsrc, scripts);
}

var build = gulp.series(styles, scripts, buildVersion);

exports.build = build;
exports.watch = watch;
exports.default = watch;
