<?php
//Inherit parent styles
add_action( 'wp_enqueue_scripts', 'my_parent_styles' );
function my_parent_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
}

//Add another size to the menu image
add_filter( 'menu_image_default_sizes', function($sizes){

  // remove the default 36x36 size
  unset($sizes['menu-36x36']);

  // add a new size
  $sizes['menu-160x160'] = array(160,160);

  // return $sizes (required)
  return $sizes;

});

?>
