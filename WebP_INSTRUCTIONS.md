WebP cache behavior

- The `catalog/model/tool/image.php` resize method now generates a .webp cached image alongside the existing cached format when the GD extension supports WebP (`imagewebp` function exists).
- When a client request includes the `Accept: image/webp` header and the .webp cached file exists, the returned image URL will point to the WebP file.
- Configure WebP quality by adding a config key `image_webp_quality` (0-100). If not set, default quality is 85.
- If the server's GD extension does not support WebP, the code falls back to saving/serving the original image format.

Notes for deployment

- Ensure PHP GD is compiled with WebP support. On Windows XAMPP, enable the appropriate PHP extension or use a build with webp enabled.
- Clear `image/cache/` after updating this code so new webp files are generated.
