<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function tme_load_font_awesome() {
    // You can find the current URL for the latest version here: https://fontawesome.com/start
    wp_enqueue_style( 'font-awesome-free', '//use.fontawesome.com/releases/v5.6.3/css/all.css' );
}
add_action( 'wp_enqueue_scripts', 'tme_load_font_awesome' );
function child_enqueue_styles() {
	
	
    /* Styles */
    wp_enqueue_style('fontawesome', 'https://pro.fontawesome.com/releases/v5.10.0/css/all.css', null, 'all' );
	wp_enqueue_style( 'astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

    wp_enqueue_style( 'dev', get_stylesheet_directory_uri() . '/assets/css/dev.css', [] );

    $check_option = get_option( 'save_address_option' );
    $check_user_id = get_option( 'save_address_userid' );
	
	$user = get_current_user_id();
	
	
    if($check_option == "saved_once" && $check_user_id == $user) {
        return;
    }else{
        wp_enqueue_style( 'astra-child-my-account', get_stylesheet_directory_uri() . '/my-account.css', '', NULL, 'all' );
        wp_enqueue_script( 'my-account-js', get_stylesheet_directory_uri() . '/js/my-account.js', array(), NULL, true );
    }

    /* Scripts */
    wp_enqueue_script( 'custom-js', get_stylesheet_directory_uri() . '/js/custom.js', array(), '1.0.0', true );
    wp_enqueue_script('jquery');
}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

if( function_exists('acf_add_options_page') ) {
	acf_add_options_page(); 
}

if( function_exists('acf_set_options_page_title') ) {
    acf_set_options_page_title( __('Featured Categories') );
}

function redirect_my_account_for_first_time() {
	global $post;
	
	$check_option = get_option( 'save_address_option' );
    $check_user_id = get_option( 'save_address_userid' );
	
	$user = get_current_user_id();
	
	if( $post->ID == 621 ) {
		if($check_option == "saved_once" && $check_user_id == $user) {
			return;
		}else {
			wp_redirect( '/my-account/edit-address/billing/' );
		}
	}
	
}
add_action( 'init', 'redirect_my_account_for_first_time' );

// Change breakpoint tablets
add_filter( 'astra_tablet_breakpoint', function() {
    return 1024;
    });

// Change breakpoint mobile
add_filter( 'astra_mobile_breakpoint', function() {
    return 800;
    });

/*Write here your own functions */
function cc_mime_types($mimes) {
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
}
add_filter('upload_mimes', 'cc_mime_types');


add_action( 'init', 'register_custom_post_types' );

function register_custom_post_types() {
    
    // Articless Post Type
    $article_args = array(
        'labels' => array(
            'name' => 'Articles',
            'menu_name' => 'Articles',
            'singular_name' => 'Article'
        ),

        'public' => true,
        'has_archive' => false,
        'rewrite'  => array( 'slug' => 'article' ),
        'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'revisions', 'excerpt' , 'page-attributes' , 'comments')
    );

    register_post_type( 'article', $article_args );   
    
}

add_action( 'init', 'register_custom_taxonomies', 0 );

function register_custom_taxonomies() {
    
    // Add new taxonomy, make it hierarchical (like categories)
    $case_labels = array(
        'name'              => _x( 'Categories', 'taxonomy general name', 'textdomain' ),
        'singular_name'     => _x( 'Category', 'taxonomy singular name', 'textdomain' ),
        'search_items'      => __( 'Search Category', 'textdomain' ),
        'all_items'         => __( 'All Category', 'textdomain' ),
        'parent_item'       => __( 'Parent Category', 'textdomain' ),
        'parent_item_colon' => __( 'Parent Category:', 'textdomain' ),
        'edit_item'         => __( 'Edit Category', 'textdomain' ),
        'update_item'       => __( 'Update Category', 'textdomain' ),
        'add_new_item'      => __( 'Add New Category', 'textdomain' ),
        'new_item_name'     => __( 'New Category Name', 'textdomain' ),
        'menu_name'         => __( 'Categories', 'textdomain' ),
    );
 
     $article_categories = array(
        'hierarchical'      => true,
        'labels'            => $article_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'articles-categories' ),
    );
 
    register_taxonomy( 'article_categories', array( 'article' ), $article_categories );
    
    
    register_taxonomy( 
    'custom-tag', //taxonomy 
    'article', //post-type
        array( 
            'hierarchical'  => false, 
            'label'         => __( 'Tags','taxonomy general name'), 
            'singular_name' => __( 'Tag', 'taxonomy general name' ), 
            'rewrite'       => true, 
            'query_var'     => true 
        )    
    );
    
}

/**
 * Shortcode
 */
