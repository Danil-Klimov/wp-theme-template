<?php get_header(); ?>

<section class="">
	<?php if ( have_posts() ) : ?>
		<h1 class="">
			<?php printf( 'Результат поиска для: %s', '<span>' . get_search_query() . '</span>' ); ?>
		</h1>

		<?php while ( have_posts() ) : the_post(); ?>
			<?php the_title(); ?>
		<?php endwhile; ?>

		<?php the_posts_navigation(); ?>
	<?php else : ?>
		<p>Поиск не дал результатов</p>
	<?php endif; ?>
</section>

<?php get_footer(); ?>
