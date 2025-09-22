<?php
// build.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/plain');

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$recurse = isset($_GET['recurse']) && intval($_GET['recurse']) === 1;

if (!$keyword) {
  http_response_code(400);
  echo "Missing keyword.";
  exit;
}

$map = [
  'gifgen' => '../gifgen',
  'cf'     => '../cf',
  'cf-php' => '../cf-php',
  'php'    => '../php',
  'pb'     => __DIR__,
  'dc'     => '../diff-checker',
  'tr'     => '../tr',
  'sg'     => '../show-chatgpt',
  'build' => __DIR__,
];

if (!isset($map[$keyword])) {
  http_response_code(400);
  echo "Unknown keyword: $keyword";
  exit;
}

$folder = realpath($map[$keyword]);
if (!$folder || !is_dir($folder)) {
  http_response_code(500);
  echo "Invalid folder.";
  exit;
}

// --- Drill-down support ---
$subPath = isset($_GET['path']) ? trim($_GET['path']) : '';
if ($subPath) {
    $target = realpath($folder . '/' . $subPath);
    if (!$target) {
        http_response_code(404);
        echo "Not found.";
        exit;
    }
    if (is_file($target)) {
        echo "FILECONTENT::";
        readfile($target);
        exit;
    }
    if (is_dir($target)) {
        $folder = $target;
    }
}

$items = scandir($folder);
$items = array_filter($items, function($item) {
  return $item !== '.' && $item !== '..';
});
sort($items);

echo "/" . basename($folder) . "\n";
$total = count($items);
$i = 0;
foreach ($items as $item) {
  $i++;
  $isLast = ($i === $total);
  $pointer = $isLast ? '└── ' : '├── ';
  echo $pointer . (is_dir("$folder/$item") ? $item . "/" : $item) . "\n";
}