function all_articles($atts, $content) {
    
    $args = shortcode_atts( array(
        'post' => '10',
        'filter' => 0,
		'pagination' => 0
    ), $atts );
    
    $output = "";
    
    if($args["filter"] == 1){
        $filtered_category = $_GET["filter_c"];
        $filtered_author = (isset($_GET["filter_a"])) ? $_GET["filter_a"] : "";
        
        $display_admins = false;
        $order_by = 'nicename'; // 'nicename', 'email', 'url', 'registered', 'display_name', or 'post_count'
        $order = 'DESC';
        $role = 'author, editor'; // 'subscriber', 'contributor', 'editor', 'author' - leave blank for 'all'
        $avatar_size = 161;
        $hide_empty = false; // hides authors with zero posts
        $content = '';
        
        if(!empty($display_admins)) {
            //$blogusers = get_users('orderby='.$order_by.'&role='.$role);
            $blogusers = get_users( [ 'role__in' => [ 'editor', 'author' ], 'orderby' => $order ] );
        } else {
            $admins = get_users('role=administrator');
            $exclude = array();
            
            foreach($admins as $ad) {
              $exclude[] = $ad->ID;
            }
            $exclude = implode(',', $exclude);
            //$blogusers = get_users('exclude='.$exclude.'&orderby='.$order_by.'&order='.$order.'&role='.$role.'&meta_query[key]=order');
            $blogusers = get_users( [ 'role__in' => [ 'editor', 'author' ], 'orderby' => $order ] );
        }
        
        $authors = array();
        foreach ($blogusers as $bloguser) {
        $user = get_userdata($bloguser->ID);
        
        if(!empty($hide_empty)) {
          $numposts = count_user_posts($user->ID);
          if($numposts < 1) continue;
          }
          $authors[] = (array) $user;
        }
        
        $categories = get_categories( array(
            'orderby' => 'name',
            'taxonomy' => 'article_categories',
            'order'   => 'ASC'
        ) );
        
        $output .= '<div class="articles-filter">';
            $output .= '<div class="filter-by">Filter by: </div>';
            $output .= '<select id="filter_category">';
                $output .= '<option data-url="' . get_the_permalink() . '">Category</option>';
                foreach( $categories as $category ) {
                    $selected = ($filtered_category == $category->slug) ? "selected" : "";
                    $output .= '<option '. $selected .' data-url="' . get_the_permalink() . '?filter_c='. $category->slug .'">' . $category->name . '</option>';   
                }
            $output .= '</select>';
            $output .= '<select id="filter_authors">';
                $output .= '<option data-url="' . get_the_permalink() . '">Authors</option>';
                foreach($authors as $author) {
                  $first_name = get_the_author_meta( 'first_name', $author['ID'] );
                  $last_name = get_the_author_meta( 'last_name', $author['ID'] );
                  //$selected_author = ($filtered_category == $category->slug) ? "selected" : "";
                  $output .= '<option '. $selected .' data-url="' . get_the_permalink() . '?filter_a='. $first_name .' '. $last_name . '">'. ucfirst($first_name) .' '. ucfirst($last_name) . '</option>';
                }
            $output .= '</select>';
        $output .= '</div>';
    }
  
    $output .= '<div class="articles">';
      $output .= '<div class="list">';
      if(isset($filtered_category) && $filtered_category != 'all'){
          $loop = new WP_Query( array(
              'post_type' => 'article',
              'posts_per_page' => '-1',
              'author_name' => $filtered_author,
              'tax_query' => array(
                    array(
                        'taxonomy' => 'article_categories',
                        'field'    => 'slug',
                        'terms' => $filtered_category
                    )
                )
            )
          );
      }else{
          $loop = new WP_Query( array(
              'post_type' => 'article',
              'posts_per_page' => $args["post"],
              'paged' => get_query_var('paged') ? get_query_var('paged') : 1
            )
          );
      }
          
          $count = 1;
          while ( $loop->have_posts() ) : $loop->the_post(); 
				$img = "";
				if(get_the_post_thumbnail_url(get_the_ID(),'full')){
					$img = get_the_post_thumbnail_url(get_the_ID(),'full'); 
				}else{
					$img = "http://staging-vancouvertechjournal.kinsta.cloud/wp-content/uploads/2021/09/single-post.png";
				}
              //$image = get_the_post_thumbnail_url(get_the_ID(),'full'); 
              $permalink = get_permalink( get_the_ID() );
              $title = get_the_title( get_the_ID() );
              //$date = get_the_date('F d Y'); 
			  $date = get_the_date('F j, Y');  
			  $excerpt = get_the_excerpt(get_the_ID());
              $short_desc = get_field('short_description');
              $get_author = get_the_author( get_the_ID() );
              $class = '';
              $terms = get_the_terms( get_the_ID() , 'article_categories' );
              $post_category = "";
              foreach ( $terms as $term ) {
                  if ($term->slug != 'featured-article' && $term->slug != '') {
					  $category_link = get_category_link( $term->term_id );
                      $class .= '<a href="'.esc_url( $category_link ).'"><span class="category__badge">'.$term->name.'</span></a>';
                  }
			  }
              $output .= '<div class="article article-'. get_the_ID() .'">';
                  $output .= '<div class="wrap">';
                      $output .= '<div class="article-card-image thumb-img" data-url="'.$permalink.'" style="background-image: url('.$img.');"></div>';
				      $output .= '<div class="info-wrap">';
					    $output .= '<p class="category"><span>'. $class .'</span></p>';
					    $output .= '<a href="'.$permalink.'"><h2 class="title">'.$title.'</h2></a>';
					    //$output .= '<p class="date">'.$date.'</p>';
					    //$output .= '<div class="excerpt">'.$short_desc.'</div>';
                        $output .= '<div class="excerpt">'.$excerpt.'</div>';
                        
                        foreach ( $terms as $term ) {
							if( ! is_user_logged_in() ) {
								if ($term->slug != 'featured-article' && $term->slug == 'members-only') {
									$output .= '<div class="mem_button"><a href="/membership"><span class="members__badge">Unlock this article</span></a></div>';
								}
							}
                        }
				      $output .= '</div>';
                   $output .= '</div>';
               $output .= '</div>';	
				if(is_page( 15 ) || is_category() || is_tax() ) {
					if($count == 4){
						$output .=do_shortcode("[show-form]");
					}
				}		  
				$count++;			
      endwhile; 
		
		if ($args["pagination"] == 1){
			if(!isset($filtered_category)){
				$output .=  paginate_links( array(
					'base' => get_pagenum_link(1) . '%_%',
					'format' => 'page/%#%', //for replacing the page number
					'type' => 'list', //format of the returned value
					'total' => $loop->max_num_pages,
					'current' => max( 1, get_query_var('paged') ),
					'prev_text'    => __('Previous Page'),
					'next_text'    => __('Next Page'),
				) );
			}
		}
      wp_reset_postdata();
      $output .= '</div>';
  $output .= '</div>';
  
  return $output;
}

add_shortcode( 'all-articles' , 'all_articles' );


function showPost_id($atts, $content){
    $args_atts = shortcode_atts( array(
        'category' => '',
        'author' => ''
    ), $atts );

    if(!empty($args_atts["category"])){
        $args = array(
            'post_type' => 'article',
            'post_per_page' => 1,
            'orderby' => 'post_date',
            'order' => 'DESC',
            'tax_query' => array(
                array (
                    'taxonomy' => 'article_categories',
                    'field' => 'slug',
                    'terms' => $args_atts['category'],
                )
            ),
        );
    }elseif(!empty($args_atts["author"])){
        $args = array(
            'post_type' => 'article',
            'post_per_page' => 1,
            'orderby' => 'post_date',
            'author' => $args_atts["author"],
            'order' => 'DESC',
            'tax_query' => array(
                array (
                    'taxonomy' => 'article_categories',
                    'field' => 'slug',
                    'terms' => 'featured-article',
                )
            ),
        );
    }

    $query = new WP_Query( $args );
        $count = 1;
        while ($query -> have_posts()) : $query -> the_post();
        
        if($count == 1){
            $id = get_the_ID();
        }
        $count++;
        
        endwhile;
        wp_reset_postdata();
    return $id;
}

add_shortcode( 'getpost-id' , 'showPost_id' );

