///////////////////add new top 3 products purchased shortcode///////////////////////
 
add_shortcode( 'my_top_products', 'user_products_bought' );
 
function user_products_bought() {
 
    global $product, $woocommerce, $woocommerce_loop;
    $columns = 3;
 
    // GET USER
    $current_user = wp_get_current_user();
 
    // GET USER ORDERS for last 3 products (COMPLETED + PROCESSING) 
    $customer_orders = get_posts( array(
        'numberposts' => -1,
        'meta_key'    => '_customer_user',
        'meta_value'  => $current_user->ID,
        'post_type'   => wc_get_order_types(),
        'post_status' => array_keys( wc_get_order_statuses() ),
    ) );
 
    // LOOP THROUGH ORDERS AND GET PRODUCT IDS
    if ( ! $customer_orders ) return;
    $product_ids = array();
    foreach ( $customer_orders as $customer_order ) {
        $order = new WC_Order( $customer_order->ID );
        $items = $order->get_items();
        foreach ( $items as $item ) {
            $product_id = $item->get_product_id();
            $product_ids[] = $product_id;
        }
    }
    $values = array_count_values($product_ids);
    arsort($values);
    $popular = array_slice(array_keys($values), 0, 3, true);
    // QUERY PRODUCTS
    $args = array(
       'post_type' => 'product',
       'post__in' => $popular,
    );
    $loop = new WP_Query( $args );
 
    // GENERATE WC LOOP
    ob_start();
    woocommerce_product_loop_start();
    while ( $loop->have_posts() ) : $loop->the_post();
    wc_get_template_part( 'content', 'product' ); 
    endwhile; 
    woocommerce_product_loop_end();
    woocommerce_reset_loop();
    wp_reset_postdata();
 
    // RETURN CONTENT
    return '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
 
}


///////////////////add new my account page endpoint///////////////////////

 
// ------------------
// 1. Register new endpoint to use for My Account page
// Note: Resave Permalinks or it will give 404 error
 
function add_recent_products_endpoint() {
    add_rewrite_endpoint( 'recent-products', EP_ROOT | EP_PAGES );
}
 
add_action( 'init', 'add_recent_products_endpoint' );
 
 
// ------------------
// 2. Add new query var
 
function recent_products_query_vars( $vars ) {
    $vars[] = 'recent-products';
    return $vars;
}
 
add_filter( 'query_vars', 'recent_products_query_vars', 0 );
 
 
// ------------------
// 3. Insert the new endpoint into the My Account menu
 
function add_recent_products_link_my_account( $items ) {
    $items['recent-products'] = 'My Products';
    return $items;
}
 
add_filter( 'woocommerce_account_menu_items', 'add_recent_products_link_my_account' );

 
// ------------------
// 4. Add content to the new endpoint
 
function recent_products_content() {
echo '<h3 class = "ordered-products-header-my-account">Most Ordered Products</h3>';
echo do_shortcode( '[my_top_products]' );
}
 
add_action( 'woocommerce_account_recent-products_endpoint', 'recent_products_content' );
// Note: add_action must follow 'woocommerce_account_{your-endpoint-slug}_endpoint' format

