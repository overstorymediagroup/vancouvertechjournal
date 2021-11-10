<?php
/**
 * The template for displaying archive pages.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Astra
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header(); ?>



<?php if ( astra_page_layout() == 'left-sidebar' ) : ?>

	<?php get_sidebar(); ?>

<?php endif ?>

	<div id="primary" <?php astra_primary_class(); ?>>

		<?php astra_primary_content_top(); ?>

        <div class="featured-wrap">
            <?php
                $term = get_queried_object();
                echo do_shortcode("[featured-article category='".$term->slug."']");
            ?>
        </div>

		<!--<section class="ast-archive-description">
            <h1 class="page-title ast-archive-title"><span><?//=$term->name;?></span></h1>
        </section>-->

		<?php
            $post_id = do_shortcode("[getpost-id category='".$term->slug."']");
			$count = 0;
            $loop = new WP_Query( array(
                'post_type' => 'article',
                'posts_per_page' => '8',
                'tax_query' => array(
                    array(
                        'taxonomy'  => 'article_categories',
                        'field'     => 'slug',
                        'terms'     => $term->slug
                    )
                ),
                'post__not_in' => array($post_id),
                'paged' => get_query_var('paged') ? get_query_var('paged') : 1
            )
            );

            $output .= '<div class="articles">';
                $output .= '<div class="list">';

                    while ( $loop->have_posts() ) : $loop->the_post(); 
                        
						$image = get_the_post_thumbnail_url(get_the_ID(),'full'); 
						$bg_image = "";
						if(!$image){
							$bg_image = "http://staging-vancouvertechjournal.kinsta.cloud/wp-content/uploads/2021/09/single-post.png";
						}else{
							$bg_image = $image;
						}
                        $permalink = get_permalink( get_the_ID() );
                        $title = get_the_title( get_the_ID() );
                        //$date = get_the_date('F d Y'); 
                        $date = get_the_date('F j, Y');  
                        $excerpt = get_the_excerpt();  
                        $counter = $count++;
                        $get_author = get_the_author( get_the_ID() );
                        
                        $terms = get_the_terms( get_the_ID() , 'article_categories' );
                        foreach ( $terms as $term ) {
                            if ($term->slug != 'featured-article') {
                                $category_link = get_category_link( $term->term_id );
                                $class = '<a href="'.esc_url( $category_link ).'"><span class="category__badge">'.$term->name.'</span></a>';
                            }
                        }

                        $output .= '<div class="article">';
                            $output .= '<div class="wrap">';
								$output .='<div class="article-card-image thumb-img" data-url="'.$permalink.'" style="background-image: url('.$bg_image.');"></div>';
                                //$output .= '<p class="category"><span>'.$class.'</span></p>';
                                //$output .= '</div>';
                                $output .= '<div class="infos">';
                                    $output .= '<div class="infos-wrap">';
                                        $output .= '<p class="category"><span>'.$class.'</span></p>';
                                        $output .= '<h2 class="title"><a href="'.$permalink.'">'.$title.'</a></h2>';
                                        $output .= '<p class="excerpt">'.$excerpt.'</p>';
                                    $output .= '</div>';
                                $output .= '</div>';
                            $output .= '</div>';
                        $output .= '</div>';
		
					if($count == 4){
						$output .=do_shortcode("[show-form color='dark']");
					}
                    
                    endwhile; 

                    $output .=  paginate_links( array(
                        'base' => get_pagenum_link(1) . '%_%',
                        'format' => 'page/%#%', //for replacing the page number
                        'type' => 'list', //format of the returned value
                        'total' => $loop->max_num_pages,
                        'current' => max( 1, get_query_var('paged') ),
                        'prev_text'    => __('Previous Page'),
                        'next_text'    => __('Next Page'),
                    ) );

                    wp_reset_postdata();

                $output .= '</div>';
            $output .= '</div>';
            
            echo $output;
        ?>

		<?php astra_primary_content_bottom(); ?>

	</div><!-- #primary -->

<?php if ( astra_page_layout() == 'right-sidebar' ) : ?>

	<?php get_sidebar(); ?>

<?php endif ?>

<?php get_footer(); ?>