function featured_article($atts, $content) {

    $args_atts = shortcode_atts( array(
        'category' => '',
        'author' => ''
    ), $atts );

    $content = '';

    if(!empty($args_atts["category"])){
        $args = array(
            'post_type' => 'article',
            'post_per_page' => 1,
            'orderby' => 'post_date',
            'order' => 'DESC',
            'tax_query' => array(
                array (
                    'taxonomy' => 'article_categories',
                    'field' => 'slug',
                    'terms' => $args_atts['category'],
                )
            ),
        );
    }elseif(!empty($args_atts["author"])){
        $args = array(
            'post_type' => 'article',
            'post_per_page' => 1,
            'orderby' => 'post_date',
            'author' => $args_atts["author"],
            'order' => 'DESC',
            'tax_query' => array(
                array (
                    'taxonomy' => 'article_categories',
                    'field' => 'slug',
                    'terms' => 'featured-article',
                )
            ),
        );
    }else{
        $args = array(
            'post_type' => 'article',
            'post_per_page' => 1,
            'orderby' => 'post_date',
            'order' => 'DESC',
            'tax_query' => array(
                array (
                    'taxonomy' => 'article_categories',
                    'field' => 'slug',
                    'terms' => 'featured-article',
                )
            ),
        );
    }

    $query = new WP_Query( $args );
        $count = 1;
        while ($query->have_posts()) : $query->the_post();
        
        if($count == 1){
			
			$image = get_the_post_thumbnail_url(get_the_ID(),'full'); 
			$bg_image = "";
			if(!$image){
				$bg_image = "http://staging-vancouvertechjournal.kinsta.cloud/wp-content/uploads/2021/09/single-post.png";
			}else{
				$bg_image = $image;
			}
            $link = get_permalink( $query->ID );
            $title = get_the_title( $query->ID );
            $excerpt = get_the_excerpt( $query->ID );
            $date = get_the_date('F j, Y');
            $author = get_the_author( $query->ID );
            $terms = get_the_terms( $query->ID , 'article_categories' );
                $class = [];
                foreach ( $terms as $term ) {
                    $category_slug[] = $term->slug;
                    if ($term->slug != 'featured-article') {
                            $category_link = get_category_link( $term->term_id );
                            $class[] = '<a href="'.esc_url( $category_link ).'"><span class="category__badge">'.$term->name.'</span></a>';
                    }
                }
				
                $content .='<div class="featured-article">';
                if (in_array("featured-article", $category_slug) && !empty($args_atts["category"])){
                  // $content .= '<p class="category"><span>'.implode(' ',$class).'</span></p>';
                   $content .='<div class="thumb-img omay ni" data-url="'.$link.'" style="background-image: url('.$bg_image.');">';
                        $content .='<div class="wrap">';
                            $content .= '<a href="'.$link.'"><h2 class="title">'.$title.'</h2></a>';
                            //$content .= '<p class="excerpt">'.$excerpt.'</p>';
    //                         $content .= '<div class="meta"><span class="date">'.$date.'</span><span class="author">'.$author.'</span></div>';
                            $content .= '<div class="meta"><span class="date">'.$date.'</span></div>';
                            //$content .='<a class="read-more" href="'.$link.'">Read more ></a>';
                        $content .='</div>';
                    $content .='</div>';
                }else{
                 // $content .= '<p class="category"><span>'.implode(' ',$class).'</span></p>';
                    $content .='<div class="thumb-img" data-url="'.$link.'" style="background-image: url('.$bg_image.');">';
                        $content .='<div class="wrap">';
                            $content .= '<a href="'.$link.'"><h2 class="title">'.$title.'</h2></a>';
                            //$content .= '<p class="excerpt">'.$excerpt.'</p>';
    //                         $content .= '<div class="meta"><span class="date">'.$date.'</span><span class="author">'.$author.'</span></div>';
                            $content .= '<div class="meta"><span class="date">'.$date.'</span></div>';
                            //$content .='<a class="read-more" href="'.$link.'">Read more ></a>';
                        $content .='</div>';
                    $content .='</div>';
                }
			$content .='</div>';
        }
        $count++;  
        endwhile;
        wp_reset_postdata();
    return $content;
}
add_shortcode( 'featured-article' , 'featured_article' );

function showForm($atts){
	$args = shortcode_atts( array(
        'color' => 'light'
    ), $atts );
    
	$output = '';
		if ($args["color"] == 'dark'){
			$output .='<div class="form-container dark">';
				$output .='<div class="wrap">';
					$output .='<h2 class="heading">Access to exclusive content</h2>';
					$output .='<p class="subheading small-width">Want access to exclusive articles, interviews with top Vancouver companies, and private networking events? Start your journey to become a member of the Vancouver Tech Journal community!</p>';
					$output .= do_shortcode("[activecampaign form=57 css=0]");
					$output .='<p class="text small-width">By filling out the form above, you consent to receive emails from Vancouver Tech Journal. You can unsubscribe at any time. View our <a href="https://staging-vancouvertechjournal.kinsta.cloud/privacy-policy/" style="color: #9EA8CC !important; text-decoration: none;">privacy policy here.</a></p>';
				$output .='</div>';
			$output .='</div>';
		} else {
			$output .='<div class="form-container light">';
				$output .='<div class="wrap">';
					$output .='<h2 class="heading">Email Signup Form</h2>';
					$output .='<p class="subheading small-width custom-body-font">Like what you’re reading? Sign up below to receive our FREE Sunday Briefing newsletter to get a weekly roundup of the hottest stories, deep dives, and exclusive interviews from the Vancouver tech industry.</p>';
					$output .= do_shortcode("[activecampaign form=55 css=0]");
					$output .='<p class="text small-width custom-body-font">By filling out the form above, you consent to receive emails from Vancouver Tech Journal. You can unsubscribe at any time. View our <a href="https://staging-vancouvertechjournal.kinsta.cloud/privacy-policy/" style="text-decoration: none;">privacy policy here.</a></p>';
				$output .='</div>';
			$output .='</div>';
		}
	wp_reset_postdata();
	return $output;
}
add_shortcode('show-form','showForm');




/* custom post type for testimonials */

