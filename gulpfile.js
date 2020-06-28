const gulp = require('gulp')

// CSS related plugins.
const autoprefixer = require('gulp-autoprefixer')

// Utility related plugins.
const notify = require('gulp-notify')
const wpPot = require('gulp-wp-pot')

const CSSsrc = './admin/css/*.css'
const PHPsrc = ['./**/*.php', './*.php']

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
                message: 'TASK: CSS Prefixed!',
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
                message: 'TASK: Translations Updated!',
                onLast: true,
            })
        )
}

function watch() {
    gulp.watch(CSSsrc, translate)
    gulp.watch(PHPsrc, styles)
}

var build = gulp.parallel(translate, styles)

exports.watch = watch
exports.default = build
