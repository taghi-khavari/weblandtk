<?php

/*
 * Plugin Name: Adding Special products
 * Description: This is a simple plugin to separate some of product with a custom meta that can be distinguished from other products
 * Author: Taghi Khavari
 * Author Uri: https://ariazdevs.com
 * License: GPL2
 */

//Adding a Custom meta box to the product page

function save_your_fields_meta( $post_id ) {
    // verify nonce
    if ( isset($_POST['your_meta_box_nonce'])
        && !wp_verify_nonce( $_POST['your_meta_box_nonce'], basename(__FILE__) ) ) {
        return $post_id;
    }
    // check autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
    }
    // check permissions
    if (isset($_POST['product'])) { //Fix 2
        if ( 'page' === $_POST['product'] ) {
            if ( !current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            } elseif ( !current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }
    }

    $old = get_post_meta( $post_id, 'Custom_checkbox', true );
    if (true) { //Fix 3
        $new = $_POST['Custom_checkbox'];
        if ( $new !== $old ) {
            update_post_meta( $post_id, 'Custom_checkbox', $new );
        } elseif ( '' === $new && $old ) {
            delete_post_meta( $post_id, 'Custom_checkbox', $old );
        }
    }
}
add_action( 'save_post', 'save_your_fields_meta' );



add_action( 'add_meta_boxes', 'add_your_fields_meta_box' );
function add_your_fields_meta_box() {
    add_meta_box(
        'ariaz_dev_fields_meta_box', // $id
        'محصول ويژه', // $title
        'show_your_fields_meta_box', // $callback
        'product', // $screen
        'side', // $context
        'high' // $priority
    );
}


function show_your_fields_meta_box() {
    global $post;

    $meta = get_post_meta( $post->ID, 'Custom_checkbox', true );
    ?>

    <input type="hidden" name="your_meta_box_nonce" value="<?php echo wp_create_nonce( basename(__FILE__) ); ?>">

    <p>
        <label for="Custom_checkbox">فعال کردن
            <input type="checkbox" name="Custom_checkbox"
            <?php
            if(isset($meta)){
                if ( $meta === 'on' ) {
                    echo 'checked';
                }else echo ''; } ?>>
        </label>
    </p>

<?php }










//Adding new Column to the product admin page

add_filter( 'manage_product_posts_columns', 'weblandtk_filter_posts_columns' );
function weblandtk_filter_posts_columns( $columns ) {
    $columns['wtcp'] = __( 'محصول ویژه' );
    return $columns;
}


add_action('manage_product_posts_custom_column','weblandtk_realstate_column',10,2);
function weblandtk_realstate_column($column,$post_id){
        if ( 'wtcp' === $column ) {
        $custom_product = get_post_meta( $post_id, 'Custom_checkbox', true );

        if ( $custom_product == 'on') {
            _e( 'ویژه‍' );
        } else {
            echo '';
        }
    }
}



//Bulk Action for adding custom product

add_filter( 'bulk_actions-edit-product', 'register_my_bulk_actions' );

function register_my_bulk_actions($bulk_actions) {
    $bulk_actions['wtcp_custom_product'] = __( 'افزودن محصول ویژه');
    return $bulk_actions;
}



add_filter( 'handle_bulk_actions-edit-product', 'my_bulk_action_handler', 10, 3 );

function my_bulk_action_handler( $redirect_to, $doaction, $post_ids ) {
    if ( $doaction !== 'wtcp_custom_product' ) {
        return $redirect_to;
    }
    foreach ( $post_ids as $post_id ) {
        // Perform action for each post.
        update_post_meta( $post_id, 'Custom_checkbox', 'on' );

    }
    $redirect_to = add_query_arg( 'bulk_custom_product', count( $post_ids ), $redirect_to );
    return $redirect_to;
}

add_action( 'admin_notices', 'my_bulk_action_admin_notice' );

function my_bulk_action_admin_notice() {
    if ( ! empty( $_REQUEST['bulk_custom_product'] ) ) {
        $product_count = intval( $_REQUEST['bulk_custom_product'] );
        printf( '<div id="message" class="updated fade">' .
            _n( ' %s product was changed to custom product.',
                ' %s products was changed to custom product..',
                $product_count,
                'bulk_custom_product'
            ) . '</div>', $product_count );
    }
}







//bulk action to remove custom product


add_filter( 'bulk_actions-edit-product', 'register_new_bulk_actions' );

function register_new_bulk_actions($bulk_actions) {
    $bulk_actions['wtcp_removing_custom_product'] = __( 'حذف محصول ویژه');
    return $bulk_actions;
}



add_filter( 'handle_bulk_actions-edit-product', 'bulk_action_handler_for_removing_custom_product', 10, 3 );

function bulk_action_handler_for_removing_custom_product( $redirect_to, $doaction, $post_ids ) {
    if ( $doaction !== 'wtcp_removing_custom_product' ) {
        return $redirect_to;
    }
    foreach ( $post_ids as $post_id ) {
        // Perform action for each post.
        update_post_meta( $post_id, 'Custom_checkbox', '' );

    }
    $redirect_to = add_query_arg( 'bulk_custom_remove_product', count( $post_ids ), $redirect_to );
    return $redirect_to;
}

add_action( 'admin_notices', 'bulk_action_admin_notice_for_removing_custom_product' );

function bulk_action_admin_notice_for_removing_custom_product() {
    if ( ! empty( $_REQUEST['bulk_custom_remove_product'] ) ) {
        $product_count = intval( $_REQUEST['bulk_custom_remove_product'] );
        printf( '<div id="message" class="updated fade">' .
            _n( ' %s product was changed to custom product.',
                ' %s products was changed to custom product..',
                $product_count,
                'bulk_custom_remove_product'
            ) . '</div>', $product_count );
    }
}


//Registering a new role for accessing to the special products price

// ** Adding a role **
function weblandtk_simple_role()
{
//add_role( string $role, string $display_name, array $capabilities = array() )
    add_role(
        'simple_role',
        'Simple Role',
        [
            'read'         => true,
        ]
    );
}

// add the simple_role
add_action('init', 'weblandtk_simple_role');





remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );


//***************** This section is about changing front end of special products**************


add_action( 'woocommerce_after_shop_loop_item_title', 'weblandtk_woocommerce_template_loop_price', 10 );


if ( ! function_exists( 'weblandtk_woocommerce_template_loop_price' ) ) {

    function weblandtk_woocommerce_template_loop_price()
    {
        global $post;
        $custom_product = get_post_meta( $post->ID, 'Custom_checkbox', true );
        if($custom_product==='on'){

            if ((weblandtk_get_current_user_role() === 'administrator')||(weblandtk_get_current_user_role() === 'simple_role')) {
                /**
                 * Get the product price for the loop.
                 */
                wc_get_template('loop/price.php');

            }else{
                echo 'برای اطلاع از قیمت تماس بگیرید';
            }
        }else{
            wc_get_template('loop/price.php');
        }
    }
}

function weblandtk_get_current_user_role() {
    if( is_user_logged_in() ) {
        $user = wp_get_current_user();
        $role = ( array ) $user->roles;
        return $role[0];
    } else {
        return false;
    }
}

add_action('init','weblandtk_get_current_user_role');
