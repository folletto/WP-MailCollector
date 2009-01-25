<?php 
/*
 * WordPress Portal
 * WP Theming Functions Library
 * 
 * Version 0.5
 * Last revision: 2007 01 21
 *
 * by Davide 'Folletto' Casali
 * www.digitalhymn.com
 * Copyright (C) 2006 - Creative Commons (CC) by-sa 2.5
 * 
 * Based upon a library developed for key-one.it (Kallideas / Key-One)
 *
 */

/*
 * SUMMARY:
 *  wpp_foreach_post($filter, $limit): creates a "custom The Loop", with a filter match
 *  wpp_get_posts($filter, $limit): gets all the posts matching a filter
 *  wpp_uri_category($field, $default): gets the category of the loaded page
 *  wpp_in_category($nicename): [TheLoop] checks if the posts belongs to that category
 *  wpp_is_parent_category($parent, $child): checks if the category is parent of another (nicename)
 *  wpp_get_post_custom($custom, $before, $after, $optid): [TheLoop] gets the specified custom
 *  wpp_get_page_content($nicename): gets the specified page content
 *  wpp_is_admin($userid): check if the current logged user is an "administrator"
 *  wpp_get_last_comments($size): gets all the last comments
 *  wpp_get_last_comments_grouped($size): gets the last comments, one comment per post
 * 
 * DETAILS:
 * The most interesting function is the wpp_foreach_post() that in fact creates a custom
 * "The Loop", using the syntax:
 *          while($post = wpp_foreach_post($filter, $limit)) { ... }
 * 
 * The function wpp_uri_category() retrieves the correct category from the page currently loaded.
 *  If the uri opens a category, it returns the nicename of the category.
 *  If the uri opens a page, it returns the page nicename.
 *  If the uri opens a post, it returns the post category.
 * This is *really* useful to create complex sites, using the page hierarchy as structure.
 * 
 */

