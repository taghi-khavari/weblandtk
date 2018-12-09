<?php
//First we need to add a custom meta box

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
