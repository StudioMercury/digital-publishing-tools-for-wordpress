<?php
/*
Template Name: AEM Mobile - Sample Article
*/
?>

<?php
	/* =================== */
	/* AEM Mobile Required */
	/* =================== */
	
	
	/* FILE PATH FOR THE BUNDLER */
	/* Sets the file path based on the plugin or theme folder */ 
	$isInPlugin = (strpos(__FILE__, DPSFA_DIR_NAME . "/publish-templates") !== FALSE); 
	if($isInPlugin){
		// Create file path based on plugin folder
    	$filePath = ($_SERVER['SERVER_NAME'] == 'localhost') ? plugins_url(). '/' . DPSFA_DIR_NAME . '/publish-templates/' : plugin_dir_url( (__DIR__) . "/publish-templates/");
	}else{
		// Create file path based on template folder
    	$filePath = get_template_directory_uri(). "/publish-templates/";
	}

?>


<?php	 

	/* =========================================== */
	/* == WP Filter to include additional files == */
	/* =========================================== */
	/*
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
		
		/* FILE PATH FOR BUNDLE (publish templates in the plugin folder vs theme folder) */ 
		$isInPlugin = (strpos(__FILE__, DPSFA_DIR_NAME . "/publish-templates") !== FALSE); 
		if($isInPlugin){
			// Create file path based on plugin folder
	    	$filePath = ($_SERVER['SERVER_NAME'] == 'localhost') ? plugins_url(). '/' . DPSFA_DIR_NAME . '/publish-templates/' : plugin_dir_url( (__FILE__) . "/publish-templates/");
		}else{
			// Create file path based on template folder
	    	$filePath = get_template_directory_uri(). "/publish-templates/";
		}
		
		return array(
			$filePath . 'HTMLResources/fonts/glyphicons-halflings-regular.eot',
			$filePath . 'HTMLResources/fonts/glyphicons-halflings-regular.svg',
			$filePath . 'HTMLResources/fonts/glyphicons-halflings-regular.ttf',
			$filePath . 'HTMLResources/fonts/glyphicons-halflings-regular.woff',
			$filePath . 'HTMLResources/fonts/glyphicons-halflings-regular.woff2',
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

    <link rel="stylesheet" href="<?php echo $filePath; ?>HTMLResources/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $filePath; ?>HTMLResources/css/template-article.css">
	
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



    <script src="<?php echo $filePath; ?>HTMLResources/js/jquery-2.1.4.min.js"></script>
    <script src="<?php echo $filePath; ?>HTMLResources/js/bootstrap.min.js"></script>
    <script src="<?php echo $filePath; ?>HTMLResources/js/main.js"></script>


</body>

</html>

<?php endwhile; endif; ?>