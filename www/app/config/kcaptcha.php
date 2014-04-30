<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

# KCAPTCHA configuration file

$alphabet = "0123456789abcdefghijklmnopqrstuvwxyz"; # do not change without changing font files!

# symbols used to draw CAPTCHA
//$allowed_symbols = "0123456789"; #digits
//$allowed_symbols = "23456789abcdegkmnpqsuvxyz"; #alphabet without similar symbols (o=0, 1=l, i=j, t=f)
$allowed_symbols = "23456789abcdegikpqsvxyz"; #alphabet without similar symbols (o=0, 1=l, i=j, t=f)

# folder with fonts
$fontsdir = 'fonts';

# CAPTCHA string length
$length = mt_rand(5,7); # random 5 or 6 or 7
//$length = 6;

# CAPTCHA image size (you do not need to change it, whis parameters is optimal)
$width = 100;
$height = 50;

# symbol's vertical fluctuation amplitude
$fluctuation_amplitude = 8;

#noise
//$white_noise_density=0; // no white noise
$white_noise_density = 0; // 1/6;
//$black_noise_density=0; // no black noise
$black_noise_density = 0; // 1/30;

# increase safety by prevention of spaces between symbols
$no_spaces = TRUE;

# show credits
$show_credits = FALSE; # set to false to remove credits line. Credits adds 12 pixels to image height
$credits = 'tested.co.kr'; # if empty, HTTP_HOST will be shown // www.captcha.ru

# CAPTCHA image colors (RGB, 0-255)
//$foreground_color = array(0, 0, 0);
//$background_color = array(220, 230, 255);
$foreground_color = array(mt_rand(0,100), mt_rand(0,100), mt_rand(0,100));
$background_color = array(mt_rand(200,255), mt_rand(200,255), mt_rand(200,255));

# JPEG quality of CAPTCHA image (bigger is better quality, but larger file size)
$jpeg_quality = 90;

// $wave = false;
?>