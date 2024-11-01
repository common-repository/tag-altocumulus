<?php
/*
Plugin Name: Altocumulus
Plugin URI: http://www.jasonmorrison.net/content/tag-altocumulus-wordpress-plugin/
Description: Places clouds of related tags on your Category pages.
Version: 0.2
Author: Jason Morrison
Author URI: http://www.jasonmorrison.net
*/

/*  Copyright 2008  Jason Morrison  (email : PLUGIN AUTHOR EMAIL)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


//add an action so it will run before the loop. 
add_action ( 'loop_start', 'tag_altocumulus');

function display_tag_altocumulus() {
  
   // stub function - in case they've already got it in their template 
   //TODO:  support either template use or auto

}

function tag_altocumulus() {

  $tag_array['term_id'] = '';

  if (is_category()) {

    $tag_array = get_term_by('name',  single_cat_title('', false), 'category', 'ARRAY_A');

  } elseif (is_tag()) {

    $tag_array = get_term_by('name',  single_tag_title('', false), 'post_tag', 'ARRAY_A');

  }  

  if ($tag_array['term_id']) {
  	global $wpdb;
  	
  	$sql = 
    "SELECT
      	rel_terms.term_id AS related_term_id,
      	COUNT(wp_posts.ID) as post_count
    FROM
      	wp_terms
      	INNER JOIN wp_term_taxonomy ON wp_terms.term_id = wp_term_taxonomy.term_id
      	INNER JOIN wp_term_relationships ON wp_term_taxonomy.term_taxonomy_id = wp_term_relationships.term_taxonomy_id
      	INNER JOIN wp_posts ON wp_term_relationships.object_id = wp_posts.ID
      	INNER JOIN wp_term_relationships AS rel_relationships ON wp_posts.ID = rel_relationships.object_id
        INNER JOIN wp_term_taxonomy AS rel_taxonomy ON rel_relationships.term_taxonomy_id = rel_taxonomy.term_taxonomy_id
        INNER JOIN wp_terms AS rel_terms ON rel_taxonomy.term_id = rel_terms.term_id
    WHERE
      	wp_terms.term_id = '". $tag_array['term_id'] ."'"
    ."    AND wp_term_taxonomy.taxonomy = \"post_tag\"
        AND rel_terms.term_id <> wp_terms.term_id
    GROUP BY rel_relationships.term_taxonomy_id
    ORDER BY post_count DESC
    LIMIT 20";

    $results = $wpdb->get_col( $sql );
    
    wp_tag_cloud('include='.implode(',',$results)); 
  
  }
}

?>