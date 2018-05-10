'use strict';

var gulp = require('gulp-help')(require('gulp'));

// Plugins.
var gutil = require('gulp-util');
var jshint = require('gulp-jshint');
var uglify = require('gulp-uglify');
var rename = require('gulp-rename');
var sass = require('gulp-sass');

var sassPath = './scss/**/*.scss';

gulp.task('scss', 'Process SCSS using libsass',
  function () {
    var includePaths = [
      'node_modules/compass-mixins/lib'
    ];

    // Reference version of compiled files.
    // These can be used for debugging or determining changes.
    gulp.src(sassPath)
      .pipe(sass({
        // The nested output style is the most verbose one.
        outputStyle: 'nested',
        includePaths: includePaths
      }).on('error', sass.logError))
      .pipe(gulp.dest('./css'));
    // Production version of compiled files. These are used by default.
    gulp.src(sassPath)
      .pipe(sass({
        outputStyle: 'compressed',
        includePaths: includePaths
      }).on('error', sass.logError))
      // Add a .min to compiled files to separate them from the verbose set.
      .pipe(rename(function (path) {
        path.extname = '.min.css';
      }))
      .pipe(gulp.dest('./css'));
  }
);

gulp.task('watch', 'Watch and process JS and SCSS files', [ 'scss'],
  function() {
    gulp.watch(sassPath, ['scss']);
  }
);

gulp.task('default', ['help']);
