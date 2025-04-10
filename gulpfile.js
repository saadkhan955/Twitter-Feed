const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const sourcemaps = require('gulp-sourcemaps');
const postcss = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
const cleanCSS = require('gulp-clean-css');

const paths = {
  scss: {
    src: 'scss/**/*.scss',
    main: 'scss/styles.scss',
    dest: 'css',
  }
};

function compileSass() {
  return gulp.src(paths.scss.main)
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(postcss([autoprefixer()]))
    .pipe(cleanCSS())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(paths.scss.dest));
}

function watch() {
  gulp.watch(paths.scss.src, compileSass);
}

exports.default = gulp.series(compileSass, watch);
exports.build = compileSass;
