var gulp         = require( 'gulp' );
var sass         = require( 'gulp-sass' );
var plumber      = require( 'gulp-plumber' );
var postcss      = require( 'gulp-postcss' );
var autoprefixer = require( 'autoprefixer' );
var csso         = require( 'gulp-csso' );
var minify       = require( 'gulp-minify' );

gulp.task( 'styles', function() {
	return gulp.src( 'css/styles.scss' )
		.pipe( plumber() )
		.pipe( sass( {
			outputStyle: 'compressed'
		} ) )
		.pipe( postcss( [
			autoprefixer( {
				browsers: [
					'last 2 versions'
				]
			} )
		] ) )
		.pipe( csso() )
		.pipe( gulp.dest( 'css' ) );
} );

gulp.task( 'scripts', function() {
	return gulp.src( [
		'js/scripts.js'
	] )
	.pipe( plumber() )
	.pipe( minify( {
		ext: {
			min:'.min.js'
		},
		noSource: true,
		ignoreFiles: ['.min.js']
	} ) )
	.pipe( gulp.dest( 'js/' ) )
} );
