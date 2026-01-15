<?php
class ModelToolImage extends Model {
	public function resize($filename, $width, $height) {
		if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
			return;
		}

		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		// Generate cached image path (original extension)
		$image_old = $filename;
		$image_new = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . (int)$width . 'x' . (int)$height . '.' . $extension;

		// Create directories if not exist
		$path = '';
		$directories = explode('/', dirname($image_new));
		foreach ($directories as $directory) {
			$path = $path . '/' . $directory;
			if (!is_dir(DIR_IMAGE . $path)) {
				@mkdir(DIR_IMAGE . $path, 0777);
			}
		}

		// Resize original image if needed
		if (!is_file(DIR_IMAGE . $image_new) || (filemtime(DIR_IMAGE . $image_old) > filemtime(DIR_IMAGE . $image_new))) {
			list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);

			if (!in_array($image_type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF))) { 
				return DIR_IMAGE . $image_old;
			}

			if ($width_orig != $width || $height_orig != $height) {
				$image = new Image(DIR_IMAGE . $image_old);
				$image->resize($width, $height);
				$image->save(DIR_IMAGE . $image_new);
			} else {
				copy(DIR_IMAGE . $image_old, DIR_IMAGE . $image_new);
			}
		}

		// -------- WebP Conversion --------
		$webp_new = preg_replace('/\.(jpe?g|png|gif)$/i', '.webp', $image_new);
		if (!is_file(DIR_IMAGE . $webp_new) || (filemtime(DIR_IMAGE . $image_new) > filemtime(DIR_IMAGE . $webp_new))) {
			$image_info = getimagesize(DIR_IMAGE . $image_new);
			switch ($image_info[2]) {
				case IMAGETYPE_JPEG:
					$img = imagecreatefromjpeg(DIR_IMAGE . $image_new);
					break;
				case IMAGETYPE_PNG:
					$img = imagecreatefrompng(DIR_IMAGE . $image_new);
					// preserve transparency
					imagepalettetotruecolor($img); 
					imagealphablending($img, true);
					imagesavealpha($img, true);
					break;
				case IMAGETYPE_GIF:
					$img = imagecreatefromgif(DIR_IMAGE . $image_new);
					break;
				default:
					$img = false;
			}

			if ($img) {
				imagewebp($img, DIR_IMAGE . $webp_new, 80); // 80 = quality
				imagedestroy($img);
			}
		}

		// Encode spaces for URL
		$image_new = str_replace(' ', '%20', $webp_new);  

		if ($this->request->server['HTTPS']) {
			return $this->config->get('config_ssl') . 'image/' . $image_new;
		} else {
			return $this->config->get('config_url') . 'image/' . $image_new;
		}
	}
}
