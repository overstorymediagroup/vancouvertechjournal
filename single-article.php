<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header(); ?>
	<div id="primary" <?php astra_primary_class(); ?>>
        <div class="title-container">
            <div class="post-content-top">
                <time class="date-style" datetime="<?php echo get_the_date('c'); ?>" itemprop="datePublished"><?php echo get_the_date(); ?></time>
                <h1 class="post-title"><?php echo get_the_title(); ?></h1>
                <h4 class="post-author"><?php echo get_the_author( $post->ID ); ?></h4>
            </div>
			<?php
				$img = "";
				if(get_the_post_thumbnail_url()){
					$img = get_the_post_thumbnail_url();
				}else{
					$img = "http://staging-vancouvertechjournal.kinsta.cloud/wp-content/uploads/2021/09/single-post.png";
				}
			?>
            <div class="post-featured-img" style="background-image: url('<?php echo $img; ?>');"></div>
            <p class="featured-img-caption"><?php echo get_field('featured_image_caption'); ?></p>

            <div class="post-con">
                <div class="buttons-wrap">
    		        <a class="social-icon" target="_blank" href="http://twitter.com/intent/tweet?text=Currently reading <?php the_title(); ?>&url=<?php the_permalink(); ?>"><i class="fab fa-twitter"></i>Tweet this</a>
    		        <a class="social-icon" target="_blank" href="https://www.facebook.com/sharer?u=<?php the_permalink();?>&<?php the_title(); ?>"><i class="fab fa-facebook-f"></i>Share this</a>
    		        <a class="social-icon" id="copy_link" href="<?= get_the_permalink($post->ID); ?>"><i class="fas fa-link"></i>Copy this</a>
    		    </div>
                <p class="post-content"><?php $content = apply_filters( 'the_content', get_the_content() ); 
                            echo $content;
                ?></p>
                <div class="author-info-container">
                    <?php
                        $get_author_id = get_the_author_meta('ID');
                        $get_author_gravatar = get_field( 'image', 'user_'.$get_author_id );
                        $bio_excerpt = get_field('biographical_info_excerpt', 'user_'.$get_author_id);
                        $author_title = get_field('title', 'user_'.$get_author_id);
                        $author = get_the_author( $post->ID );
                        $twitter = get_the_author_meta( 'twitter', $get_author_id );
                        $email = get_the_author_meta( 'email', $get_author_id );
                    ?>
                    <div class="wrap">
                        <div class="author-info">
                            <div class="author-img-wrap">
                                 <div class="author-img" style="background: url('<?php echo esc_url( $get_author_gravatar['url']);?>')"></div>
                            </div>
                            <div class="author-bio">
                                <h4 class="the-author"><a href="<?=esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) );?>"><?= $author ?></a></h4>
                                <p class="author-title"><?=$author_title;?></p>
                                <div class="social-links">
                                    <a class="link" href="mailto:<?=$email;?>"><i class="fas fa-envelope"></i></a>
                                    <a class="link" href="https://twitter.com/<?=$twitter;?>" target="_blank"><i class="fab fa-twitter"></i></a>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>

            </div>
        </div>
        <div class="related-posts-container">
                    <?php echo do_shortcode('[related-posts]'); ?>
        </div>
    </div><!-- #primary -->
    <?php if( is_user_logged_in() ): ?>
        <?php echo '<div class="boted">'.do_shortcode("[show-form color='light']").'</div>'; ?>
    <?php else: ?>
        <?php
            echo do_shortcode("[membership-benefit]");
			echo do_shortcode("[testimonials]");
            echo do_shortcode("[show-subscription]");    
        ?>
    <?php endif; ?>

<?php get_footer(); ?>