function custom_post_type_testimonials() {
 
// Set UI labels for Custom Post Type
    $labels = array(
        'name'                => _x( 'Testimonials', 'Post Type General Name', '' ),
        'singular_name'       => _x( 'Testimonial', 'Post Type Singular Name', '' ),
        'menu_name'           => __( 'Testimonials', '' ),
        'parent_item_colon'   => __( 'Main', '' ),
        'all_items'           => __( 'All testimonials', '' ),
        'view_item'           => __( 'View testimonials', '' ),
        'add_new_item'        => __( 'Add New testimonial', '' ),
        'add_new'             => __( 'Add New', '' ),
        'edit_item'           => __( 'Edit testimonials', '' ),
        'update_item'         => __( 'Update testimonials', '' ),
        'search_items'        => __( 'Search testimonials', '' ),
        'not_found'           => __( 'Not Found', '' ),
        'not_found_in_trash'  => __( 'Not found in Trash', '' ),
    );
     
// Set other options for Custom Post Type
     
    $args = array(
        'label'               => __( 'testimonials', '' ),
        'description'         => __( 'testimonials', '' ),
        'labels'              => $labels,
        // Features this CPT supports in Post Editor
        'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
        // You can associate this CPT with a taxonomy or custom taxonomy. 
        'taxonomies'          => array( 'genres' ),
        /* A hierarchical CPT is like Pages and can have
        * Parent and child items. A non-hierarchical CPT
        * is like Posts.
        */ 
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 3,
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'show_in_rest' => true,
 
    );
     
    // Registering your Custom Post Type
    register_post_type( 'testimonials', $args );
 
}

 
add_action( 'init', 'custom_post_type_testimonials', 0 );

function show_testimonials(){
    $content = '';

     $args = array(
        'post_type' => 'testimonials',
		'posts_per_page' => 1,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC'
    );
    $content .= '<div class="testimonial-container">';
    $post_query = new WP_Query($args);
        if($post_query->have_posts() ) {
            while($post_query->have_posts() ) {
                $post_query->the_post();
				$content .= '<div class="testimonial-wrapper">';
					$content .='<div class="img-featured" style="background-image: url('.get_the_post_thumbnail_url().')"></div>';
					$content .= '<div class="testimonial-content-wrapper">';
						$content .= '<h2 class="testimonial-content">'.get_the_title().'</h2>';
						$content .= '<p class="testimonial-author"> <span>Name:</span>'.get_the_content().'</p>';
					$content .= '</div>';
				$content .= '</div>';
            }
        }
    $content .= '</div>';
    wp_reset_postdata();
    return $content;
}
 add_shortcode('testimonials','show_testimonials');


/* meet the team show
function show_team(){
	$content = " ";
	$blogusers = get_users( array( 'role__in' => array( 'author', 'editor','contributor' ) ) );
// Array of WP_User objects.
	foreach ( $blogusers as $user ) {
		$image = get_field('profile_image');
		if( !empty( $image ) ){
			 $content .= '<img src="'.$image["url"].'">';
		}
		$content .= '<h2 class="testimonial-content">'.$user->display_name.'</h2>';
	}
    wp_reset_postdata();
    return $content;
}
 add_shortcode('show-team','show_team');
*/
function shortcodeBlogAuthors()
{
    $display_admins = false;
    $order_by = 'meta_value_num'; // 'nicename', 'email', 'url', 'registered', 'display_name', or 'post_count'
    $order = 'DESC';
    $role = 'author, editor, contributor'; // 'subscriber', 'contributor', 'editor', 'author' - leave blank for 'all'
    $avatar_size = 161;
    $hide_empty = false; // hides authors with zero posts
    $content = '';
    if(!empty($display_admins)) {
        //$blogusers = get_users('orderby='.$order_by.'&role='.$role);
        $blogusers = get_users( [ 'role__in' => [ 'editor', 'author','contributor' ], 'orderby' => 'meta_value', 'meta_query' => 'order' ] );
    } else {
        $admins = get_users('role=administrator');
        $exclude = array();
        
        foreach($admins as $ad) {
          $exclude[] = $ad->ID;
        }
        $exclude = implode(',', $exclude);
        //$blogusers = get_users('exclude='.$exclude.'&orderby='.$order_by.'&order='.$order.'&role='.$role.'&meta_query[key]=order');
        $blogusers = get_users( [ 'role__in' => [ 'editor', 'author','contributor' ], 'orderby' => 'meta_value', 'exclude' => $exclude, 'meta_key' => 'order' ] );
    }
    
    $authors = array();
    foreach ($blogusers as $bloguser) {
    $user = get_userdata($bloguser->ID);
    
    if(!empty($hide_empty)) {
      $numposts = count_user_posts($user->ID);
      if($numposts < 1) continue;
      }
      $authors[] = (array) $user;
    }
    
    $content .= '<div class="authors-list">';
    foreach($authors as $author) {
      $first_name = get_the_author_meta( 'first_name', $author['ID'] );
      $last_name = get_the_author_meta( 'last_name', $author['ID'] );
      $avatar = get_field( 'image', 'user_'.$author['ID'] );
      $author_profile_url = get_author_posts_url($author['ID']);
      $email = get_the_author_meta( 'user_email', $author['ID'] );
	  $twitter = get_the_author_meta( 'twitter', $author['ID'] );
	  $linked = get_the_author_meta( 'linkedin', $author['ID'] );
      $bio = get_the_author_meta( 'description', $author['ID'] );
      $author_role = get_field( 'title', 'user_'.$author['ID'] );
	  $author_name = get_field( 'name', 'user_'.$author['ID'] );
      $content .= '<div class="author-main-wrap">';
          $content .= '<div class="author-wrap-details">';
              $content .= '<div class="author-gravatar"><a href="javascript:void(0);" class="contributor-link toggle-info" data-id="'.$author['ID'].'"><img style="width: 100%; height: auto;" src="' . $avatar['url'] . '"></a></div>';
		$content .= '<div class="author-social">
						
						<a href="mailto:'. $email .'" target="_blank"><svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M0 2.33332C0 1.56012 0.626802 0.933319 1.4 0.933319H12.6C13.3732 0.933319 14 1.56012 14 2.33332V3.46039L7.00001 7.39789L0 3.46038V2.33332Z" fill="#2D2E74"/>
						<path d="M0 4.53124V11.6667C0 12.4399 0.626802 13.0667 1.4 13.0667H12.6C13.3732 13.0667 14 12.4399 14 11.6667V4.53125L7.00001 8.46874L0 4.53124Z" fill="#2D2E74"/>
						</svg></a>
						
						<a href="https://twitter.com/'. $twitter .'" target="_blank"><svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M15.619 1.54076C15.0382 1.80208 14.4193 1.97529 13.774 2.05939C14.4379 1.65289 14.9445 1.01411 15.1827 0.244176C14.5638 0.622637 13.8805 0.889963 13.1522 											1.03915C12.5645 0.397363 11.727 -0.00012207 10.8133 -0.00012207C9.0405 -0.00012207 7.61331 1.47568 7.61331 3.28488C7.61331 3.5452 7.63479 3.79551 7.6875 4.0338C5.02543 								3.90063 2.66988 2.59204 1.08748 0.598608C0.811214 1.09021 0.649167 1.65289 0.649167 2.25863C0.649167 3.39602 1.22024 4.40425 2.07148 4.98796C1.55702 4.97795 1.05233 4.82476 							 0.624762 4.58347C0.624762 4.59348 0.624762 4.6065 0.624762 4.61951C0.624762 6.21546 1.73469 7.54107 3.19019 7.84645C2.92955 7.91954 2.64548 7.95458 2.35067 										    7.95458C2.14567 7.95458 1.93871 7.94256 1.74445 7.89851C2.15933 9.1991 3.33662 10.1553 4.73648 10.1863C3.64705 11.0604 2.26379 11.587 0.766309 11.587C0.503714 11.587 									0.251857 11.575 0 11.542C1.4184 12.4801 3.0994 13.0157 4.91219 13.0157C10.8045 13.0157 14.0259 8.00965 14.0259 3.67035C14.0259 3.52518 14.021 3.38501 14.0142 											3.24584C14.6497 2.78327 15.1837 2.20557 15.619 1.54076Z" fill="#213070"/>
						</svg></a>
							
						<a href="'. $linked .'" target="_blank"><svg width="15" height="14" viewBox="0 0 15 14" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M2.2556 0C1.2661 0 0.619141 0.703072 0.619141 1.62716C0.619141 2.53084 1.24682 3.25397 2.21763 3.25397H2.23641C3.24526 3.25397 3.87311 2.53084 3.87311 											1.62716C3.85425 0.703072 3.24526 0 2.2556 0Z" fill="#2D2E74"/>
							<path d="M0.619141 4.5553H3.87311V13.6664H0.619141V4.5553Z" fill="#2D2E74"/>
							<path d="M10.8496 3.90454C9.23945 3.90454 8.15972 5.51164 8.15972 5.51164V4.1284H5.1748V13.6664H8.15955V8.34003C8.15955 8.05488 8.17901 7.77018 8.25787 7.56627C8.47365 									6.99687 8.96469 6.40698 9.7893 6.40698C10.8694 6.40698 11.3013 7.28166 11.3013 8.56389V13.6664H14.2859V8.1975C14.2859 5.26783 12.8133 3.90454 10.8496 3.90454Z" 										fill="#2D2E74"/>
						</svg></a>
							
							</div>';
              $content .= '<div class="item-details_wrap">';
                  $content .= '<div class="author-name">' . $first_name . '</span> <span class="lname">' . $last_name . '</span><div class="author-role">'.$author_role.'</div></div>';
					$content .='<button class="bio-btn"  data-id="'.$author['ID'].'" >BIO</button>';
              $content .= '</div>';
          $content .= '</div>';
      $content .= '</div>';
     $content .= '<div class="backdrop backdrop-'.$author['ID'].'"></div>
                    <div class="box box-'.$author['ID'].'">
                      <div class="close">x</div>
                      <p>'.$bio.'</p>
                    </div>';
      }
    $content .= '</div>';
    return $content;
}

