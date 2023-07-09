<?php

include plugin_dir_path(__FILE__) . 'api-request.php';

/*
 * Plugin Name:       Manatal Career Page
 * Description:       Fetch manatal API for display career page and custom it
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      8.0
 * Author:            Iqbal
 * Author URI:        https://qeba.my
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

// Activation hooks
function manatal_career_activate()
{
    // Create the new table using the wpdb class
    global $wpdb;
    $table_name = $wpdb->prefix . 'manatal_career_page';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        company_name TEXT NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'manatal_career_activate');

// Deactivation hooks
function manatal_career_deactivate()
{
    // Delete the table created during activation
    global $wpdb;
    $table_name = $wpdb->prefix . 'manatal_career_page';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}
register_deactivation_hook(__FILE__, 'manatal_career_deactivate');

// load the css file 
function enqueue_custom_table_styles()
{
    wp_enqueue_style('data-table-style', plugin_dir_url(__FILE__) . 'css/table.css');
}
add_action('admin_enqueue_scripts', 'enqueue_custom_table_styles');


// Menu page
function manatal_career_menu_page()
{
    add_menu_page(
        'Manatal API Settings',
        'Manatal Config',
        'manage_options',
        'manatal-plugin-settings',
        'manatal_career_settings_page',
        'dashicons-admin-generic',
        50
    );
    add_submenu_page(
        'manatal-plugin-settings',
        'View Job Listing', // Sub-menu page title
        'View Job Listing', // Sub-menu label
        'manage_options',
        'manatal-plugin-job-listing', // Sub-menu slug
        'manatal_job_listing_page' // Callback function for the sub-menu
    );
}
add_action('admin_menu', 'manatal_career_menu_page');

// Settings page
function manatal_career_settings_page()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'manatal_career_page';

    // Handle form submission
    if (isset($_POST['submit'])) {
        // Get and sanitize the user input
        $company_name = sanitize_text_field($_POST['company_name']);
        // Save the input to the table

        $wpdb->insert($table_name, array(
            'company_name' => $company_name
        ));
    }


    // get the data from table
    $results = $wpdb->get_results("SELECT id, company_name FROM $table_name");

    //hide form if there already data added
    $data_exists = !empty($results);

    // Handle deletion
    if (isset($_POST['delete'])) {
        // Get the row ID to be deleted
        $delete_id = intval($_POST['delete']);
        $wpdb->delete($table_name, array('id' => $delete_id), array('%d'));
        echo '<script>location.reload();</script>';
    }
?>
    <!-- // Display the form -->
    <div class="wrap">
        <h1>Manatal API Settings</h1>
        <br></br>
        <form method="post" action="">
            <label for="company_name">Company Name:</label>
            <input type="text" name="company_name" id="company_name" value="<?php echo esc_attr(get_option('company_name')); ?>" required <?php echo $data_exists ? 'disabled' : ''; ?>>
            <br><br>
            </br>
            <input class="button button-primary" type="submit" name="submit" value="Save" <?php echo $data_exists ? 'disabled' : ''; ?>>
        </form>

        <br></br>
        <hr>

        <!-- // display the saved data. -->
        <h2>Company Name Added: </h2>
        <?php if (!empty($results)) { ?>
            <table class="data-table wp-list-table  striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Company Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($results as $row) {
                        echo '<tr>';
                        echo '<td>' . $row->id . '</td>';
                        echo '<td>' . $row->company_name . '</td>';
                        echo '<td>';
                        echo '<form method="post" action="">';
                        echo '<input type="hidden" name="delete" value="' . $row->id . '">';
                        echo '<button class="button center" type="submit" onclick="return confirm(\'Are you sure you want to delete this?\')">Delete</button>';
                        echo '</form>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>Please add company name first using above form.</p>
        <?php } ?>
    </div>
    <br></br>
    <hr>
<?php
}

// job listing page here
function manatal_job_listing_page()
{
    echo '<h1>Job Listing</h1>'; // Example content
    fetch_job_listing();
}

function call_job_listing_shortcode()
{
    $all_job = job_listing_shortcode();
    return $all_job;
}

// define shortcode here
add_shortcode('job_listing', 'call_job_listing_shortcode');