if (!function_exists('wpp_foreach_post')) {
	/****************************************************************************************************
	 * Creates a custom the_loop, the_wppthe_wpp_loop_loop.
	 * The query filter parameter in array mode filters additional special queries:
	 * - 'category' => 'name', selects all the posts from a specific category using its nicename
	 * - 'page' => 'name', retrieves the page defined by its nicename
	 *
	 * @param			filter string (SQL WHERE) or array (converted to SQL WHERE, AND of equals (==))
	 * @param			limit string (i.e. 1 or 1,10)
	 * @return		single post array
	 */
	function wpp_foreach_post($filter, $limit = null) {
		global $wpdb;
		global $__wpp_posts;						// working variables for the_wpp_loop
	
		global $__wpp_old_posts;				// backup: possible existing $post
		global $__wpp_old_previousday;	// backup: possible existing $previousday
	
		global $post, $id, $day;				// TheLoop emu: content and working functions
		global $day, $previousday;			// TheLoop emu: date functions

		// ****** Init
		$out = null;
		$where = array();
		$join = '';

		if (!isset($__wpp_posts) || $__wpp_posts === null) {
			// *** Backup
			$__wpp_old_posts = $post;
			$__wpp_old_previousday = $previousday;
		
			// ****** Building SQL where clause. 
			if (is_array($filter)) {
				foreach ($filter as $key => $value) {
					if ($key == 'category') {
						// *** Special: category by nicename
						$catwhere = array();
						$categories = wpp_get_categories_children_flat_array($value);
						foreach ($categories as $category) {
							$catwhere[] = "p2c.category_id = '" . $category->cat_ID . "'";
						}
						if (sizeof($catwhere))
							$where[] = '(' . join(' OR ', $catwhere) . ')';
					} else if ($key == 'page') {
						// *** Special: page by nicename
						$where[] = "post_name = '" . $value . "'";
					} else {
						// ***  Normal where condition
						$where[] = "" . $key . " = '" . $value . "'";
					}
				}
				$where = join(' AND ', $where);
			} else {
				$where = $filter;
			}
		
			// ****** Querying
			$query = "
				SELECT DISTINCT p.*
				FROM " . $wpdb->posts . " As p
				INNER JOIN " . $wpdb->post2cat . " As p2c ON p.ID = p2c.post_id
					" . ($join ? $join : '') . "
				WHERE
					" . ((is_array($filter) && isset($filter['page'])) ? "post_status = 'static'" : "post_status = 'publish'" ) . "
					" . ($where ? "AND " . $where : '') . "
				ORDER BY post_date DESC
					" . ($limit ? 'LIMIT ' . $limit : '') . "
				";

			if ($__wpp_posts = $wpdb->get_results($query)) {
				// *** First
				$post = array_shift($__wpp_posts);
				$id = $post->ID;
				$day = mysql2date('d.m.y', $post->post_date);
		
				$out = $post;
			}
		} else {
			// ****** We're in the_wpp_loop
			if (is_array($__wpp_posts) && sizeof($__wpp_posts) > 0) {
				// *** Next
				$post = array_shift($__wpp_posts);
				$id = $post->ID;
				$day = mysql2date('d.m.y', $post->post_date);
			
				$out = $post;
			} else {
				// *** Reset
				$post = null;
				$__wpp_posts = null;
			
				// *** Restore backup
				$post = $__wpp_old_posts;
				$id = $post->ID;
				$day = mysql2date('d.m.y', @$post->post_date);
				$previousday = $__wpp_old_previousday;
			}
		}

		return $out;
	}

	/****************************************************************************************************
	 * Gets all the posts into an array. Wraps k_foreach_post().
	 *
	 * @param			filter string (SQL WHERE) or array (converted to SQL WHERE, AND of equals (==))
	 * @param			limit string (i.e. 1 or 1,10)
	 * @return		posts array
	 */
	function wpp_get_posts($filter, $limit = null) {
		$posts = array();

		while ($post = wpp_foreach_post($filter, $limit)) {
			$posts[] = $post;
		}

		return $posts;
	}

	/****************************************************************************************************
	 * Get the category matching the nicename and all its children in a flat array.
	 *
	 * @param			category nicename
	 * @param			(optional) depth of recursion (defult: -1, ALL)
	 * @return		single post array
	 */
	function wpp_get_categories_children_flat_array($category_ref, $levels = -1) {
		global $wpdb;

		$out = array();

		if ($category_ref !== '') {
			// ****** Conditional WHERE
			if (strval($category_ref) === strval(intval($category_ref))) {
				$where = "WHERE c.category_parent = '" . $category_ref . "'";	
			
				if ($levels != -1) {
					if ($levels > 0) $levels--;
				}
			} else {
				$where = "WHERE c.category_nicename = '" . $category_ref . "'";
			}
		
			// ****** Querying
			$query = "
				SELECT *
				FROM " . $wpdb->categories . " As c
				" . $where . "
				";
		
			if ($categories = $wpdb->get_results($query))
			{
				// ***Loop
				foreach ($categories as $category) {
					$out[] = $category;
				
					if ($levels == -1 || $levels > 0) {
						$out = array_merge($out, wpp_get_categories_children_flat_array($category->cat_ID, $levels));
					}
				}
			}
		}
	
		return $out;
	}

	/****************************************************************************************************
	 * Retrieve the current URI related category.
	 * If the URI asks for a "post", the category will be the category's post.
	 * If the URI asks for a "page", the category will be the page's nicename.
	 * If the URI asks for a "category", the category will be... that one.
	 *
	 * @param			category field (def: 'nicename') [nicename, name, ID, description]
	 * @return		category value
	 */
	function wpp_uri_category($field = 'nicename', $default = false) {
		global $wpdb;
		global $_kcategory;

		// ****** Init
		$query = '';
		$out = $default;

		if (!isset($_kcategory) || !$_kcategory) {
			// ****** Parsing URL
			$type = wpp_uri_type();
			if ($type['type'] == 'page') {
				// *** We're in a PAGE
				$query = "
					SELECT c.*
					FROM " . $wpdb->posts . " As p
					INNER JOIN " . $wpdb->categories . " As c ON p.post_name = c.category_nicename
					WHERE
					p.post_status = 'static' AND
					p.ID = '" . $type['id'] . "'
					LIMIT 1
					";
			} else if ($type['type'] == 'post') {
				// *** We're in a POST
				$query = "
					SELECT c.*
					FROM " . $wpdb->posts . " As p
					INNER JOIN " . $wpdb->post2cat . " As p2c ON p.ID = p2c.post_id
					INNER JOIN " . $wpdb->categories . " As c ON c.cat_ID = p2c.category_id
					WHERE
					p.post_status = 'publish' AND
					p.ID = '" . $type['id'] . "'
					LIMIT 1
					";
			} else if ($type['type'] == 'cat') {
				// *** We're in a CATEGORY
				$query = "
					SELECT c.*
					FROM " . $wpdb->categories . " As c
					WHERE
					c.cat_ID = '" . $type['id'] . "'
					LIMIT 1
					";
			}
		
			// ****** Retrieving category
			if ($categories = $wpdb->get_results($query)) {
				// *** Exists
				$_kcategory = $categories[0];
			}
		}

		// ****** Now $_kcategory should be set...
		if (isset($_kcategory) || is_array($_kcategory)) {
			if ($field == 'id') $field = 'ID';
		
			// Handling silly implementation of WP table...
			if ($field == 'ID' || $field == 'name')
				$out = $_kcategory->{'cat_' . $field};
			else
				$out = $_kcategory->{'category_' . $field};
		}
	
		return $out;
	}

	/****************************************************************************************************
	 * Checks if the post in the_loop belongs to the specified category nicename.
	 * Different from in_category(), that checks for the id, not for the nicename.
	 *
	 * @param		nicename string
	 * @return	boolean
	 */
	function wpp_in_category($nicename) {
		$out = false;
		$category = get_the_category();

		foreach ($category as $cat) {
			if ($nicename == $cat->category_nicename)
				$out = true;
		}

		return $out;
	}

	/****************************************************************************************************
	 * Checks if a category is parent (or the same) of another.
	 *
	 * @param		parent category
	 * @param		child category
	 * @return	boolean true
	 */
	function wpp_is_parent_category($parent_nicename, $child_nicename) {
		$categories = wpp_get_categories_children_flat_array($parent_nicename);

		foreach ($categories as $category) {
			if ($category->category_nicename == $child_nicename)
				return true;
		}
	
		return false;
	}

	/****************************************************************************************************
	 * Get a specific custom item, optionally wrapped between two text strings.
	 * Works inside The Loop only. To be used used outside specify the optional id parameter.
	 *
	 * @param			custom field
	 * @param			before html
	 * @param			after html
	 * @param			optional id (to fetch the custom of a different post)
	 * @return		html output
	 */
	function wpp_get_post_custom($custom, $before = '', $after = '', $optid = 0) {
		global $id;

		$out = '';
		if ($id && !$optid) $optid = $id;

		$custom_fields = get_post_custom($optid);

		if (isset($custom_fields[$custom])) {
			$out = $before . $custom_fields[$custom][0] . $after;
		}

		return $out;
	}

	/****************************************************************************************************
	 * Returns the specified page, given a nicename.
	 *
	 * @param			page nicename
	 * @return		page content
	 */
	function wpp_get_page_content($nicename) {
	  $out = '';

	  $posts = wpp_get_posts(array('page' => $nicename));
	  if ($posts[0]->post_content)
	    $out = $posts[0]->post_content;
	  else
	    $out = 'La pagina "' . $nicename . '" &egrave; da definirsi.';

	  return $out;
	}

	/****************************************************************************************************
	 * Return the type of the 'section' where we are and the matching id.
	 * - types: page, post
	 *
	 * @return		array ('type' => '...', 'id' => 'n')
	 */
	function wpp_uri_type() {
		$out = array(
			'type'  => 'none',
			'id'    => 0
			);

		if ($_GET['page_id']) {
			// *** We're in a PAGE
			$out = array(
				'type'  => 'page',
				'id'    => $_GET['page_id']
				);
		} else if ($_GET['p']) {
			// *** We're in a POST
			$out = array(
				'type'  => 'post',
				'id'    => $_GET['p']
				);
		} else if ($_GET['cat']) {
			// *** We're in a CATEGORY
			$out = array(
				'type'  => 'cat',
				'id'    => $_GET['cat']
				);
		} else if ($_GET['tag']) {
			// *** We're in a TAG
			$out = array(
				'type'  => 'tag',
				'id'    => $_GET['tag']
				);
		}

		return $out;
	}

	/****************************************************************************************************
	 * Checks if the specified user ID is an admin user
	 *
	 * @param		user id (0 for current logged user)
	 * @return	boolean
	 */
	function wpp_is_admin($uid = 0) {
	  global $wpdb, $current_user;
  
	  $out = false;
  
	  // ****** Get current logged user
	  if ($uid == 0 || strtolower($uid) == "me") {
	    if (isset($current_user) && isset($current_user->id) && $current_user->id > 0) {
	      $uid = $current_user->id;
	    }
	  }
  
	  // ****** Query check Admin
	  $query = "
			SELECT count(*) As isAdmin
			FROM " . $wpdb->usermeta . " As um
			WHERE
			  um.user_id = '" . $uid . "' AND
			  um.meta_key = 'wp_capabilities' AND
			  um.meta_value LIKE '%" . "\"administrator\"" . "%'
			LIMIT 1
			";
	
		// ****** Retrieving capabilities count
	  if ($users = $wpdb->get_results($query)) {
	  	// *** Exists
	  	if ($users[0]->isAdmin > 0) {
	  	  $out = true;
		  }
	  }
  
	  return $out;
	}

	/****************************************************************************************************
	 * Get comments list array.
	 *
	 * @param		number of comments to retrieve
	 * @param		optional post ID to relate comments
	 * @return	array
	 */
	function wpp_get_last_comments($size = 10, $id = 0) {
		global $wpdb;
		$out = array();
	
		$sqlPost = "";
		if ($id > 0) $sqlPost = "AND p.ID = '" . $id . "'";
	
		$comments = $wpdb->get_results("
			SELECT
				c.comment_ID, c.comment_author, c.comment_author_email,
				c.comment_date, c.comment_content, c.comment_post_ID,
				p.post_title, p.comment_count
			FROM " . $wpdb->comments . " as c
			INNER JOIN " . $wpdb->posts . " as p ON c.comment_post_ID = p.ID
			WHERE
				comment_approved = '1'
				" . $sqlPost . "
			ORDER BY comment_date_gmt DESC
			LIMIT 0," . $size);
	
		foreach ($comments as $comment) {
			$out[] = array(
				'id' => $comment->comment_ID,
				'author' => $comment->comment_author,
				'email' => $comment->comment_author_email,
				'md5' => md5($comment->comment_author_email),
				'date' => $comment->comment_date,
				'content' => $comment->comment_content,
				'post' => array(
					'id' => $comment->comment_post_ID,
					'title' => $comment->post_title,
					'comments' => $comment->comment_count
				)
			);
		}
	
		return $out;
	}

	/****************************************************************************************************
	 * Get comments list array.
	 * Requires MySQL 4.1+ (nested queries, but just two calls).
	 *
	 * @param		number of comments to retrieve
	 * @return	array
	 */
	function wpp_get_last_comments_grouped($size = 10) {
		global $wpdb;
		$out = array();
	
		$sqlPost = "";
		if ($id > 0) $sqlPost = "AND p.ID = '" . $id . "'";
	
		// ****** Get the ID of the Last Comment for Each Post (sorted by Comment Date DESC)
		$last = $wpdb->get_results("
			SELECT
				c.comment_ID, c.comment_post_ID
			FROM " . $wpdb->comments . " as c
			INNER JOIN
				(SELECT MAX(comment_ID) AS comment_ID FROM " . $wpdb->comments . " GROUP BY comment_post_ID) cg
				ON cg.comment_ID = c.comment_ID
			WHERE
				comment_approved = '1'
			ORDER BY comment_date_gmt DESC
			LIMIT 0," . $size);
	
		$where = '';
		foreach ($last as $comment) {
			if ($where) $where .= ' OR ';
			$where .= "comment_ID = '" . $comment->comment_ID . "'";
		}
		$where = '(' . $where . ')';
	
		// ****** Get the Last Comments details
		$comments = $wpdb->get_results("
			SELECT
				c.comment_ID, c.comment_author, c.comment_author_email,
				c.comment_date, c.comment_content, c.comment_post_ID,
				p.post_title, p.comment_count
			FROM " . $wpdb->comments . " as c
			INNER JOIN " . $wpdb->posts . " as p ON c.comment_post_ID = p.ID
			WHERE
				comment_approved = '1' AND
				" . $where . "
			ORDER BY comment_date_gmt DESC
			LIMIT 0," . $size);
	
		foreach ($comments as $comment) {
			$out[] = array(
				'id' => $comment->comment_ID,
				'author' => $comment->comment_author,
				'email' => $comment->comment_author_email,
				'md5' => md5($comment->comment_author_email),
				'date' => $comment->comment_date,
				'post' => array(
					'id' => $comment->comment_post_ID,
					'title' => $comment->post_title,
					'comments' => $comment->comment_count
				)
			);		
		}
	
		return $out;
	}
}
?>