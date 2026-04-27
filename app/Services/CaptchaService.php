<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CaptchaService
{
  public function generate()
  {
    $code = Str::upper(Str::random(6));
    $key = 'captcha_' . Str::uuid();

    Cache::put($key, $code, now()->addMinutes(5));

    $width = 160;
    $height = 50;

    $image = imagecreatetruecolor($width, $height);

    $bg = imagecolorallocate($image, 255, 255, 255);
    $textColor = imagecolorallocate($image, 0, 0, 0);

    imagefilledrectangle($image, 0, 0, $width, $height, $bg);

    $fontSize = 20;
    $fontFile = public_path('fonts/arial/ARIAL.TTF');

    $bbox = imagettfbbox($fontSize, 0, $fontFile, $code);

    $textWidth = $bbox[2] - $bbox[0];
    $textHeight = $bbox[1] - $bbox[7];

    $x = ($width - $textWidth) / 2;
    $y = ($height + $textHeight) / 2;

    imagettftext($image, $fontSize, 0, $x, $y, $textColor, $fontFile, $code);

    ob_start();
    imagepng($image);
    $imageData = ob_get_clean();

    return [
      'key' => $key,
      'image' => 'data:image/png;base64,' . base64_encode($imageData),
    ];
  }

  // --------------------------------------------

  public function validate($key, $input)
  {
    $stored = Cache::get($key);

    if (!$stored) {
      return false;
    }

    $isValid = strtoupper($input) === $stored;

    return $isValid;
  }
}
