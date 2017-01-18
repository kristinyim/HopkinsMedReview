add_action( 'wp_enqueue_scripts', 'my_parent_styles' );
function my_parent_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
}