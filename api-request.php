<?php
// File: api-request.php

// Include necessary dependencies
// Example: include 'functions.php';

function getCompanyName()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'manatal_career_page';
    $company_name = $wpdb->get_var("SELECT company_name FROM $table_name");
    return $company_name;
}


function fetch_job_listing()
{
    $company_name = getCompanyName();
    // Make API request
    $response = wp_remote_get("https://api.manatal.com/open/v3/career-page/$company_name/jobs/");

    // Check for errors
    if (is_wp_error($response)) {
        // Handle error case
        $error_message = $response->get_error_message();
        echo "API request failed: $error_message";
    } else {
        // Process the response
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);

        // Display the job listings in a table
        echo '<table class="data-table wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>ID</th><th>Position</th><th>Country</th></tr></thead>';
        echo '<tbody>';

        foreach ($data->results as $job) {
            echo '<tr>';
            echo '<td>' . $job->id . '</td>';
            echo '<td>' . $job->position_name . '</td>';
            echo '<td>' . $job->country . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    }
}

function enqueue_plugin_assets()
{
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
    wp_enqueue_style( 'bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css' );
}


add_action('wp_enqueue_scripts', 'enqueue_plugin_assets');


function job_listing_shortcode()
{

    $output = '';

    $company_name = getCompanyName();
    // Make API request
    $response = wp_remote_get("https://api.manatal.com/open/v3/career-page/$company_name/jobs/");

    // Check for errors
    if (is_wp_error($response)) {
        // Handle error case
        $error_message = $response->get_error_message();
        $output = "API request failed: $error_message";
    } else {
        // Process the response
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);

        $output .= '<div class="my-own-namespace">'; // Add a unique class or ID for your shortcode
        foreach ($data->results as $job) {

            $applyURL = "https://www.careers-page.com/$company_name/job/$job->hash";

            $output .= '<div class="card mb-3">';
            $output .= '<div class="card-body">';
            $output .= '<h5 class="card-title">' . $job->position_name . '</h5>';
            $output .= ' <p class="card-text"> <i class="bi bi-geo-alt-fill">' . $job->location_display . '</p></i>';
            $output .= '<a href="' . $applyURL . '" class="btn btn-primary">Apply</a>';
            $output .= '</div>';
            $output .= '</div>';
        }
        $output .= '</div>';
    }

    return $output;
}
