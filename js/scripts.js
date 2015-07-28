(function() {

  /*-----------------------------------------------------------------------------------*/
  /*  Functions for the navigation
  /*-----------------------------------------------------------------------------------*/

  var cbpAnimatedHeader = (function() {

    var docElem = document.documentElement,
      header = document.querySelector( '.navbar' ),
      didScroll = false,
      changeHeaderOn = $("header").height()-52;

    function init() {
      window.addEventListener( 'scroll', function( event ) {
        if( !didScroll ) {
          didScroll = true;
          setTimeout( scrollPage, 250 );
        }
      }, false );
    }

    function scrollPage() {
      var sy = scrollY();
      if ( sy >= changeHeaderOn ) {
        classie.add( header, 'navbar-fixed' );
      }
      else {
        classie.remove( header, 'navbar-fixed' );
      }
      didScroll = false;
    }

    function scrollY() {
      return window.pageYOffset || docElem.scrollTop;
    }

    init();

  })();

  /*-----------------------------------------------------------------------------------*/
  /*  Video Loading
  /*-----------------------------------------------------------------------------------*/
  
  /* Video.js loader */
  var fitPlayerYT = videojs('fitVideoYT', {}, function(){});
  var overlayPlayer = videojs('overlayVideo', {}, function(){});

  /*-----------------------------------------------------------------------------------*/
  /*  Smooth Scroll - Navigation + .scroll items
  /*-----------------------------------------------------------------------------------*/
  
  jQuery('.scroll').bind('click',function(event){
      event.preventDefault();

      var anchor = jQuery(this);
      
      jQuery('#navigation .nav li').removeClass('active');
      jQuery(this).addClass('active');

      if (jQuery(this).parent().parent().hasClass('dropdown-menu')) {
          jQuery(this).parent().parent().parent().addClass('active');
      }
      
      jQuery('html, body').stop().animate({
          scrollTop: jQuery(anchor.attr('href')).offset().top-52
      }, 1500,'easeInOutExpo');
  });

  /*-----------------------------------------------------------------------------------*/
  /*  Active Section - update section while scrolling
  /*-----------------------------------------------------------------------------------*/

  jQuery(window).bind('scroll', function () {

    var scrollTop = jQuery(window).scrollTop();

    if (scrollTop < $("#hero").height()-52) {
      $("#navigation .nav li:not('.dropdown').active").removeClass('active')
    } else {
      jQuery('section').each(function(){
        if (scrollTop >= jQuery(this).offset().top-52){
          var section = jQuery(this).attr('id');
          $("#navigation .nav li").each(function(){
            if(section == jQuery(this).find('a').attr('href').replace("#","") && jQuery(this).not('.active')){
              $("#navigation .nav li:not('.dropdown')").removeClass('active');
              jQuery(this).addClass('active');
            }
          });
        }
      });
    }

  });

  /*-----------------------------------------------------------------------------------*/
  /*  Initializing wow.js plugin to trigger animations from animate.css
  /*-----------------------------------------------------------------------------------*/

  wow = new WOW(
    {
      boxClass:     'animate',
      mobile:       false,
      offset:       150
    }
  )
  wow.init();

  /*-----------------------------------------------------------------------------------*/
  /*  Initializing sliders
  /*-----------------------------------------------------------------------------------*/

  var flexslider = $('.flexslider');

  $(window).load(function() {

    /* Flexslider for device display */
    if($(".flexslider").length != 0) {
      flexslider.flexslider({
        animation: "slide",
        directionNav: false,
        touch: true
      });
    }

    /* Flexslider for testimonials display */
    if($(".testimonials-slider").length != 0) {
      $('.testimonials-slider').flexslider({
        animation: "fade",
        directionNav: false,
        start: function(){
          $('.testimonials-slider .loading').fadeOut(function(){
            $('.testimonials-slider .slides').show(function(){
              $('.testimonials-section .flex-control-nav').show();
            });
          });
        }
      });
    }

    /* Flexslider for twitter display */
    if($(".twitter-slider").length != 0) {
      $('.twitter-slider').flexslider({
        animation: "fade",
        controlNav: false,
        directionNav: false,
        start: function(){
          $('.twitter-slider .spinner').fadeOut(function(){
            $('.twitter-slider .slides').show();
          });
        }
      });
    }
  });

  /*-----------------------------------------------------------------------------------*/
  /*  Different types of displaying video
  /*-----------------------------------------------------------------------------------*/

  var container = document.querySelector( '#page-wrapper' );
  var triggerBttn = document.querySelector( '#display-video' );

  transEndEventNames = {
    'WebkitTransition': 'webkitTransitionEnd',
    'MozTransition': 'transitionend',
    'OTransition': 'oTransitionEnd',
    'msTransition': 'MSTransitionEnd',
    'transition': 'transitionend'
  },
  transEndEventName = transEndEventNames[ Modernizr.prefixed( 'transition' ) ],
  support = { transitions : Modernizr.csstransitions };

  function toggleVideo() {

    if ( $('body').hasClass('app-overlay') ) {
      videoContainer = document.querySelector( '.video-overlay' );
      closeBttn = document.querySelector( '#closeOverlay' );
    } else {
      videoContainer = document.querySelector( '#hero' );
      closeBttn = document.querySelector( '#closeFit' );
    }
    closeBttn.addEventListener( 'click', toggleVideo );

    if( classie.has( videoContainer, 'open' ) ) {
      classie.remove( videoContainer, 'open' );
      classie.remove( container, 'video-overlay-open' );
      classie.add( videoContainer, 'closing' );

      fitPlayerYT.pause();  
      overlayPlayer.pause();

      var onEndTransitionFn = function( ev ) {
        if( support.transitions ) {
            this.removeEventListener( transEndEventName, onEndTransitionFn );
        }
        classie.remove( videoContainer, 'closing' ); 

        $("#navigation").removeClass('fadeOutUp').addClass('fadeInDown');
        $("#hero-text").removeClass('fadeOutDown').addClass('fadeInUp');
        $("#hero .overlay").removeClass('fadeOut').addClass('fadeIn');
        $(".video-fit-header").removeClass('fadeIn animated');
      };

      if( support.transitions) {
        if ( !($('body').hasClass('app-overlay')) && $(document).width() < 992) {
          onEndTransitionFn(); 
        } else {
          videoContainer.addEventListener( transEndEventName, onEndTransitionFn );
        }
      } else {
        onEndTransitionFn(); 
      }
    }
    else if( !classie.has( videoContainer, 'closing' ) ) {
      classie.add( videoContainer, 'open' );
      if ( $ ('body').hasClass('app-overlay')) {
        classie.add( container, 'video-overlay-open' );
      } else {
        $("#navigation").addClass('fadeOutUp animated');
        $("#hero-text").addClass('fadeOutDown animated');
        $("#hero .overlay").addClass('fadeOut animated');    
        $(".video-fit-header").addClass('fadeIn animated');
      }
    }
  }

  triggerBttn.addEventListener( 'click', toggleVideo );

  /*-----------------------------------------------------------------------------------*/
  /*  Device Selection
  /*-----------------------------------------------------------------------------------*/

  if($("#device-select").length != 0) {
    $("#device-select li").on("click", function() {
      var device = $(this).attr("class");

      $("#device-select li i").removeClass('active');
      $(this).find("i").addClass('active');

      $(".device, .detailed-view").removeClass("apple android windows").addClass(device);
      flexslider.resize();
    });
  }

  /*-----------------------------------------------------------------------------------*/
  /*  Social Feeds
  /*-----------------------------------------------------------------------------------*/

  /* Initialize Instagram feed */
  if($("#instagram").length != 0) {

    var count=0;

    if ( $(document).width() < 768 ) {
      count = 2;
    } else if ( $(document).width() < 992 ) {
      count = 3;
    } else if ( $(document).width() < 1200 ) {
      count = 4;
    } else {
      count = 6;
    }

    $('#instagram').pongstgrm({
      // User Authentication
      accessId: '262941207',
      accessToken: '262941207.388182b.f8be72f70975466985caba8cc4ec29df',
      // Display Options
      likes: false,
      comments: false,
      timestamp: false,
      full: true,
      show: 'dribbble',
      count: count,
      // HTML Options
      button:           "appeal-button",
      buttontext:       "Load more",
      column:           "col-xs-6 col-sm-4 col-md-3 col-lg-2",
      likeicon:         "fa fa-heart",
      muteicon:         "fa fa-volume-off",
      videoicon:        "fa fa-play",
      commenticon:      "fa fa-comments"
    });
  }

  /* Initialize Twitter feed */
  if($(".twitter-slider").length != 0) {
    $.getJSON( "./inc/Twitter.php", function(tweets) {
        for (var i=0; i<tweets.length; i++) {
          var tweet_html = "<li><div class='row'>";
          tweet_html += "<blockquote cite='"+ tweets[i].url +"'>";  
          tweet_html += "<header class='col-sm-2 tweet-date'><span>" + relative_time(tweets[i].created_at) + "</span></header>";
          tweet_html += "<p class='col-sm-9 col-sm-offset-1 tweet'>" + tweets[i].text + "</p>";    
          tweet_html += "</blockquote></div></li>";
          tweet_html = tweet_links(tweet_html);
          $('.twitter-slider > ul').append(tweet_html);
        }
    });
  }

  /* Return proper Twitter links */
  function tweet_links(tweet) {
     tweet = tweet.replace(/((https?|s?ftp|ssh)\:\/\/[^"\s\<\>]*[^.,;'">\:\s\<\>\)\]\!])/g, function(url) {
        return '<a href="'+url+'"  target="_blank">'+url+'</a>';
    });
         
    tweet = tweet.replace(/\B@([_a-z0-9]+)/ig, function(reply) {
        return '<a href="http://twitter.com/'+reply.substring(1)+'" style="font-weight:lighter;" target="_blank">'+reply.charAt(0)+reply.substring(1)+'</a>';
    });

    tweet = tweet.replace(/\B#([_a-z0-9]+)/ig, function(reply) {
        return '<a href="https://twitter.com/search?q='+reply.substring(1)+'" style="font-weight:lighter;" target="_blank">'+reply.charAt(0)+reply.substring(1)+'</a>';
    });
    return tweet;
  }

  /* Calculate relative time for Twitter posts */
  function relative_time(time) {
    var values = time.split(" ");
    time = values[1] + " " + values[2] + ", " + values[5] + " " + values[3];
    var parsed_date = Date.parse(time);
    var relative_to = (arguments.length > 1) ? arguments[1] : new Date();
    var delta = parseInt((relative_to.getTime() - parsed_date) / 1000);
    var date = time.substr(0,6);
    delta = delta + (relative_to.getTimezoneOffset() * 60);

    if (delta < 60) {
      return '1m ago';
    } else if(delta < 120) {
      return '1m ago';
    } else if(delta < (60*60)) {
      return (parseInt(delta / 60)).toString() + 'm ago';
    } else if(delta < (120*60)) {
      return '1h ago';
    } else if(delta < (24*60*60)) {
      return (parseInt(delta / 3600)).toString() + 'h ago';
    } else if(delta < (48*60*60)) {
      return date;
    } else {
      return date;
    }
  }

  /*-----------------------------------------------------------------------------------*/
  /*  Initializing Fluidbox for clean lightbox effect to display images
  /*-----------------------------------------------------------------------------------*/

  if($(".fluidbox-section").length != 0) {

      if ( !Modernizr.touch ) {
        $('.fluidbox-section a').fluidbox(
          {
            viewportFill: 0.75,
            debounceResize: true,
            stackIndex: 9999
          }
        );
      } else {
          $('.fluidbox-section a').on('click', function(){
              event.preventDefault();
          }); 
      } 
  }

  /*-----------------------------------------------------------------------------------*/
  /*  Initializing MailChimp Ajax Form
  /*-----------------------------------------------------------------------------------*/

  $('#mc-form').ajaxChimp({
    url: 'http://joaopedro.us9.list-manage1.com/subscribe/post?u=5a5d1c21a31ad1d9c02713146&id=f28ed2fae8'
  });

  /*-----------------------------------------------------------------------------------*/
  /*  Contacts
  /*-----------------------------------------------------------------------------------*/ 
    
  /* Validation Form with AJAX while typing for inputs */
  jQuery('#contact-form input').bind('input propertychange', function() {
    jQuery(this).parent().find('span').remove();
      if( jQuery(this).attr('id') == 'email' ){
        var checkEmail = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/; 
        var email = jQuery('#email').val();
        if (email == "" || email == " ") {
          jQuery('#email').after("<span class='error'>Please enter your email adress.</span>");
          jQuery('#email').parent().find('.error').fadeIn('slow');
          error = true;
        } else if (!checkEmail.test(email)) { 
          jQuery('#email').after("<span class='error'>Please enter a valid email adress.</span>");
          jQuery('#email').parent().find('.error').fadeIn('slow');
          error = true;
        }   
      } else {
        if(jQuery(this).val() == "" || jQuery(this).val() == " "){
          jQuery(this).after("<span class='error'></span>");
          jQuery(this).parent().find('.error').fadeIn('slow');         
        } else {
          jQuery(this).after("<span class='valid'></span>");
          jQuery(this).parent().find('.valid').fadeIn('slow');  
        }
      }
  });
  
  /* Validation Form with AJAX while typing for textarea */
  jQuery('#contact-form textarea').bind('input propertychange', function() {
    jQuery(this).parent().find('span').remove(); 
    if(jQuery(this).val() == "" || jQuery(this).val() == " "){
      jQuery(this).after("<span class='error'></span>");
      jQuery(this).parent().find('.error').fadeIn('slow');         
    } else {
      jQuery(this).after("<span class='valid'></span>");
      jQuery(this).parent().find('.valid').fadeIn('slow');  
    }
  }); 
  
  
  /* Validation Form with AJAX on Submit */
  jQuery('#submit').click(function(){
    jQuery('#contact-form span').remove();
    jQuery('#thanks').hide();
    jQuery('#error').hide();
    jQuery('#timedout').hide();
    jQuery('#state').hide();
    
    var error = false; 
    var name = jQuery('#name').val(); 
    if(name == "" || name == " ") {
      jQuery('#name').after("<span class='error'>Please enter your name.</span>");
      jQuery('#name').parent().find('.error').fadeIn('slow');
      error = true; 
    }
    
    var checkEmail = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/; 
    var email = jQuery('#email').val();
    if (email == "" || email == " ") {
      jQuery('#email').after("<span class='error'>Please enter your email adress.</span>");
      jQuery('#email').parent().find('.error').fadeIn('slow');
      error = true; 
    } else if (!checkEmail.test(email)) { 
      jQuery('#email').after("<span class='error'>Please enter a valid email adress.</span>");
      jQuery('#email').parent().find('.error').fadeIn('slow');
      error = true; 
    }
    
    var message = jQuery('#message').val(); 
    if(message == "" || message == " ") {
      jQuery('#message').after("<span class='error'>Please enter a message.</span>");
      jQuery('#message').parent().find('.error').fadeIn('slow');
      error = true; 
    }

    if(error == true) {
      jQuery('#error').fadeIn('slow');
      jQuery('#error').html('Please fill in all data correctly.');
      setTimeout(function() {
          jQuery('#error').fadeOut('slow');
      }, 3000);
      return false;
    }
    
    var data_string = jQuery('#contact-form').serialize();
    
    jQuery.ajax({
      type: "POST",
      url: "inc/sendMail.php",
      data: {name:name,email:email,message:message}, 
      timeout: 6000,
      error: function(request,error) {
        if (error == "timeout") {
          jQuery('#timedout').fadeIn('slow');
          setTimeout(function() {
              jQuery('#timedout').fadeOut('slow');
          }, 3000);
        }
        else {
          jQuery('#error').fadeIn('slow');
          jQuery("#error").html('The following error occured: ' + error + '');
          setTimeout(function() {
              jQuery('#error').fadeOut('slow');
          }, 3000);
        }
      },
      success: function() {
        jQuery('#contact-form span').remove();
        jQuery('#thanks').fadeIn('slow');
        jQuery('#contact-form input').val('');
        jQuery('#contact-form textarea').val('');
        setTimeout(function() {
            jQuery('#thanks').fadeOut('slow');
        }, 3000);
      }
    });
    
    return false;
  });

})();
