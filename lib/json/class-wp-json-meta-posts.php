<?php

class WP_JSON_Meta_Posts extends WP_JSON_Meta {
	/**
	 * Base route name.
	 *
	 * @var string Route base (e.g. /my-plugin/my-type/(?P<id>\d+)/meta). Must include ID selector.
	 */
	protected $route = 'meta';

	/**
	 * Associated object type.
	 *
	 * @var string Type slug ("post" or "user")
	 */
	protected $type = 'post';

	/**
	 * Check that the object can be accessed.
	 *
	 * @param mixed $id Object ID
	 * @return boolean|WP_Error
	 */
	protected function check_object( $id ) {
		$id = (int) $id;

		$post = get_post( $id, ARRAY_A );

		if ( empty( $id ) || empty( $post['ID'] ) ) {
            wpapper_json_error(BigAppErr::$post['code'],BigAppErr::$post['msg'],"empty $id");
		}

		if ( ! wpapper_json_check_post_permission( $post, 'edit' ) ) {
            wpapper_json_error(BigAppErr::$post['code'],BigAppErr::$post['msg'],"cant read:$id");
		}

		return true;
	}

	/**
	 * Add meta to a post.
	 *
	 * Ensures that the correct location header is sent with the response.
	 *
	 * @param int $id Post ID
	 * @param array $data {
	 *     @type string|null $key Meta key
	 *     @type string|null $key Meta value
	 * }
	 * @return bool|WP_Error
	 */
	public function add_meta( $id, $data ) {
		$response = parent::add_meta( $id, $data );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$data = (object) $response->get_data();

		$response = new WP_JSON_Response();
		$response->header( 'Location', get_json_url_meta_get_meta( $id , $data->ID ) );
		$response->set_data( $data );
		$response = wpapper_json_ensure_response( $response );

		return $response;
	}

	/**
	 * Add post meta to post responses.
	 *
	 * Adds meta to post responses for the 'edit' context.
	 *
	 * @param WP_Error|array $data Post response data (or error)
	 * @param array $post Post data
	 * @param string $context Context for the prepared post.
	 * @return WP_Error|array Filtered data
	 */
	public function add_post_meta_data( $data, $post, $context ) {
		if ( $context !== 'edit' || is_wp_error( $data ) ) {
			return $data;
		}

		// Permissions have already been checked at this point, so no need to
		// check again
		$data['post_meta'] = $this->get_all_meta( $post['ID'] );
		if ( is_wp_error( $data['post_meta'] ) ) {
			return $data['post_meta'];
		}

		return $data;
	}

	/**
	 * Add post meta on post update.
	 *
	 * Handles adding/updating post meta when creating or updating posts.
	 *
	 * @param array $post New post data
	 * @param array $data Raw submitted data
	 * @return array|WP_Error Post data on success, post meta error otherwise
	 */
	public function insert_post_meta( $post, $data ) {
		// Post meta
		if ( ! empty( $data['post_meta'] ) ) {
			$result = $this->handle_inline_meta( $post['ID'], $data['post_meta'] );

			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}

		return $post;
	}

	/**
	 * Call protected method from {@see WP_JSON_Posts}.
	 *
	 * WPAPI-1.2 deprecated a bunch of protected methods by moving them to this
	 * class. This proxy method is added to call those methods.
	 *
	 * @param string $method Method name
	 * @param array $args Method arguments
	 * @return mixed Return value from the method
	 */
	public function _deprecated_call( $method, $args ) {
		return call_user_func_array( array( $this, $method ), $args );
	}
}
