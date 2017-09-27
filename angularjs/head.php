<?php

add_action( 'wp_enqueue_scripts', 'yj_asana_finder_style', 1001);
function yj_asana_finder_style() {       
  wp_enqueue_style('bootstrapCSS', "//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.min.css", '6.1c', 'all' );
  wp_enqueue_style('mainCss', plugin_dir_url( __FILE__ ) .'style.css', 'v1.8', 'all' );
  wp_enqueue_style('fontAwesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css', '3.2.1', 'all' );
}

$currentUser = wp_get_current_user();

if(is_user_logged_in()){
  $userData = array(
    'loggedIn' => true,
    'ID'=> $currentUser->ID,
    'displayName'=>$currentUser->display_name,
    );
}else{
  $userData = array(
    'loggedIn' => false,
    'ID'=> 99999,
    'displayName'=> 'gast',
    );
}
?>
<script type="text/javascript">
	var appSettings = {};
  appSettings.pluginDirUrl = "<?php echo plugin_dir_url('');?>asana-finder/"; 
  appSettings.pluginDirPath = "<?php echo plugin_dir_path('');?>asana-finder/"; 
	appSettings.root = "<?php echo esc_url_raw( rest_url());?>";
	appSettings.nonce = "<?php echo wp_create_nonce( 'wp_rest' ); ?>";  
  appSettings.currentUser = <?php echo json_encode($userData); ?>;
  appSettings.asanas = <?php echo json_encode($asana_finder->get_asanas()); ?>;
  appSettings.savedSequences = <?php echo json_encode($asana_finder->get_saved_sequnce_from_user($userData['ID'])); ?>;
  appSettings.homeURL = "<?php echo home_url(); ?>";
</script>
<?php
/*$se = get_saved_sequnce_from_user(1);
echo 'in array:'. array_key_exists('label',$se);
echo '<pre>';
print_r($se);
echo '</pre>';*/

#print_r(update_user_sequence_test(array('userID'=>1,'type'=>'saved-asanas','key'=>'1490110773916','label'=>'newlabel','func'=>'update_label')));

// Register and enqueue Javascript files
function yj_asana_finder_scripts() {
  if ( !is_admin() ) {            
    wp_enqueue_script( 'angularJS', "https://ajax.googleapis.com/ajax/libs/angularjs/1.6.1/angular.min.js", '', true );
    #wp_enqueue_script( 'longPress',  plugin_dir_url( __FILE__ ) .'longpress.js',array('angularJS'), '', true );
    wp_enqueue_script( 'angularJSMain',  plugin_dir_url( __FILE__ ) .'main.js', array('angularJS'), '', true );
    wp_enqueue_script( 'angularDD',  plugin_dir_url( __FILE__ ) .'angularDAD.js',array('angularJSMain'), '', true );
    wp_enqueue_script( 'boostrap', "//angular-ui.github.io/bootstrap/ui-bootstrap-tpls-0.3.0.min.js",array('angularJS'),  '', true );
    
    if(wp_is_mobile()){
      wp_enqueue_script( 'mobileDad',  plugin_dir_url( __FILE__ ) .'mobileDaD.js',array('angularJS'), '', true );
    }    
  }
}

add_action( 'wp_enqueue_scripts', 'yj_asana_finder_scripts' );
get_header();

//set the template id, used to get the template specific settings
$template_id = 'page';
$local = site_url();

?>