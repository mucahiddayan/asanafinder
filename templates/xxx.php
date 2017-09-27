<?php
/**
 * Plugin Name: Asana Finder
 * Plugin URI: //dayan.one
 * Description: Nur für functions.php
 * Version: 1.0.0
 * Author: Mücahid Dayan
 * Author URI: http://dayan.one
 * License: GPL2
 */
 
 
 function asana_finder_styles() {      
    wp_enqueue_style( 'afs', plugin_dir_url( __FILE__ ) .'style.css', '', true );
   # wp_enqueue_script( 'jquery2.2.4', 'https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js', '', true );
    wp_enqueue_style('fontAwesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css', '3.2.1', 'all' );
    wp_enqueue_script( 'asana-finder-main-js', plugin_dir_url( __FILE__ ) . 'main.js', array(), '1.0', true );
  }

  add_action( 'wp_enqueue_scripts', 'asana_finder_styles' ); 

/*asana finder scripts*/
  function my_connection_types() {
    p2p_register_connection_type( array(
        'name' => 'lehrer_to_event',
        'from' => 'lehrer',
        'to' => 'event',
        'reciprocal' => true,
        ) );   

    p2p_register_connection_type( array(
        'name' => 'asana_to_asana',
        'from' => 'asana',
        'to' => 'asana',
        'reciprocal' => false,
        'title' => array( 'from' => 'vorbereitende Asanas','to'=>'')
        ) );

    p2p_register_connection_type( array(
        'name' => 'asana_to_asana2',
        'from' => 'asana',
        'to' => 'asana',
        'reciprocal' => false,
        'title' => array( 'from' => 'anschliessende Asanas','to'=>'')
        ) );
    
    p2p_register_connection_type( array(
        'name' => 'asana_to_asana_level',
        'from' => 'asana',
        'to' => 'asana_level',
        'cardinality' => 'many_to_one',
        ) );    

    p2p_register_connection_type( array(
        'name' => 'asana_type_to_asana',
        'from' => 'asana_type',
        'to' => 'asana',
        'reciprocal' => true,
        ) );
    /*p2p_register_connection_type( array(
      'name' => 'asana_to_asana_against',
      'from' => 'asana',
      'to' => 'asana_against',
      'reciprocal' => true,
      ) );
    p2p_register_connection_type( array(
      'name' => 'asana_to_asana_effect',
      'from' => 'asana',
      'to' => 'asana_effect',
      'reciprocal' => true,
      ) );*/
}
add_action( 'p2p_init', 'my_connection_types' );

add_action( 'load-post.php', 'sanskrit_name_boxes_setup' );
add_action( 'load-post-new.php', 'sanskrit_name_boxes_setup' );

function sanskrit_name_boxes_setup(){
    add_action('add_meta_boxes','sanskrit_name_add_boxes');
    add_action('save_post','sanskrit_name_save',10,2);
}

function sanskrit_name_add_boxes(){
    add_meta_box(
        'sanskrit_name',
        esc_html__('Sanskrit Name','example'),
        'sanskrit_name_meta_box',
        'asana',
        'normal',
        'default'
        );    
}

function sanskrit_name_save($post_id,$post){
    /* Verify the nonce before proceeding. */
    if ( !isset( $_POST['sanskrit_name_nonce'] ) || !wp_verify_nonce( $_POST['sanskrit_name_nonce'], basename( __FILE__ ) ) )
        return $post_id;

    /* Get the post type object. */
    $post_type = get_post_type_object( $post->post_type );

    /* Check if the current user has permission to edit the post. */
    if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
        return $post_id;

    /* Get the posted data and sanitize it for use as an HTML class. */
    $new_meta_value = ( isset( $_POST['sanskrit_name'] ) ? $_POST['sanskrit_name']  : '' );
    $new_meta_value2 = ( isset( $_POST['sanskrit_name_desc'] ) ? $_POST['sanskrit_name_desc']  : '' );

    /* Get the meta key. */
    $meta_key = 'sanskrit_name';
    $meta_key2 = 'sanskrit_name_desc';

    /* Get the meta value of the custom field key. */
    $meta_value = get_post_meta( $post_id, $meta_key, true );
    $meta_value2 = get_post_meta( $post_id, $meta_key2, true );

    /* If a new meta value was added and there was no previous value, add it. */
    if ( $new_meta_value && '' == $meta_value  ||  $new_meta_value2 && '' == $meta_value2){
        add_post_meta( $post_id, $meta_key, $new_meta_value, true );
        add_post_meta( $post_id, $meta_key2, $new_meta_value2, true );
    }
    /* If the new meta value does not match the old value, update it. */
    elseif ( $new_meta_value && $new_meta_value != $meta_value || $new_meta_value2 && $new_meta_value2 != $meta_value2 ){
        update_post_meta( $post_id, $meta_key, $new_meta_value );
        update_post_meta( $post_id, $meta_key2, $new_meta_value2 );
    }
    /* If there is no new meta value but an old value exists, delete it. */
    elseif ( '' == $new_meta_value && $meta_value || '' == $new_meta_value2 && $meta_value2 ){
        delete_post_meta( $post_id, $meta_key, $meta_value );
        delete_post_meta( $post_id, $meta_key2, $meta_value2 );
    }
}

