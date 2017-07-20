module.exports = function( grunt ) {

	'use strict';
	var banner = '/**\n * <%= pkg.homepage %>\n * Copyright (c) <%= grunt.template.today("yyyy") %>\n * This file is generated automatically. Do not edit.\n */\n';
	// Project configuration
	grunt.initConfig( {

		pkg: grunt.file.readJSON( 'package.json' ),

		addtextdomain: {
			options: {
				textdomain: 'simpletts',
			},
			target: {
				files: {
					src: [ '*.php', '**/*.php', '!node_modules/**', '!php-tests/**', '!bin/**' ]
				}
			}
		},

		sass: {
			options: {
				sourceComments: false
			},
			compile: {
				files: {
					'assets/css/simpletts.css' : 'assets/css/scss/simpletts.scss',
				}
			}
		},

		postcss: {
			options: {
				processors: [
					require('autoprefixer')(),
				]
			},
			dist: {
				src: 'assets/css/*.css'
			}
		},

		makepot: {
			target: {
				options: {
					domainPath: '/languages',
					mainFile: 'simpletts.php',
					potFilename: 'simpletts.pot',
					potHeaders: {
						poedit: true,
						'x-poedit-keywordslist': true
					},
					type: 'wp-plugin',
					updateTimestamp: true
				}
			}
		},
	} );

	grunt.loadNpmTasks( 'grunt-sass' );
	grunt.loadNpmTasks( 'grunt-postcss' );
	grunt.loadNpmTasks( 'grunt-wp-i18n' );
	grunt.registerTask( 'default', [ 'styles' ] );
	grunt.registerTask( 'styles', [ 'sass', 'postcss' ] );
	grunt.registerTask( 'i18n', ['addtextdomain', 'makepot'] );

	grunt.util.linefeed = '\n';

};