add_shortcode( 'show-authors' , 'shortcodeBlogAuthors' );


function show_blank_images(){
    $content = '';
    global $post;

    $content .= '<div class="blank-img-con">';
        $content .= '<div class="blank-img-wrapper">';
            $image = get_field( 'blank_image', $post->ID );
            if( !empty( $image ) ){
                $content .= '<img src="'.$image["url"].'">';
            }
            $content .= '<p style="width:100%;padding-right:100px;text-align:right;">'.get_field('caption').'</p>';
        $content .= '</div>';
    $content .= '</div>';
    return $content;
}
add_shortcode('show-blank-image','show_blank_images');


function show_all_catergory($atts, $content){
    $args = shortcode_atts( array(
        'post' => '10',
        'filter' => 0,
		'pagination' => 0
    ), $atts );
    $output = "";
      if(isset($filtered_category) && $filtered_category != 'all'){
          $loop = new WP_Query( array(
              'post_type' => 'article',
              'posts_per_page' => '-1',
              'author_name' => $filtered_author,
              'tax_query' => array(
                    array(
                        'taxonomy' => 'article_categories',
                        'field'    => 'slug',
                        'terms' => $filtered_category
                    )
                )
            )
          );
      }else{
          $loop = new WP_Query( array(
              'post_type' => 'article',
              'posts_per_page' => $args["post"],
              'paged' => get_query_var('paged') ? get_query_var('paged') : 1
            )
          );
      }
          $class = [];
          $count = 1;
          while ( $loop->have_posts() ) : $loop->the_post();  
              $permalink = get_permalink( get_the_ID() );
              $title = get_the_title( get_the_ID() );
 

              $terms = get_the_terms( get_the_ID() , 'article_categories' );
              $post_category = "";
              foreach ( $terms as $term ) {
                  if ($term->slug != 'featured-article' && $term->slug != '') {
					
					  $category_link = get_category_link( $term->term_id );
                      $class []= $term->name;
					  
                  }
			  } 
				$count++;			
      endwhile; 
      wp_reset_postdata();

    $optionscat = get_field('category_selection','option');
    $output .= '<div class="cat-con">';
        foreach( $optionscat as $options ):
            $output .='<a href="/articles-categories/'. $options->slug .'"><span>'.$options->name.'</span></a>';
        endforeach;
    $output .= '</div>';

//    $u = array_unique($class); 
// 	$output .= '<div class="cat-con">';
//    foreach($u as $ucat){
// 	   if($ucat != "Members only"){
// 	  	 $output .='<a href="/article-categories/'. strtolower($ucat) .'"><span>'.$ucat.'</span></a>';
// 	   }
//    }
// 	$output .= '</div>';
  return $output;	
}
add_shortcode('show-category','show_all_catergory');






