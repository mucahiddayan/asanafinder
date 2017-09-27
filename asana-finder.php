<?php
/**
 * Plugin Name: Asana Finder
 * Plugin URI: //dayan.one
 * Description: Asana Finder App
 * Version: 1.0.0
 * Author: Mücahid Dayan
 * Author URI: http://dayan.one
 * License: GPL2
 */
class Asana_Finder {

  protected static $_instance = null;
  protected $templates;
  protected $appURL;
  private $slug = 'asana-finder';
  private $types = array(
      'getSharedSequence' => 'shared-asanas',
      'getSavedSequence' => 'saved-asanas'
      );

  public function __construct(){
    add_action( 'wp_enqueue_scripts', array( $this,'asana_finder_styles') );
    add_action( 'p2p_init', array( $this,'my_connection_types') );
    add_action( 'load-post.php', array( $this,'sanskrit_name_boxes_setup' ));
    add_action( 'load-post-new.php', array( $this,'sanskrit_name_boxes_setup' ));
    add_action( 'rest_api_init', array( $this,'custom_end_points'));
    add_action( 'bp_setup_nav', array( $this,'bp_custom_user_nav_item'), 99 );
    add_action( 'plugins_loaded', array( $this, 'get_asana_finder_template' ) );
    add_filter( 'single_template', array( $this,'asana_templates') );
    
    add_action( 'admin_menu', array( $this,'create_plugin_settings_page' ) );
    add_action( 'admin_init', array( $this, 'setup_sections' ) );
    add_action( 'admin_init', array( $this, 'setup_fields' ) );    
  }

  /*settings page*/
  public function create_plugin_settings_page(){

    $page_title = "Asana Finder Settings Page";
    $menu_title = "Asana Finder";
    $capability = "manage_options";     
    $callback = array($this,'plugin_settings_page_content');
    $icon   = "dashicons-editor-customchar";
    $position = 100;

    if(!add_submenu_page("/edit.php?post_type=asana", $page_title, $menu_title, $capability, $this->slug, $callback )){
      add_menu_page($page_title,$menu_title,$capability,$this->slug,$callback,$icon,$position);
    };
  }

  public function plugin_settings_page_content() { 
    ?>
    <div class="wrap">
      <h2>Asana Finder Settings Page</h2>
      <form method="post" action="options.php">
        <?php
        settings_fields( $this->slug );
        do_settings_sections( $this->slug );
        submit_button();
        ?>
      </form>
    </div> 
    <?php
  }
  public function setup_sections() {
    add_settings_section( 'asana_finder_config', 'Settings', array( $this, 'section_callback' ), $this->slug );
  }

  public function section_callback( $arguments ) {
    switch( $arguments['id'] ){
      case 'asana_finder_config':
      echo 'Asana Finder';
      break;        
    }
  }

  public function setup_fields() {
    $fields = array(
      array(
        'uid' => 'af_app_url',
        'label' => 'URL to APP',
        'section' => 'asana_finder_config',
        'type' => 'text',
        'options' => false,
        'placeholder' => 'URL zur App eintragen',
        'helper' => '',
        'supplemental' => '',
        'default' => ''
        ),
        array(
          'uid' => 'af_bugreport_page_id',
          'label' => 'Bug Report Page Id',
          'section' => 'asana_finder_config',
          'type' => 'text',
          'options' => false,
          'placeholder' => 'Bug Report Page Id eintragen',
          'helper' => '',
          'supplemental' => '',
          'default' => ''
        ),  
      );
    foreach( $fields as $field ){
      add_settings_field( $field['uid'], $field['label'], array( $this, 'field_callback' ), $this->slug, $field['section'], $field );
      register_setting( $this->slug, $field['uid'] );
    }
  }

  public function field_callback( $arguments ) {
    $value = get_option( $arguments['uid'] ); 
    if( ! $value ) { 
      $value = $arguments['default']; 
    }
    switch( $arguments['type'] ){
      case 'text': 
      printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value );
      break;
      case 'checkbox':
      printf('<input name="%1$s" id="%1$s" type="%2$s" value="1" placeholder="%3$s" %4$s />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], checked(1, $value,false));
      break;
    }
    if( $helper = $arguments['helper'] ){
      printf( '<span class="helper"> %s</span>', $helper ); 
    }
    if( $supplimental = $arguments['supplemental'] ){
      printf( '<p class="description">%s</p>', $supplimental ); 
    }
  }

