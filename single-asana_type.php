<?php
/*
Template Name: Asana
*/

locate_template('includes/wp_booster/td_single_template_vars.php', true);

get_header();

global $loop_module_id,$post;

$td_mod_single = new td_module_single($post);
?>
<div class="td-main-content-wrap">

	<div class="td-container td-post-template-default">
		<div class="td-crumb-container"><?php echo td_page_generator::get_single_breadcrumbs($td_mod_single->title); ?></div>

		<div class="td-pb-row">
			<?php
            //the default template            
			td_global::$load_featured_img_from_template = 'td_1068x0';
			?>
			<div class="td-pb-span12 td-main-content" role="main">
				<div class="td-ss-main-content">
                                 <?php // Asana Typen
                                 $connected = new WP_Query( array(
                                 	'connected_type' => 'asana_type_to_asana',
                                 	'connected_items' => get_queried_object(),
                                 	'nopaging' => true,
                                 	) );
                                 if ( $connected->have_posts() ) :
                                 	?>
                                 <table>
                                    <caption>Verbundene Asanas</caption>
                                    <thead>
                                     <tr>
                                         <th>Bild</th>
                                         <th>Title</th>
                                         <th>Sanskrit Name</th>
                                         <th>Sanskrit Name Beschreibung</th>
                                     </tr>
                                 </thead>
                                 <tbody>
                                    <?php while ( $connected->have_posts() ) : $connected->the_post(); ?>
                                        <tr  class="asana-types asana-elements">
                                           <td class="asana-img"><?php echo the_post_thumbnail('td_90x60',array('class'=>'asana-element-img'));?></td>                                       
                                           <td class="asana-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></td>
                                           <td class="asana-sanskrit-name"><?php echo get_post_meta($post->ID, 'sanskrit_name', true); ?></td>
                                           <td class="asana-sanskrit-name"><?php echo get_post_meta($post->ID, 'sanskrit_name_desc', true); ?></td>
                                       </tr>
                                   <?php endwhile; ?>
                               </tbody>                                    

                           </table>
                           <a class="button" href="../../asana-finder">Asana Finder</a>
                                 <?php 
// Prevent weirdness
                                 wp_reset_postdata();
                                 endif;?>
                             </div>
                         </div>
                         <?php  

                         ?>
                     </div> <!-- /.td-pb-row -->
                 </div> <!-- /.td-container -->
             </div> <!-- /.td-main-content-wrap -->
             
             <?php get_footer(); ?>