function related_posts() {
    
    $content = '';
    
    //Get array of terms
    $terms = get_the_terms( $post->ID , 'article_categories', 'string');
    //Pluck out the IDs to get an array of IDS
    $term_ids = wp_list_pluck($terms,'term_id');
    
    //Query posts with tax_query. Choose in 'IN' if want to query posts with any of the terms
    //Chose 'AND' if you want to query for posts with all terms
    $loop = new WP_Query( array(
      'post_type' => 'article',
      'tax_query' => array(
                    array(
                        'taxonomy' => 'article_categories',
                        'field' => 'id',
                        'terms' => $term_ids,
                        'operator'=> 'IN' //Or 'AND' or 'NOT IN'
                     )),
      'posts_per_page' => 3,
      'ignore_sticky_posts' => 1,
      'orderby' => 'rand',
      'post__not_in'=>array(get_the_ID())
    ) );
    
    if ( $loop->have_posts() ) {
        $content .='<div class="related-posts">';
            $content .='<div class="related-posts-wrap">';
                $content .='<h2 class="main-title">Related Articles</h2>';
                $content .='<div class="list">';
                     while ( $loop->have_posts() ) : $loop->the_post(); 
                        $image = get_the_post_thumbnail_url(get_the_ID(),'full'); 
                        $link = get_permalink( $loop->ID );
                        $title = get_the_title( $loop->ID );
                        $excerpt = get_the_excerpt( $loop->ID );
                        $date = get_the_date('F j, Y');
						$bg_image = "";
						if(!$image){
							$bg_image = "http://staging-vancouvertechjournal.kinsta.cloud/wp-content/uploads/2021/09/single-post.png";
						}else{
							$bg_image = $image;
						}
                            $content .='<div class="post-item">';
                                $content .= '<div class="related-img" style="background-image:url('.$bg_image.')"></div>';
                                $content .='<div class="recent-post-info">';
                                    $content .='<h4 class="title"><a href="' . $link . '" title="Look '. $title .'" >' .   $title .'</a></h4>';
                                $content .='</div>';
                            $content .='</div>';
                    endwhile; wp_reset_query();
                $content .='</div>';
            $content .='</div>';
        $content .='</div>';
    }
    return $content;
}
add_shortcode( 'related-posts' , 'related_posts' );



/* shortcode for subscription */
function show_subscription(){
    $content = '';

     $args = array(
        'post_type' => 'product',
    );
    $content .= '<div class="subscription-container">';
    $post_query = new WP_Query($args);
        if($post_query->have_posts() ) {
			 $content .= '<div class="subscription-wrapper">';
				 $content .= '<div class="subscription-item-wrapper">';
				 	 $content .= '<div class="subscription-item-header avenir-heavey-22px"> THE BASIC';
				 	 $content .= '</div>';
				 	 $content .= '<div class="subscription-item-description">';
				 		$content .= '<p class="subscription-text">Et has minim elitr intellegat.<br>
						Mea aeterno eleifend antiopam ad, nam no suscipit quaerendum.';
				 		$content .= '</p>';
				 	 $content .= '</div>';
					 $content .= '<div class="subscription-item-price"> $0';
				 	 $content .= '</div>';
					 $content .= '<div class="basic-benefit-wrapper">';
						 $content .= '
								<ul class="the-basics">
								  <li>Access to our Sunday Briefing newsletter</li>
								  <li>Access to some articles on VanTechJournal.com</li>
								  <li>Meetups and networking opportunities</li>
								  <li class="disabled-li">Playbooks and Cheatsheets</li>
								  <li class="disabled-li">Startups to watch</li>
								  <li class="disabled-li">Exclusive interviews with industry insiders</li>
								  <li class="disabled-li">Members-only meetups and events</li>
								</ul>
						 ';
				 	 $content .= '</div>';
					$content .= '<div class="basic-sign-up">
							<form method="POST">
								<input type="email" placeholder="Your email" class="basic-input">
								<input type="submit" value="Sign up" class="basic-submit">
							</form>
					';
				 	 $content .= '</div>';
				 $content .= '</div>';

				/* essential  */
				 $content .= '<div class="subscription-item-wrapper">';
					 $content .= '<div class="subscription-item-header avenir-heavey-22px"> THE ESSENTIALS';
				 	 $content .= '</div>';
				 	 $content .= '<div class="subscription-item-description">';
				 		$content .= '<p class="subscription-text">Et has minim elitr intellegat.<br>
						Mea aeterno eleifend antiopam ad, nam no suscipit quaerendum.';
				 		$content .= '</p>';
				 	 $content .= '</div>';
					 $content .= '<div class="essentials-amount-wrapper">
									<strike class="essentials-first-price">$325</strike>
									<span class="essentials-real-price">$250<span class="essentials-subscription">/year</span></span>
									<span class="essentials-currency-price">CAD</span>';
				 	 $content .= '</div>';
					$content .= '<div class="basic-benefit-wrapper">';
						 $content .= '
								<ul class="the-basics">
								  <li>Access to our Sunday Briefing newsletter</li>
								  <li>Access to our Morning Report daily newsletter </li>
								  <li>Unlocks ALL articles and videos on VanTechJournal.com</li>
								  <li>Playbooks and Cheatsheets</li>
								  <li>Startups to watch</li>
								  <li>Exclusive interviews with industry insiders</li>
								  <li>Members-only meetups and events</li>
								  <p>*billed annually</p>
								</ul>
						 ';
				 	 $content .= '</div>';
                    $content .= '<div class="mem-btn-con"><a class="essential-btn add_to_cart_button ajax_add_to_cart" href="'. do_shortcode('[add_to_cart_url id="628"]') .'">Buy Now</a></div>';
				 $content .= '</div>';
				/* executive  */
				$content .= '<div class="subscription-item-wrapper">';
				 	 $content .= '<div class="subscription-item-header avenir-heavey-22px"> EXECUTIVE PLANS';
				 	 $content .= '</div>';
				 	 $content .= '<div class="subscription-item-description">';
				 		$content .= '<p class="subscription-text">Et has minim elitr intellegat.<br>
						Mea aeterno eleifend antiopam ad, nam no suscipit quaerendum.';
				 		$content .= '</p>';
				 	 $content .= '</div>';
					 $content .= '<div class="executive-amount-wrapper">
									<strike class="executive-first-price">$325</strike>
									<span class="executive-real-price">$200<span class="executive-subscription">/year</span></span>
									<span class="executive-currency-price">CAD</span>';
					 $content .= '</div>';
					 $content .= '<div class="executive-benefit-wrapper">';
						 $content .= '
								<ul class="the-executive">
								   <li>All the benefits of The Essentials with a 20% discount for you and your team</li>
								  <p style="text-align: center;">PLUS</p>
								  <li>Dedicated support from our Vancouver Tech Journal Team</li>
								  <li>Admin access for you and your team so you can customize users and transfer accounts</li>
								  <p>*Minimum 10 active accounts to access our Team level </p>
								</ul>
						 ';
				 	 $content .= '</div>';
                    //$content .= '<a class="essential-btn" href="'. do_shortcode('[add_to_cart_url id="631"]') .'">Buy Now</a>';
                    $content .= '<form class="cart" action="/product/executive-plan/" method="post" enctype="multipart/form-data">';
                        $content .= '<input type="hidden" class="input-text " name="team_name" id="team_name" placeholder="" value="Your team name">';
                        $content .= '<input type="hidden" id="quantity_614400bbf1a9a" class="input-text qty text" step="1" min="5" max="50" name="quantity" value="5" title="Qty" size="4" placeholder="" inputmode="numeric">';
            
                        $content .= '<div class="mem-btn-con added-pt"><button type="submit" name="add-to-cart" value="631" class="essential-btn single_add_to_cart_button button alt">Buy Now</button></div>';
                    $content .= '</form>';
                    //$content .= '<a class="essential-btn add_to_cart_button ajax_add_to_cart" href="'. do_shortcode('[add_to_cart_url id="631"]') .'">Buy Now</a>';
				 $content .= '</div>';
            while($post_query->have_posts()) {
                $post_query->the_post();
				/*$content .= '<div class="testimonial-wrapper">';
					$content .= '<div class="testimonial-content-wrapper">';
						$content .= '<h2 class="testimonial-content">'.get_regular_price().'</h2>';
						$content .= '<p class="testimonial-author"> <span>Name:</span>'.get_the_content().'</p>';
					$content .= '</div>';
				$content .= '</div>';*/
            }
			$content .= '</div>';
        }
    $content .= '</div>';
    wp_reset_postdata();
    return $content;
}
 add_shortcode('show-subscription','show_subscription');




