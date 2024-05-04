<?php get_header(); ?>

<?php if ( have_posts() ) : ?>

	<?php
	the_archive_title( '<h1 class="page-title">', '</h1>' );
	the_archive_description( '<div class="archive-description">', '</div>' );
	?>

	<?php while ( have_posts() ) : the_post(); ?>

		<?php the_title(); ?>

	<?php endwhile; ?>

	<?php the_posts_navigation(); ?>

<?php else : ?>

	<p>no posts</p>

<?php endif; ?>

<?php get_footer(); ?>
