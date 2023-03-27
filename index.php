<?php
/* users login status check custom functions */
function update_user_activity( $user_id ) {
global $wpdb;
$table_name = $wpdb->prefix . 'user_activity';
$wpdb->replace( $table_name, array(
'user_id' => $user_id,
'last_activity' => current_time( 'mysql' )
), array( '%d', '%s' ) );
}
function is_user_online( $user_id ) {
global $wpdb;
$table_name = $wpdb->prefix . 'user_activity';
$online_timeout = apply_filters( 'user_online_timeout', 5 * 60 ); // 5 minutes
$last_activity = $wpdb->get_var( $wpdb->prepare( "SELECT last_activity FROM $table_name WHERE user_id = %d", $user_id ) );
if ( $last_activity && ( time() - strtotime( $last_activity ) ) < $online_timeout ) {
return true;
}
return false;
}
// Add "Online Status" column to user table
function custom_users_columns( $columns ) {
$columns['online_status'] = 'Online Status';
return $columns;
}
add_filter( 'manage_users_columns', 'custom_users_columns' );
// Show user's online status in "Online Status" column
function custom_users_column_data( $value, $column_name, $user_id ) {
if ( 'online_status' == $column_name ) {
global $wpdb;
$table_name = $wpdb->prefix . 'user_activity';
$last_activity = $wpdb->get_var( $wpdb->prepare( "SELECT last_activity FROM $table_name WHERE user_id = %d", $user_id ) );
$online_timeout = apply_filters( 'user_online_timeout', 2 * 60 ); // 15 minutes
if ( $last_activity && ( current_time( 'timestamp' ) - strtotime( $last_activity ) ) < $online_timeout ) {
$value = '<span style="color: green;">Online</span>';
} else {
$value = '<span style="color: red;">Offline</span>';
}
}
return $value;
}
add_filter( 'manage_users_custom_column', 'custom_users_column_data', 10, 3 );
// Save user's last activity timestamp
function save_custom_user_meta_fields( $user_id ) {
if ( current_user_can( 'edit_user', $user_id ) ) {
global $wpdb;
$table_name = $wpdb->prefix . 'user_activity';
$wpdb->replace( $table_name, array(
'user_id' => $user_id,
'last_activity' => current_time( 'mysql' )
), array( '%d', '%s' ) );
}
}
add_action( 'wp_login', 'save_custom_user_meta_fields' );
add_action( 'admin_init', 'save_custom_user_meta_fields' );
update_user_activity( get_current_user_id());