/* Display the post meta box. */
function sanskrit_name_meta_box( $object, $box ) { 
    wp_nonce_field( basename( __FILE__ ), 'sanskrit_name_nonce' ); ?>

    <p>
        <label for="sanskrit_name"><?php _e( "Add a Sanskrit Name to asana.", 'example' ); ?></label>
        <br />
        <input class="widefat" type="text" name="sanskrit_name" id="sanskrit_name" value="<?php echo esc_attr( get_post_meta( $object->ID, 'sanskrit_name', true ) ); ?>" size="30" />
    </p>
    <p>
        <label for="sanskrit_name_desc"><?php _e( "Add a Sanskrit Name Description to asana.", 'example' ); ?></label>
        <br />
        <input class="widefat" type="text" name="sanskrit_name_desc" id="sanskrit_name_desc" value="<?php echo esc_attr( get_post_meta( $object->ID, 'sanskrit_name_desc', true ) ); ?>" size="30" />
    </p>
    <?php }
/*end of asana finder scripts*/

  /*buddypress*/
  function bp_custom_user_nav_item() {
  global $bp;
  
  $args = array(
    
    'name' => __('My Sequences', 'buddypress'),
    'slug' => 'saved-sequences',
    'default_subnav_slug' => 'saved_sequences',
    'position' => 50,
    'show_for_displayed_user' => false,
    'screen_function' => 'bp_custom_user_nav_item_screen',
    'item_css_id' => 'saved-sequences',
    
    
    );   
  
  bp_core_new_nav_item( $args );
  
}
add_action( 'bp_setup_nav', 'bp_custom_user_nav_item', 99 );

function bp_custom_user_nav_item_screen() {
  add_action( 'bp_template_content', 'bp_saved_sequenzen' );
  add_action( 'bp_template_content', 'bp_shared_sequenzen' );
  bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}

function bp_saved_sequenzen() {
  $userID = get_current_user_id();
  $myseq = get_asanas_from_user($userID,'saved-asanas');
  $style = 'style="width:5%;text-align:center;"';


  ?>
  <table class="sequences">
    <caption>Gespeicherte Sequenzen</caption>
    <thead>
      <tr>
        <th>Label</th>
        <th <?php echo $style;?>>Edit</th>
        <th <?php echo $style;?>>Share</th>
        <th <?php echo $style;?>>Delete</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($myseq as $key => $value):
      $shareLink = base64_encode($userID.'.'.$key);
      $label = intval($value['label']) != 0 ? date('d.m.Y H:i:s',(floatval($value['label'])/1000)) : $value['label'];
      echo '<tr data-share="getSavedSequence='.$shareLink.'" data-user="'.get_current_user_id().'" data-key="'.$key.'" data-type="saved-asanas"><td><a  class="editable" target="_blank" href="'.get_site_url().'/asana-finder/app/?getSavedSequence='.$shareLink.'">'.$value['label'].'</a></td><td style="width:5%"text-align:center";><i class="fa fa-pencil edit" aria-hidden="true"></i></td><td '.$style.'><i class="fa fa-share-alt share" aria-hidden="true"></i></td><td '.$style.'><i class="fa fa-trash delete" aria-hidden="true"></i></td></tr>';
      ?>
      <?php
      endforeach; ?>
    </tbody>
  </table>
  <?php 
}

function bp_shared_sequenzen() {
  $userID = get_current_user_id();
  $myseq = get_asanas_from_user($userID,'shared-asanas');
  $style = 'style="width:5%;text-align:center;"';

  ?>
  <table class="sequences">
    <caption>Geteilte Sequenzen</caption>
    <thead>
      <tr>
        <th>Label</th>
        <th <?php echo $style;?>>Edit</th>
        <th <?php echo $style;?>>Share</th>
        <th <?php echo $style;?>>Delete</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($myseq as $key => $value):
      $shareLink = base64_encode($userID.'.'.$key);
      echo '<tr data-share="getSharedSequence='.$shareLink.'" data-user="'.get_current_user_id().'" data-key="'.$key.'" data-type="shared-asanas"><td><a  class="editable" target="_blank" href="'.get_site_url().'/asana-finder/app/?getSavedSequence='.$shareLink.'">'.$value['label'].'</a></td><td style="width:5%"text-align:center";><i class="fa fa-pencil edit" aria-hidden="true"></i></td><td '.$style.'><i class="fa fa-share-alt share" aria-hidden="true"></i></td><td '.$style.'><i class="fa fa-trash delete" aria-hidden="true"></i></td></tr>';
      ?>
      <?php
      endforeach; ?>
    </tbody>
  </table>
  <?php 
}

