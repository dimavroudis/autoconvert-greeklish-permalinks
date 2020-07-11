const gulp = require('gulp')

// CSS related plugins.
const autoprefixer = require('gulp-autoprefixer')

// Utility related plugins.
const notify = require('gulp-notify');
const wpPot = require('gulp-wp-pot');
const zip = require('gulp-zip');
const copy = require('gulp-copy');
const CSSsrc = './admin/css/*.css';
const PHPsrc = ['./**/*.php', './*.php', '!./dist/**/*.php'];

function styles() {
    return gulp
        .src(CSSsrc)
        .pipe(
            autoprefixer({
                overrideBrowserslist: ['last 10 versions', '> 1% in GR', 'ie 9'],
            })
        )
        .pipe(gulp.dest('./admin/css'))
        .pipe(
            notify({
                message: 'CSS Prefixed',
                onLast: true,
            })
        )
}

function buildVersion() {
    return gulp
        .src(['./**', '!./package.json', '!./package-lock.json','!./LICENSE', '!./README.md', '!./gulpfile.js', '!./node_modules/**', '!./docs/**', '!./dist/**', '!./assets/**'])
        .pipe(gulp.dest('./dist'))
        .pipe(
            notify({
                message: 'Build Complete',
                onLast: true,
            })
        )
}


function translate() {
    return gulp
        .src(PHPsrc)
        .pipe(
            wpPot({
                domain: 'agp',
                package: 'agp',
            })
        )
        .pipe(gulp.dest('./languages/agp.pot'))
        .pipe(
            notify({
                message: 'Translations Updated',
                onLast: true,
            })
        )
}

function watch() {
    gulp.watch(CSSsrc, translate);
    gulp.watch(PHPsrc, styles);
}

var build = gulp.series(styles, buildVersion);

exports.build = build;
exports.watch = watch;
exports.default = watch;
