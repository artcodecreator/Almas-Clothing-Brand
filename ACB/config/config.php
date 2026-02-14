<?php
function app_root() {
  $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
  $script = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
  
  // Check for ACB folder
  if (strpos($uri, '/ACB/') === 0 || strpos($script, '/ACB/') === 0) {
    return '/ACB/';
  }
  // Fallback for AlmasClothingBrand if renamed back
  if (strpos($uri, '/AlmasClothingBrand/') === 0 || strpos($script, '/AlmasClothingBrand/') === 0) {
    return '/AlmasClothingBrand/';
  }
  return '/';
}

function base_url($path = '') {
  $path = ltrim($path, '/');
  return app_root() . $path;
}

function asset_url($path = '') {
  $path = ltrim($path, '/');
  return base_url('assets/' . $path);
}

function normalize_url($url) {
  $root = app_root();
  $path = $url;
  
  // Remove known prefixes if present
  if (strpos($url, '/AlmasClothingBrand/') === 0) {
    $path = substr($url, strlen('/AlmasClothingBrand/'));
  } elseif (strpos($url, '/ACB/') === 0) {
    $path = substr($url, strlen('/ACB/'));
  }
  
  return $root . ltrim($path, '/');
}

function image_or_placeholder($url) {
  $path = normalize_url($url);
  $doc = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '';
  $fs = $doc . $path;
  if ($url && $doc && file_exists($fs)) {
    return $path;
  }
  return asset_url('images/placeholder.svg');
}
?>