/*bp ends*/

/*wp rest api*/

add_action( 'rest_api_init', function () {
  register_rest_field( 'post',
    'post_thumbnail',
    array(
      'get_callback'    => 'yj_get_thumbnail',
      'update_callback' => null,
      'schema'          => null,
      )
    );
  register_rest_field( 'page',
    'shared-asanas',
    array(
      'get_callback'    => 'get_shared_asanas',
      'update_callback' => null,
      'schema'          => null,
      )
    );
  register_rest_field( 'user',
    'shared-asanas',
    array(
      'get_callback'    => null,
      'update_callback' => null,
      'schema'          => null,
      )
    );

  register_rest_field( 'user',
    'saved-asanas',
    array(
      'get_callback'    => null,
      'update_callback' => null,
      'schema'          => null,
      )
    );
  register_rest_route( 'yj/v2', '/user-asanas/', array(
    array(
      'methods'         => WP_REST_Server::READABLE,
      'callback'        => 'get_user_sequence',      
      ),      
    array(
      'methods'  => WP_REST_Server::DELETABLE,
      'callback' => 'delete_user_sequence',
      'permission_callback' => function () {
        return current_user_can( 'edit_others_posts' );
      },
      'args'     => array(
        'force'    => array(
          'default'      => false,
          ),
        )
      ),
    array(
      'methods' => WP_REST_Server::CREATABLE,
      'callback' => 'add_user_asanas',
      'permission_callback' => function () {
        return current_user_can( 'edit_others_posts' );
      },
      ),      
    )
  ); 
  register_rest_route( 'yj/v2', '/user-asanas-update/', 
    array(
      array(
        'methods' => 'POST',
        'callback'=> 'update_user_sequence'
        ),
      )       
    );
  register_rest_route( 'yj/v2', '/asanas/', array(
    'methods' => 'GET',
    'callback' => 'get_asanas',
    ) 
  );      
});

  function extract_sequence($seqs,$exclude){
    $newArray = array();
    foreach ($seqs as $key => $value) {
      foreach ($value as $k => $v) {
        if(isset($exclude)){unset($v[$exclude]);}
        $newArray[$k] = $v;               
      }
    }
    return $newArray;
  }

  function get_asanas_from_user($id,$type){
    return extract_sequence(get_user_meta($id,$type));
  }

  function get_saved_sequnce_from_user($id,$key=false){    
     $seqs =  extract_sequence(get_user_meta($id,'saved-asanas'),'label');     
     if ($key) {       
       return $seqs[$key];
     }else{
      $last = end($seqs);     
      return $last;
     }     
  }

 
  
  function add_user_asanas($request){
    $parameters = $request->get_params();
    $userID = $parameters['userID'];
    $data = $parameters['value'];
    $label = isset($parameters['label'])?$parameters['label']:$parameters['key'];
    $data['label']=$label;
    if(isset($userID) && isset($parameters['key']) && isset($parameters['value'])){
      if(add_user_meta($userID,$parameters['type'],array($parameters['key'] => $data))){
        return array('userID'=>$userID,'key'=>$parameters['key']);
      }
      else{
        return new WP_Error( 'unknown_error', 'Asanas could not be added to given user:'.$userID, array( 'status' => 404 ) );
      }
    }
    else{
      return new WP_Error( 'invalid_parameter', 'Parameters are empty', array( 'status' => 404 ) );
    }    
  }
  
  function get_user_sequence($request){
    $parameters = $request->get_params();
    $exclude = isset($parameters['exclude'])?$parameters['exclude']:'';
    $seqs = extract_sequence(get_user_meta($parameters['userID'],$parameters['type']),$exclude);
    if (isset($parameters['key'])) {      
      return $seqs[$parameters['key']];     
    }
    else{      
      return $seqs;
    }    
  }
  

  function update_user_sequence($request){
    $params = $request->get_params();
    if (!isset($params['userID'])) {return new WP_Error( 'invalid_parameter', 'userID empty', array( 'status' => 404 ) );}
    if (!isset($params['type'])) {return new WP_Error( 'invalid_parameter', 'type empty', array( 'status' => 404 ) );}
    if (!isset($params['key'])) {return new WP_Error( 'invalid_parameter', 'key empty', array( 'status' => 404 ) );}
    if (!isset($params['func'])) {return new WP_Error( 'invalid_parameter', 'func empty', array( 'status' => 404 ) );}
    $seqs =  extract_sequence(get_user_meta($params['userID'],$params['type']));
    if ($params['func'] == 'update_label') {
      $seqs[$params['key']]['label'] = $params['label'];      
      update_user_meta($params['userID'],$params['type'],$seqs);
      return 'updated';
    }
  }

  /*test funcs*/
  function update_user_sequence_test($params){
    if (!isset($params['userID'])) {return new WP_Error( 'invalid_parameter', 'userID empty', array( 'status' => 404 ) );}
    if (!isset($params['type'])) {return new WP_Error( 'invalid_parameter', 'type empty', array( 'status' => 404 ) );}
    if (!isset($params['key'])) {return new WP_Error( 'invalid_parameter', 'key empty', array( 'status' => 404 ) );}
    if (!isset($params['func'])) {return new WP_Error( 'invalid_parameter', 'func empty', array( 'status' => 404 ) );}
    $seqs =  extract_sequence(get_user_meta($params['userID'],$params['type']));
    if ($params['func'] == 'update_label') {
      $seqs[$params['key']]['label'] = $params['label'];      
      update_user_meta($params['userID'],$params['type'],$seqs);
    }
  }
  /**/
  

  function delete_user_sequence($request){
    $parameters = $request->get_params();
    if (isset($parameters['key'])) {
      $tmp = get_user_meta($parameters['userID'],$parameters['type']);
      delete_user_meta($parameters['userID'],$parameters['type']);      
      foreach ($tmp as $key => $value) {
        foreach ($value as $k => $v) {
          if (!empty($k)&& !empty($v) && $k != $parameters['key']) {
           add_user_meta($parameters['userID'],$parameters['type'],array($k => $v));
          }
        }
      }      
      return $updated;
    }
    else{
      delete_user_meta($parameters['userID'],$parameters['type']); 
    }
  }

   function get_asana_by_id($data){
    return $data['id'];
  }

  function get_asanas() {
    $posts_query  = new \WP_Query();
    $query_result = $posts_query->query( array(
      'post_type' => 'asana',
      'posts_per_page' => 108,
      'orderby' => 'title',
      'order'   => 'asc',
      ) );

    $data = array();
    if ( ! empty( $query_result ) ) {
      $counter = 0;
      foreach ( $query_result as $post ) {
        $image = get_post_thumbnail_id( $post->ID,'td_314x314' ); 
        $image_srcset = wp_get_attachment_image_srcset( $image);      
        if ( $image ) {
          $_image = wp_get_attachment_image_src( $image, 'td_314x314' );            
          if ( is_array( $_image ) ) {
            $image = $_image[0];
          } 
          else{
            $image = false;
          }
        }

        $levels_=array();
        $vorbereitende_=array();
        $anschliessende_=array();
        $types_=array();

        if (function_exists('p2p_type')) {
          $levels = p2p_type( 'asana_to_asana_level' )->set_direction( 'from' )->get_connected( $post->ID )->posts;

          foreach ($levels as $level) {
            $levels_[]= $level->post_title;
          } 
          $vorbereitende = p2p_type( 'asana_to_asana' )->set_direction( 'from' )->get_connected( $post->ID )->posts;
          foreach ($vorbereitende as $level) {
            $vorbereitende_[]= $level->post_title;
          } 
          $anschliessende = p2p_type( 'asana_to_asana2' )->set_direction( 'from' )->get_connected( $post->ID )->posts;
          foreach ($anschliessende as $level) {
            $anschliessende_[]= $level->post_title;
          } 
          $types = p2p_type( 'asana_type_to_asana' )->set_direction( 'to' )->get_connected( $post->ID )->posts;
          foreach ($types as $level) {
            $types_[]= $level->post_title;
          } 
        }

        $data[] = array(
          'id'=>$post->ID,
          'title'         => $post->post_title,
          'sanskrit_name'=> get_post_meta($post->ID, 'sanskrit_name', true),
          'sanskrit_name_desc'=> get_post_meta($post->ID, 'sanskrit_name_desc', true),
          'levels' => $levels_,
          'vorbereitende' =>$vorbereitende_,
          'anschliessende' => $anschliessende_,
          'types' => $types_,            
          'link'         => get_the_permalink( $post->ID ),
          'image_markup' => get_the_post_thumbnail( $post->ID, 'large' ),
          'image_src'    => $image,
          'image_srcset' => $image_srcset,
          'excerpt'      => $post->post_excerpt,
          'tagline'      => get_post_meta( $post->ID, 'product_tagline', true ),            
          'slug'         => $post->post_name,
          );
      }
    }
    return $data;   
  }

  function yj_get_thumbnail($post){
    if(has_post_thumbnail($post['id'])){
      $imgArray = wp_get_attachment_image_src( get_post_thumbnail_id( $post['id'] ), 'full' );
      $imgURL = $imgArray[0];
      return $imgURL;
    }else{
      return false; 
    }
  } 

  /*wp rest api ends*/