  /*settings page ends*/

  public static function getInstance(){
    if (null === self::$_instance)
    {
      self::$_instance = new self;
    }
    return self::$_instance;
  }

  public function add_new_template( $posts_templates ) {
    $posts_templates = array_merge( $posts_templates, $this->templates );
    return $posts_templates;
  }

  public function asana_templates($template){
    global $wp_query, $post;
    if ( $post->post_type == 'asana' ) {
        $page_template = plugin_dir_path( __FILE__ ) . 'templates/single-asana.php';
    }
    if ( $post->post_type == 'asana_level' ) {
        $page_template = plugin_dir_path( __FILE__ ) . 'templates/single-asana_level.php';
    }
    if ( $post->post_type == 'asana_type' ) {
        $page_template = plugin_dir_path( __FILE__ ) . 'templates/single-asana_type.php';
    }
    return $page_template;
  }

  public function get_asana_finder_template(){
    add_filter(
        'theme_page_templates', array( $this, 'add_new_template' )
      ); 

    add_filter(
      'wp_insert_post_data', 
      array( $this, 'register_project_templates' ) 
    );
    
    add_filter(
      'template_include', 
      array( $this, 'view_project_template') 
    );
    
    $this->templates = array(
      'asana-finder-template.php' => 'Asana Finder Sequence Creator',
    );
  }

  /**
   * Adds our template to the pages cache in order to trick WordPress
   * into thinking the template file exists where it doens't really exist.
   */
  public function register_project_templates( $atts ) {

    // Create the key used for the themes cache
    $cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

    // Retrieve the cache list. 
    // If it doesn't exist, or it's empty prepare an array
    $templates = wp_get_theme()->get_page_templates();
    if ( empty( $templates ) ) {
      $templates = array();
    } 

    // New cache, therefore remove the old one
    wp_cache_delete( $cache_key , 'themes');

    // Now add our template to the list of templates by merging our templates
    // with the existing templates array from the cache.
    $templates = array_merge( $templates, $this->templates );

    // Add the modified cache to allow WordPress to pick it up for listing
    // available templates
    wp_cache_add( $cache_key, $templates, 'themes', 1800 );

    return $atts;

  } 

  /**
   * Checks if the template is assigned to the page
   */
  public function view_project_template( $template ) {
    
    // Get global post
    global $post;

    // Return template if post is empty
    if ( ! $post ) {
      return $template;
    }

    // Return default template if we don't have a custom one defined
    if ( ! isset( $this->templates[get_post_meta( 
      $post->ID, '_wp_page_template', true 
    )] ) ) {
      return $template;
    } 

    $file = plugin_dir_path( __FILE__ ). get_post_meta( 
      $post->ID, '_wp_page_template', true
    );

    // Just to be safe, we check if the file exist first
    if ( file_exists( $file ) ) {
      return $file;
    } else {
      echo $file;
    }

    // Return template
    return $template;

  }

  function asana_finder_styles() {
    $id  = get_option('af_bugreport_page_id');
    wp_enqueue_style( 'afs', plugin_dir_url( __FILE__ ) .'css/style.css', '', true );
    wp_enqueue_style('fontAwesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css', '3.2.1', 'all' );
    wp_register_script('asana-finder-main-js',plugin_dir_url( __FILE__ ) . 'js/main.js', array(), '1.0', true );
    wp_localize_script('asana-finder-main-js','pluginSettings',array('homeURL'=>home_url(),'nonce'=>wp_create_nonce( 'wp_rest' ),'bugreport_page' => $id));
    wp_enqueue_script( 'asana-finder-main-js');
  }
  public function my_connection_types() {
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
  }

