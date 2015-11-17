<?php
/*
Template Name: Adobe Publish - Sample Article
*/
?>

<?php
    /* == File path for publish-templates located inside the plugin folder: == */
    $filePath = plugins_url( DPSFA_DIR_NAME . '/publish-templates/HTMLResources/' );	    
    // If you move the publish-templates folder into your template directory then uncomment the below line and comment out the line above.
	// $filePath = get_bloginfo('template_directory') . '/publish-templates/HTMLResources/';
	    
    if( isset($_GET["bundlr"]) ) {
	    // This could be used for changing links to navto:// links
        $urlPath = 'navto://';
    }
    
	/*
	    == WP Filter to include additional files ==
	    You can add additional files to the article using the filter below. You can add file two ways:
	    	    
	    1. Automatic: Specify full url to file (array of images)
	    Specifying the full url will create the necessary folder scructure in the article and download the external file
	    Folder struture for external resources: ARTICLE > sanitized hostname > path > file
	    Example: array('http://www.domain.com/wp-content/themes/theme/file.jpg') will put that file in the article as: domaincom/wp-content/themes/theme/file.jpg
	    
	    2. Manual: Specify the full paths array( "file path relative in article" => "filepath relative to server (or url)" )
	    You can have control over where the file is placed in the article and where to pull it from the server
	    Example: array( array('slideshow/image/file.jpg' => 'www/wp-content/themes/theme/file.jpg') ) will put that file in the article as: domaincom/wp-content/themes/theme/file.jpg
	*/
	
	add_filter('dpsfa_bundle_article', function($entity){
		// $entity will contain all of the info of the article (metadata / template / etc.)
		$filePath = plugins_url( DPSFA_DIR_NAME . '/publish-templates/HTMLResources/' ); // If inside plugin folder	    
		// $filePath = get_bloginfo('template_directory') . '/publish-templates/HTMLResources/'; // If inside theme folder
		
		return array(
			$filePath . 'fonts/glyphicons-halflings-regular.eot',
			$filePath . 'fonts/glyphicons-halflings-regular.svg',
			$filePath . 'fonts/glyphicons-halflings-regular.ttf',
			$filePath . 'fonts/glyphicons-halflings-regular.woff',
			$filePath . 'fonts/glyphicons-halflings-regular.woff2',
		);
	});
	
?>

<?php if ( have_posts() ) : while (have_posts()) : the_post(); ?>

<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />

    <title><?php the_title(); ?></title>

    <link rel="stylesheet" href="<?php echo $filePath; ?>css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $filePath; ?>css/template-article.css">
	
</head>

<body class="article">

    <div class="container">

    	<div class="row">

			<div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">

	            <h1><?php the_title(); ?></h1>

	            <article>
		            <?php the_content(); ?>
	            </article>

			</div>

		</div>

    </div>



    <script src="<?php echo $filePath; ?>js/jquery-2.1.4.min.js"></script>
    <script src="<?php echo $filePath; ?>js/bootstrap.min.js"></script>
    <script src="<?php echo $filePath; ?>js/main.js"></script>


</body>

</html>

<?php endwhile; endif; ?>
