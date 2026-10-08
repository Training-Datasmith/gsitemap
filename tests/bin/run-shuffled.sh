#!/usr/bin/env bash
set -euo pipefail
SEED="${GSITEMAP_SHUFFLE_SEED:-20261008}"
DIR="$(cd "$(dirname "$0")/.." && pwd)"
mapfile -t TESTS < <(php -r '
$seed = (int) getenv("GSITEMAP_SHUFFLE_SEED") ?: 20261008;
$dir = $argv[1];
$files = array();
foreach (scandir($dir) as $f) {
    if (substr($f, -8) === "Test.php") {
        $files[] = $dir . "/" . $f;
    }
}
sort($files);
mt_srand($seed);
for ($i = count($files) - 1; $i > 0; $i--) {
    $j = mt_rand(0, $i);
    $tmp = $files[$i];
    $files[$i] = $files[$j];
    $files[$j] = $tmp;
}
foreach ($files as $f) {
    echo $f, PHP_EOL;
}
' "$DIR/Unit")
for file in "${TESTS[@]}"; do
  php "$DIR/../vendor/bin/phpunit" -c "$DIR/phpunit.xml.dist" "$file"
done
