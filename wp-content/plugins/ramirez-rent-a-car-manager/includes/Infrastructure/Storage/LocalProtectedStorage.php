<?php
namespace RamirezRentACar\Infrastructure\Storage;

class LocalProtectedStorage {
	private $upload_dir;

	public function __construct() {
		// Define a non-public uploads directory under wp-content/uploads/rrc_protected
		$wp_uploads = wp_upload_dir();
		$this->upload_dir = $wp_uploads['basedir'] . '/rrc_protected';

		if ( ! file_exists( $this->upload_dir ) ) {
			wp_mkdir_p( $this->upload_dir );
			// Write htaccess to deny access
			file_put_contents( $this->upload_dir . '/.htaccess', "Deny from all\n" );
			// Write index.html to deny listing
			file_put_contents( $this->upload_dir . '/index.html', '' );
		}
	}

	public function store( $file_name, $temp_path, $mime_type ) {
		// Validate MIME type
		$allowed_mimes = [
			'application/pdf' => 'pdf',
			'image/jpeg'      => 'jpg',
			'image/jpg'       => 'jpg',
			'image/png'       => 'png',
			'image/webp'      => 'webp'
		];

		if ( ! isset( $allowed_mimes[ $mime_type ] ) ) {
			return new \WP_Error( 'invalid_mime', 'File type not allowed.' );
		}

		$ext = $allowed_mimes[ $mime_type ];
		$secure_name = wp_unique_filename( $this->upload_dir, md5( uniqid( microtime(), true ) ) . '.' . $ext );
		$target_path = $this->upload_dir . '/' . $secure_name;

		if ( ! move_uploaded_file( $temp_path, $target_path ) ) {
			return new \WP_Error( 'upload_failed', 'Could not save file.' );
		}

		$sha256 = hash_file( 'sha256', $target_path );

		return [
			'storage_key'       => $secure_name,
			'original_filename' => $file_name,
			'mime_type'         => $mime_type,
			'size_bytes'        => filesize( $target_path ),
			'sha256'            => $sha256
		];
	}

	public function serve( $storage_key ) {
		$file_path = $this->upload_dir . '/' . $storage_key;
		if ( ! file_exists( $file_path ) ) {
			return false;
		}
		return $file_path;
	}
}