  function sanskrit_name_boxes_setup(){
    add_action('add_meta_boxes',array( $this,'sanskrit_name_add_boxes'));
    add_action('save_post',array( $this,'sanskrit_name_save'),10,2);
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

    if ( !isset( $_POST['sanskrit_name_nonce'] ) || !wp_verify_nonce( $_POST['sanskrit_name_nonce'], basename( __FILE__ ) ) )
      return $post_id;


    $post_type = get_post_type_object( $post->post_type );


    if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
      return $post_id;


    $new_meta_value = ( isset( $_POST['sanskrit_name'] ) ? $_POST['sanskrit_name']  : '' );
    $new_meta_value2 = ( isset( $_POST['sanskrit_name_desc'] ) ? $_POST['sanskrit_name_desc']  : '' );


    $meta_key = 'sanskrit_name';
    $meta_key2 = 'sanskrit_name_desc';


    $meta_value = get_post_meta( $post_id, $meta_key, true );
    $meta_value2 = get_post_meta( $post_id, $meta_key2, true );


    if ( $new_meta_value && '' == $meta_value  ||  $new_meta_value2 && '' == $meta_value2){
      add_post_meta( $post_id, $meta_key, $new_meta_value, true );
      add_post_meta( $post_id, $meta_key2, $new_meta_value2, true );
    }

    elseif ( $new_meta_value && $new_meta_value != $meta_value || $new_meta_value2 && $new_meta_value2 != $meta_value2 ){
      update_post_meta( $post_id, $meta_key, $new_meta_value );
      update_post_meta( $post_id, $meta_key2, $new_meta_value2 );
    }

    elseif ( '' == $new_meta_value && $meta_value || '' == $new_meta_value2 && $meta_value2 ){
      delete_post_meta( $post_id, $meta_key, $meta_value );
      delete_post_meta( $post_id, $meta_key2, $meta_value2 );
    }
  }


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
    <?php 
  }

  function bp_custom_user_nav_item() {
    global $bp;

    $args = array(
      'name' => __('My Sequences', 'buddypress'),
      'slug' => 'my-sequences',
      'default_subnav_slug' => 'my_sequences',
      'position' => 50,
      'show_for_displayed_user' => false,
      'screen_function' => array($this,'bp_custom_user_nav_item_screen'),
      'item_css_id' => 'my-sequences',
      );   

    bp_core_new_nav_item( $args );

  }


  function bp_custom_user_nav_item_screen() {
    add_action( 'bp_template_content', array( $this,'bp_my_sequences' ));
    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
  }

  function labelToDate($str){
    if(preg_match('/[a-z\.]/i',$str)){
      return $str;      
    }else{      
      return date('d.m.Y H:i:s',(floatval($str)/1000)) ;
    }
  }

  function sequenceTable($arr,$userID,$type){
    $style = 'style="width:5%;text-align:center;"';
    $shareLinktype = $type;
    foreach ($arr as $key => $value){
      $shareLink = base64_encode($userID.'.'.$key);
      $label = $this->labelToDate($value['label']);        
      echo '<tr data-share="'.$shareLinktype.'='.$shareLink.'" title="'.$this->labelToDate($key).'" data-user="'.get_current_user_id().'" data-key="'.$key.'" data-type="'.$type.'"><td><a  class="editable" target="_blank" href="'.get_site_url().'/asana-finder/app/?'.$type.'='.$shareLink.'">'.$label.'</a></td><td style="width:5%"text-align:center";><i class="fa fa-pencil edit" aria-hidden="true"></i></td><td '.$style.'><i class="fa fa-share-alt share" aria-hidden="true"></i></td><td '.$style.'><i class="fa fa-trash delete" aria-hidden="true"></i></td></tr>';
    }
  }


  function bp_my_sequences() {
    $userID = get_current_user_id();
    $shared = $this->get_sequence_from_user($userID,'shared-asanas');
    $saved = $this->get_sequence_from_user($userID,'saved-asanas');

    echo '<a id="create-new-sequence" target="_blank" href="'.home_url().get_option( 'af_app_url').'"><i class="fa fa-plus" aria-hidden="true"></i> Neue Sequenz anlegen</a>';
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
        <?php 
        $this->sequenceTable($saved,$userID,'getSavedSequence');
        ?>
      </tbody>
    </table>
    <?php 
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
        <?php 
         $this->sequenceTable($shared,$userID,'getSharedSequence');
         ?>
      </tbody>
    </table>
    <?php 
  }

  function custom_end_points() {
    register_rest_field( 'post',
      'post_thumbnail',
      array(
        'get_callback'    => 'yj_get_thumbnail',
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
    register_rest_route( 'yj/v2', '/asanas/', array(
      'methods' => 'GET',
      'callback' => array($this,'get_asanas',),
      ) 
    );      
    register_rest_route( 'yj/v2', '/user-asanas/', array(
      array(
        'methods'         => WP_REST_Server::READABLE,
        'callback'        => array($this,'get_sequence',      ),
        ),      
      array(
        'methods'  => WP_REST_Server::DELETABLE,
        'callback' => array($this,'delete_sequence',),
        'permission_callback' => function () {
          return current_user_can( 'edit_posts' );
        },
        'args'     => array(
          'force'    => array(
            'default'      => false,
            ),
          )
        ),
      array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => array($this,'add_sequence',),
        /*'permission_callback' => function () {
          return current_user_can( 'edit_posts' );
        },*/
        ),      
      )
    ); 
    register_rest_route( 'yj/v2', '/user-asanas-update/', 
      array(
        array(
          'methods' => 'POST',
          'callback'=> array($this,'update_sequence'),
          ),
        )       
      );    

  }

  function extract_sequence($seqs,$exclude=""){
    $newArray = array();
    foreach ($seqs as $key => $value) {
      foreach ($value as $k => $v) {
        if(isset($exclude)){
          unset($v[$exclude]);
        }
        $newArray[$k] = $v;               
      }
    }
    return $newArray;
  }

  function get_sequence_from_user($id,$type){
    return $this->extract_sequence(get_user_meta($id,$type));
  }

  function get_saved_sequnce_from_user($id,$key=false){    
    $seqs =  $this->extract_sequence(get_user_meta($id,'saved-asanas'),'label');     
    if ($key) {       
      return $seqs[$key];
    }else{
      $last = end($seqs);     
      return $last;
    }     
  }



  function add_sequence($request){
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

  function get_sequence($request){
    $parameters = $request->get_params();
    $exclude = isset($parameters['exclude'])?$parameters['exclude']:'';
    $seqs = $this->extract_sequence(get_user_meta($parameters['userID'],$parameters['type']),$exclude);
    if (isset($parameters['key'])) {      
      return $seqs[$parameters['key']];     
    }
    else{      
      return $seqs;
    }    
  }


  function update_sequence($request){
    $params = $request->get_params();
    if (!isset($params['userID'])) {return new WP_Error( 'invalid_parameter', 'userID empty', array( 'status' => 404 ) );}
    if (!isset($params['type'])) {return new WP_Error( 'invalid_parameter', 'type empty', array( 'status' => 404 ) );}
    if (!isset($params['key'])) {return new WP_Error( 'invalid_parameter', 'key empty', array( 'status' => 404 ) );}
    if (!isset($params['func'])) {return new WP_Error( 'invalid_parameter', 'func empty', array( 'status' => 404 ) );}    
    $seqs =  $this->extract_sequence(get_user_meta($params['userID'],$this->types[$params['type']]));    
    if ($params['func'] == 'update_label') {
      $seqs[$params['key']]['label'] = $params['label'];      
      update_user_meta($params['userID'],$this->types[$params['type']],$seqs);
      return 'updated';
    }
  }


  function delete_sequence($request){
    $parameters = $request->get_params();
    if (isset($parameters['key'])) {
      $tmp = get_user_meta($parameters['userID'],$parameters['type']);
      delete_user_meta($parameters['userID'],$this->types[$parameters['type']]);      
      foreach ($tmp as $key => $value) {
        foreach ($value as $k => $v) {
          if (!empty($k)&& !empty($v) && $k != $parameters['key']) {
            add_user_meta($parameters['userID'],$this->types[$parameters['type']],array($k => $v));
          }
        }
      }      
      return $updated;
    }
    else{
      delete_user_meta($parameters['userID'],$this->types[$parameters['type']]);
    }
  }

  function get_asana_by_id($data){
    return $data['id'];
  }

  public static function get_asanas() {
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
          'preparings' =>$vorbereitende_,
          'followings' => $anschliessende_,
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
    }
    else{
      return false; 
    }
  } 
}

$asana_finder = new Asana_Finder();