/* read full article shortcode */
function membership_login(){
    $content = '';

    if( is_single() ):
		$content .= '<div class="membeship-main-con">';
			$content .= '<h2 class="membership-title ">Read the full article</h2>';
			$content .= '<p class="membership-text ">This article is exclusive to Vancouver Tech Journal members only. To read this article,<br><strong>sign in to your account below</strong>.<br>Not a member? Consider joining our community and stay up to date on the growing Vancouver tech industry.</p>';
			$content .= '<div class="membership-btn">';
				$content .='<a href="#" class="membership-btn-sign-up">Sign up</a>';
				$content .='<a href="#" class="membership-btn-log-in">Log in</a>';
			$content .= '</div>';
		$content .= '</div>';
    endif;

    return $content;
}
 add_shortcode('membership-login','membership_login');




/* custom post type for testimonials */

function custom_post_type_membership() {
 
// Set UI labels for Custom Post Type
    $labels = array(
        'name'                => _x( 'Membership Benefit', 'Post Type General Name', '' ),
        'singular_name'       => _x( 'Membership Benefit', 'Post Type Singular Name', '' ),
        'menu_name'           => __( 'Membership Benefit', '' ),
        'parent_item_colon'   => __( 'Main', '' ),
        'all_items'           => __( 'All Membership Benefit', '' ),
        'view_item'           => __( 'View Membership Benefit', '' ),
        'add_new_item'        => __( 'Add New Membership Benefit', '' ),
        'add_new'             => __( 'Add New', '' ),
        'edit_item'           => __( 'Edit Membership Benefit', '' ),
        'update_item'         => __( 'Update Membership Benefit', '' ),
        'search_items'        => __( 'Search Membership Benefit', '' ),
        'not_found'           => __( 'Not Found', '' ),
        'not_found_in_trash'  => __( 'Not found in Trash', '' ),
    );
     
// Set other options for Custom Post Type
     
    $args = array(
        'label'               => __( 'Membership Benefit', '' ),
        'description'         => __( 'Membership Benefit', '' ),
        'labels'              => $labels,
        // Features this CPT supports in Post Editor
        'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
        // You can associate this CPT with a taxonomy or custom taxonomy. 
        'taxonomies'          => array( 'genres' ),
        /* A hierarchical CPT is like Pages and can have
        * Parent and child items. A non-hierarchical CPT
        * is like Posts.
        */ 
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 6,
        'can_export'          => true,
        'has_archive'         => false,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'show_in_rest' => true,
 
    );
     
    // Registering your Custom Post Type
    register_post_type( 'membership', $args );
 
}

 
add_action( 'init', 'custom_post_type_membership', 0 );





/* shortcode for membership benefit */
function membership_benefit(){
    $content = '';

     $args = array(
        'post_type' => 'membership',
    );
    $content .= '<div class="membership-container">';
    $post_query = new WP_Query($args);
        if($post_query->have_posts() ) {
            while($post_query->have_posts()) {
                $post_query->the_post();
				$content .= '<div class="membership-item-wrapper">';
					/*$content .='<div class="membership-img-featured" style="background-image: url('.get_the_post_thumbnail_url().')"></div>';*/
					$content .='<img class="membership-img-featured" src="'.get_the_post_thumbnail_url().'">';
				 	$content .= '<div class="membership-item-header">'.get_the_title().'</div>';
				 	$content .= '<div class="membership-text">'.get_the_content().'</div>';
				 $content .= '</div>';
            }
        }
    $content .= '</div>';
    wp_reset_postdata();
    return $content;
}
add_shortcode('membership-benefit','membership_benefit');

add_action( 'after_setup_theme', 'my_remove_parent_theme_stuff', 0 );

remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );


//Change the Billing Details checkout label to Contact Information
function wc_billing_field_strings( $translated_text, $text, $domain ) {
    switch ( $translated_text ) {
        case 'Billing Details' :
        $translated_text = __( 'Step 1 of 2. Account Information', 'woocommerce' );
        break;
    }
    return $translated_text;
}
add_filter( 'gettext', 'wc_billing_field_strings', 20, 3 );

