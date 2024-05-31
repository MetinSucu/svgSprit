<?php

require "class.svgsprit.php";

// Kullanım örneği
$sourceDir = __DIR__ . "/svg/";
$outputFile = 'svgsprite.svg';
$excludeFiles = ['excluded'];// Dizindeki hariç tutulacak dosyalar.
$includeFiles = [
"com001"=>["path"=>false]
]; // Dizindeki eklenecek dosyalar ( boş bırakılırsa tüm dizini tarar )

$spriteGenerator = new SvgSpriteGenerator($sourceDir, $outputFile, $excludeFiles, $includeFiles);
$spriteGenerator->setPathSeparete(true);
$spriteGenerator->generateSprite();
echo $spriteGenerator->getIconList();
