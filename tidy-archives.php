<?php
/*
Plugin Name: Tidy Archives
Version: 1.0
Plugin URI: http://www.jeangalea.com/wordpress/tidy-archives/
Description: A practical and tidy way to present your archives.
Author: Jean Galea
Author URI: http://www.jeangalea.com/

GPL LICENCE
Copyright 2011  JEAN GALEA  (email : info@jeangalea.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/

register_activation_hook(__FILE__, 'ta_install');

function ta_install() {
    global $wp_version; 
    if ( version_compare( $wp_version, "2.9", "<" ) ) { 
        deactivate_plugins( basename(__FILE__) ); // Deactivate our plugin
        wp_die( "This plugin requires WordPress version 2.9 or higher." );
    }
}

function tidy_archives() {
    global $wpdb;
    
    // Get all months in current year which contain posts AND the posts from each of these months
    $current_year_posts = $wpdb->get_results("
    SELECT distinct monthname(post_date) AS monthname, month(post_date) AS month, post_title, post_date, id
        FROM wp_posts
        WHERE post_type = 'post' 
        AND post_status = 'publish'
        AND year(post_date) = year(CURDATE()) 
        ORDER BY post_date DESC
    ");
    
    $curr_month = 0;
    echo '<ul>';
    foreach( $current_year_posts as $cyp ) {               
        if ( $cyp->month <> $curr_month ) {            
            if ($curr_month <> 0 && $cyp->month < $curr_month ) echo '</ul>';
            echo '<li><a href="' . get_month_link( '', $cyp->month ) . '">' . $cyp->monthname . date(' Y') . '</a></li>';                
            echo '<ul><li><a href="'. get_permalink( $cyp->id ) . '">' . $cyp->post_title . '</a></li>';            
            $curr_month = $cyp->month;
        }
        else { echo '<li><a href="'. get_permalink( $cyp->id ) . '">' . $cyp->post_title . ' ('. $cyp->post_date . ')</a></li>'; }        
    }
    echo '</ul>'; echo '</ul>';    

    // Get all years which contain posts except current year    
    $previous_years = $wpdb->get_results("
    SELECT distinct year(post_date) AS 'year', count(ID) as posts
        FROM $wpdb->posts
        WHERE post_type = 'post' 
        AND post_status = 'publish'
        AND year(post_date) < year(CURDATE())
        GROUP BY year(post_date) 
        ORDER BY post_date DESC
    ");
        
    echo '<ul>';
    foreach( $previous_years as $previous_year ) { ?>
        <li><a href="<?php echo get_year_link( $previous_year->year ); ?>"><?php echo $previous_year->year; ?></a></li>        
    <?php }
    echo '</ul>';
   
}
?>