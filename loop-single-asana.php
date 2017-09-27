<?php
/**
 * The single post loop Default template
 **/

if (have_posts()) {
    the_post();

    $td_mod_single = new td_module_single($post);
    
    ?>

    <article id="post-<?php echo $td_mod_single->post->ID;?>" class="<?php echo join(' ', get_post_class());?>" <?php echo $td_mod_single->get_item_scope();?>>
        <div class="td-post-header">

            <?php echo $td_mod_single->get_category(); ?>

            <header class="td-post-title">
                <?php echo $td_mod_single->get_title();?>
                <h6 id="sanskrit" name="Sanskrit Name"><?php echo get_post_meta($post->ID, 'sanskrit_name', true); ?></h6>
                <?php if (!empty($td_mod_single->td_post_theme_settings['td_subtitle'])) { ?>
                    <p class="td-post-sub-title"><?php echo $td_mod_single->td_post_theme_settings['td_subtitle'];?></p>
                <?php } ?>


                <div class="td-module-meta-info">
                    <?php echo $td_mod_single->get_author();?>
                    <?php echo $td_mod_single->get_date(false);?>
                    <?php echo $td_mod_single->get_comments();?>
                    <?php echo $td_mod_single->get_views();?>
                </div>

            </header>

        </div>

        <?php echo $td_mod_single->get_social_sharing_top();?>


        <div class="td-post-content">
        <div id="top-content">
        <?php
        // override the default featured image by the templates (single.php and home.php/index.php - blog loop)
        if (!empty(td_global::$load_featured_img_from_template)) {
            echo $td_mod_single->get_image(td_global::$load_featured_img_from_template);
        } else {
            echo $td_mod_single->get_image('td_696x0');
        }
        ?>
        <?/*asan infos begins*/?>
         <i class="oeffner td-icon-menu-down"> Asana Infos</i>
            
            <div class="td-post-asana-infos">
             <?php // Asana Level
             $connected = p2p_type( 'asana_to_asana_level' )->set_direction( 'from' )->get_connected( $post->ID );
             if ( $connected->have_posts() ) :
                ?>
            <h4>Level:</h4>            
            <div class="list-wrapper">
                <ul id="level" class="lists">
                    <?php while ( $connected->have_posts() ) : $connected->the_post(); ?>
                        <li><a onclick="goToAsanaApp('levelFilter','<?php the_title(); ?>');" data-page-url="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
                    <?php endwhile; ?>
                </ul>         
            </div>
            <?php 
            // Prevent weirdness
            wp_reset_postdata();
            endif;?>
            
            <?php // Asana Typen
            $connected = p2p_type( 'asana_type_to_asana' )->set_direction( 'to' )->get_connected( $post->ID );
            if ( $connected->have_posts() ) :
                ?>
            <h4>Typen:</h4>
            <div class="list-wrapper">
                <ul id="typen" class="lists">
                    <?php while ( $connected->have_posts() ) : $connected->the_post(); ?>
                        <li><a onclick="goToAsanaApp('typeFilter','<?php the_title(); ?>');" data-page-url="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
                    <?php endwhile; ?>
                </ul>
            </div>
            <?php 
            // Prevent weirdness
            wp_reset_postdata();
            endif;?>

            <?php // Vorbeiretende Asanahaltungen
            $connected = p2p_type( 'asana_to_asana' )->set_direction( 'from' )->get_connected( $post->ID );
            if ( $connected->have_posts() ) :
                ?>
            <h4>vorbereitende Haltungen:</h4>
            <div class="list-wrapper">
                <ul id="vorbereitende" class="lists">
                    <?php while ( $connected->have_posts() ) : $connected->the_post(); ?>
                        <li><a onclick="goToAsanaApp('searchFilter','<?php the_title(); ?>');"><?php the_title(); ?></a></li>
                    <?php endwhile; ?>
                </ul>
            </div>
            <?php 
            // Prevent weirdness
            wp_reset_postdata();
            endif;?>

           <?php // Anschliessende Asanahaltungen
           $connected = p2p_type( 'asana_to_asana2' )->set_direction( 'from' )->get_connected( $post->ID );
           if ( $connected->have_posts() ) :
            ?>
        <h4>anschliessende Haltungen:</h4>
        <div class="list-wrapper">
            <ul id="anschliessende" class="lists">
                <?php while ( $connected->have_posts() ) : $connected->the_post(); ?>
                    <li><a onclick="goToAsanaApp('searchFilter','<?php the_title(); ?>');"><?php the_title(); ?></a></li>
                <?php endwhile;  ?>
            </ul>
        </div>
        <?php 
            // Prevent weirdness
        wp_reset_postdata();
        endif;?>
        <a onclick="window.history.back();" class="zurueck"><? echo __('Back')?></a>
    </div>
        <?/*asana infos ends*/?>
        </div><?/*top-content ends*/?>
         <h6 id="sanskrit-desc" name="Sanskrit Name Beschreibung"><?php echo get_post_meta($post->ID, 'sanskrit_name_desc', true); ?></h6>
        <?php echo $td_mod_single->get_content();?>
        </div>


        <footer>
            <?php echo $td_mod_single->get_post_pagination();?>
            <?php echo $td_mod_single->get_review();?>

            <div class="td-post-source-tags">
                <?php echo $td_mod_single->get_source_and_via();?>
                <?php echo $td_mod_single->get_the_tags();?>
            </div>

            <?php echo $td_mod_single->get_social_sharing_bottom();?>
            <?php echo $td_mod_single->get_next_prev_posts();?>
            <?php #echo $td_mod_single->get_author_box();?>
            <?php echo $td_mod_single->get_item_scope_meta();?>
        </footer>

    </article> <!-- /.post -->

    <?php echo $td_mod_single->related_posts();?>

<?php
} else {
    //no posts
    echo td_page_generator::no_posts();
}