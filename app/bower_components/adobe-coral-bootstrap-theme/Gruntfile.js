module.exports = function(grunt) {
  require('jit-grunt')(grunt);

  grunt.initConfig({
    less: {
      compileCore: {
        options: {
          strictMath: true,
          compress: true,
          yuicompress: true,
          optimization: 2
        },
        src: 'less/bootstrap.less',
        dest: 'css/bootstrap.css'
      },
      compileWrapper: {
        options: {
          strictMath: true,
          //compress: true,
          //yuicompress: true,
          //optimization: 2
        },
        src: 'less/bootstrap-coral-ui.less',
        dest: 'css/bootstrap-coral-ui.css'
      },      
      compileTheme: {
        options: {
          strictMath: true,
          //compress: true,
          //yuicompress: true,
          //optimization: 2
        },
        src: 'less/theme.less',
        dest: 'css/theme.css'
      }            
    },
    watch: {
      styles: {
        files: ['less/**/*.less'], // which files to watch
        tasks: ['less'],
        options: {
          nospawn: true
        }
      }
    }
  });

  grunt.registerTask('default', ['less', 'watch']);
};