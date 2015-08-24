<?php
/*
Template Name: Adobe Publish - Sample Article
*/
?>

<?php
    // If bundling set the file path to be relative to the
    if( isset($_GET["bundle"]) ) {
        $filePath = '../HTMLResources/';
        $urlPath = 'navto://';
    } else {
        $filePath = get_bloginfo('template_directory') . '/publish-templates/HTMLResources/';
    }

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
    <meta name="description" content="<?php the_excerpt(); ?>">
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
