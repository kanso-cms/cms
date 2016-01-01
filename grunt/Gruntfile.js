module.exports = function(grunt) {
    grunt.initConfig({

        pkg: grunt.file.readJSON('package.json'),

        /* SASS */
        sass: {
          
            // Compile
            compile: {

            	// Compiler options
                options: {
                    style      : 'expanded',
                    sourcemap  : 'none',
                    noCache    : true,
                },

                // Compile
                files: {
                    'assets/css/style.css' : 'src/sass/style.scss',
                }
            },

        },

        /* CSS Minify */
        cssmin: {
            options: {
                shorthandCompacting: false,
                roundingPrecision: -1
            },
            target: {
                files: {
                    'assets/css/style.min.css' : 'assets/css/style.css' 
                }
            }
        },

        /* JAVASCRIPT */

        // BEAUTIFY SRC FILES 
        jsbeautifier: {
            files: ['src/js/**/*.js', "!src/js/**/*.min.js"],
        },

        // CONCAT 
        concat: {

        	// Concat options
            options: {
                separator: '\n',
            },

            // Writer Application 
            Writer: {

                src: [

                    // codemirror
                    'src/js/Vendor/CodeMirror/codemirror.js',
                    'src/js/Vendor/CodeMirror/codemirrorMarkdown.js',
                    'src/js/Vendor/CodeMirror/codemirrorSimpleScrollbars.js',

                    // highlight js
                    'src/js/Vendor/HighlightJS/highlight.js',

                    // markdown 2 html
                    'src/js/Vendor/MarkdownIt/markdownIt.js',

                    // Writer applicatoon
                    'src/js/Libs/Writer/variables.js',
                    'src/js/Libs/Writer/initialize.js',
                    'src/js/Libs/Writer/header.js',
                    'src/js/Libs/Writer/footer.js',
                    'src/js/Libs/Writer/codeMirror.js',
                    'src/js/Libs/Writer/dropZone.js',
                    'src/js/Libs/Writer/dropZoneHero.js',
                    'src/js/Libs/Writer/ajax.js',
                    'src/js/Libs/Writer/writingEvents.js',
                    'src/js/Libs/Writer/end.js',

                ],
                dest: 'assets/js/writer.js',
            },

            // Dropzone
            dropzone: {
                src: [
                    'src/js/Vendor/DropZone/dropzone.js',
                ],
                dest: 'assets/js/libs/dropzone.js',
            },

            // Scripts
            scripts: {
                src: [

                	// Vendor
                	'src/js/Vendor/SimpleAjax/SimpleAjax.js',
					'src/js/Vendor/VanillaMasker/vanillaMasker.js',
					
					// Libs
					'src/js/Libs/helper.js',
					'src/js/Libs/formValidator.js',
					'src/js/Libs/imageResizer.js',
					'src/js/Libs/fileUploader.js',
					'src/js/Libs/pluralize.js',
					'src/js/Authentification/encrypt.js',

					// Ajax
					'src/js/Ajax/publicKey.js',
					'src/js/Ajax/queue.js',
					'src/js/Ajax/forms.js',
					'src/js/Ajax/articles.js',
					'src/js/Ajax/tags.js',
					'src/js/Ajax/comments.js',

					// UI
					'src/js/UI/dropDowns.js',
					'src/js/UI/notifications.js',
					'src/js/UI/tabs.js',
					'src/js/UI/loading.js',
					'src/js/UI/messages.js',

					// Pages
					'src/js/Pages/Account/login.js',
					'src/js/Pages/Account/forgotPassword.js',
					'src/js/Pages/Account/forgotUsername.js',
					'src/js/Pages/Account/resetPassword.js',
					'src/js/Pages/Account/page-register.js',
					'src/js/Pages/Admin/settings.js',
					'src/js/Pages/Admin/tools.js',

                ],
                dest: 'assets/js/scripts.js',
            },

        },

        /* UGLIFY JAVASCRIPT */
        uglify: {
            Javascript: {
                files: {
                    'assets/js/scripts.min.js': ['assets/js/scripts.js'],
                    'assets/js/libs/dropzone.min.js': ['assets/js/libs/dropzone.js'],
                    'assets/js/writer.min.js': ['assets/js/writer.js'],
                }
            }
        },

    });

    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks("grunt-jsbeautifier");
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-cssmin');

    grunt.registerTask('default', [ 'sass:compile', 'cssmin', 'jsbeautifier', 'concat', 'uglify']);

}