function action_woocommerce_product_thumbnails(  ) { 
    global $product;
    $content = '';
    $executiveclass = ( $product->get_id() == 631 ) ? "executive_plan" : "";

    $content .= '<div class="main-subscription-item-wrapper">';
        $content .= '<div class="subscription-item-wrapper '. $executiveclass .'">';
            $content .= '<div class="subscription-item-header avenir-heavey-22px">'. $product->get_title() .'</div>';
            $content .= '<div class="subscription-item-description">';
                $content .= '<p class="subscription-text">'. get_field( 'benefit_description', $product->get_id() ) .'</p>';
            $content .= '</div>';
            $content .= '<div class="essentials-amount-wrapper">';
                    if( $product->is_on_sale() ) {
                        $content .='<strike class="essentials-first-price">'. $product->get_regular_price() .'</strike>';
                        $content .='<span class="essentials-real-price">'. $product->get_sale_price() .'<span class="essentials-subscription">/year</span></span>';
                    } else {
                        //$content .='<strike class="essentials-first-price">'. $product->get_sale_price() .'</strike>';
                        $content .='<span class="essentials-real-price">'. $product->get_regular_price() .'<span class="essentials-subscription">/year</span></span>';
                    }
                    $content .='<span class="essentials-currency-price">CAD</span>';
            $content .= '</div>';
        $content .= '<div class="basic-benefit-wrapper">';
                if( have_rows('benefit_list', $product->get_id() ) ):
                    $content .= '<ul class="the-basics">';
                        while( have_rows( 'benefit_list', $product->get_id() ) ): the_row();
                            $content .='<li>'. get_sub_field( 'benefit_item', $product->get_id() ) .'</li>';
                        endwhile;
                    $cocntent .='</ul>';
                endif;
            $content .= '</div>';
        $content .= '</div>';
        //below the exective container
        $content .= '<div class="support-con">';
            $content .= '<h4>Need help with your subscription?</h4>';
            $content .= '<p>Email us at email@email.com</p>';
            $content .= '<p>Call us at (XXX) XXX-XXXX</p>';
        $content .= '</div>';
    $content .= '</div>';
    echo $content;
};  
add_action( 'woocommerce_before_single_product_summary', 'action_woocommerce_product_thumbnails', 10, 0 ); 

function wpse_292293_quantity_input_default( $args, $product ) {


    $productID = $product->id;
    
    foreach( WC()->cart->get_cart() as $key => $item ){
        
        if( $item['product_id'] == $productID ){    
            $args['input_value'] = $item['quantity'];   
            return $args;       
        }
        
    } 
    
    $args['input_value'] = 1;
    return $args;
    
}
add_filter( 'woocommerce_quantity_input_args', 'wpse_292293_quantity_input_default', 10, 2 );

// Removes Order Notes Title

add_filter( 'woocommerce_enable_order_notes_field', '__return_false', 9999 );

// Remove Order Notes Field

add_filter( 'woocommerce_checkout_fields' , 'njengah_order_notes' );
function njengah_order_notes( $fields ) {

    unset($fields['order']['order_comments']);

    return $fields;

}

add_filter( 'woocommerce_product_single_add_to_cart_text', 'woocommerce_custom_single_add_to_cart_text' ); 
function woocommerce_custom_single_add_to_cart_text() {
    return __( 'Update Plan Now', 'woocommerce' ); 
}

add_action( 'woocommerce_before_single_product', 'custom_single_header' );
function custom_single_header() {
    $url_to_my_attachment = "http://staging-vancouvertechjournal.kinsta.cloud/wp-content/uploads/2021/09/Logomark-1.png";
    $attachment_id = attachment_url_to_postid($url_to_my_attachment);

    $content = '';

    $content .='<div class="intro-top-con">';
        $content .='<img scr="'.wp_get_attachment_image($attachment_id);
        $content .='<h3>Choose the plan that best suits you</h3>';
        $content .='<p>Join high-powered tech and business leaders who read Vancouver Tech Journal every day.</p>';
    $content .='</div>';
    echo $content;
}

add_filter( 'woocommerce_add_to_cart_redirect', 'rv_redirect_on_add_to_cart' );
function rv_redirect_on_add_to_cart() {
	
	if ( !isset($_GET['add-to-cart']) || !is_numeric($_REQUEST['add-to-cart']) ) :
        return $url;
    endif;
     
    $product_id = absint($_GET['add-to-cart']);
    // $product = wc_get_product( $product_id );
 
    $url = get_permalink($product_id);
     
    return $url;
	
}

// Redirect page after checkout
function vtj_redirectcustom( $order_id ){
    $order = wc_get_order( $order_id );
    $url = '/my-account/edit-address/billing/';
    if ( ! $order->has_status( 'failed' ) ) {
        wp_safe_redirect( $url );
        exit;
    }
}
add_action( 'woocommerce_thankyou', 'vtj_redirectcustom');

// define the woocommerce_after_save_address_validation callback 
function action_woocommerce_after_save_address_validation( $user_id, $load_address ) {
    $user = $user_id;

    $check_option = get_option( 'save_address_option' );
    $check_user_id = get_option( 'save_address_userid' );

    if($check_option == "saved_once" && $check_user_id == $user) {
        return false;
    }else{
        update_option( 'save_address_option' , 'saved_once' );
        update_option( 'save_address_userid' , $user );

        wp_safe_redirect( "/thank-you-paid" );
        exit;
    }

}
add_action( 'woocommerce_customer_save_address', 'action_woocommerce_after_save_address_validation', 99, 2 );

// Add input field in billing address
function custom_woocommerce_billing_fields($fields)
{

    $fields['_custom_title'] = array(
        'label' => __('Title', 'woocommerce'), // Add custom field label
        'placeholder' => _x('Your Title here....', 'placeholder', 'woocommerce'), // Add custom field placeholder
        'required' => true, // if field is required or not
        'clear' => false, // add clear or not
        'type' => 'text', // add field type
        'class' => array('title-input'),    // add class name
        'priority' => 30
    );

    return $fields;
}
add_filter('woocommerce_default_address_fields', 'custom_woocommerce_billing_fields');

function my_custom_checkout_field_display_admin_order_meta($order){
    echo '<p><strong>'.__('Title').':</strong> ' . get_user_meta( $order->get_user_id(), 'billing__custom_title', true ) . '</p>';
}
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'my_custom_checkout_field_display_admin_order_meta', 10, 1 ); 




// add_filter( 'woocommerce_default_address_fields', 'bbloomer_reorder_checkout_fields' );
 
// function bbloomer_reorder_checkout_fields( $fields ) {
 
//    // default priorities:
//    // 'first_name' - 10
//    // 'last_name' - 20
//    // 'company' - 30
//    // 'country' - 40
//    // 'address_1' - 50
//    // 'address_2' - 60
//    // 'city' - 70
//    // 'state' - 80
//    // 'postcode' - 90
 
//   // e.g. move 'company' above 'first_name':
//   // just assign priority less than 10
//   $fields['_custom_title']['priority'] = 30;
 
//   return $fields;
// }
// 


//  echo do_shortcode(acf_get_field('location_info')['default_value']) ;




// function my_acf_load_field( $field ) {
		
//      $opinion ="hey";
		
//      $field['default_value'] = $opinion;
//      return $field;
		 
// }
// add_filter('acf/load_field/name=content', 'my_acf_load_field');





