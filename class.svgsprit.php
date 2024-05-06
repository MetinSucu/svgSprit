<?php

class SvgSpriteGenerator
{
    private $sourceDir;
    private $svgFilesData;
    private $outputFile;
    private $excludeFiles;
    private $includeFiles;
    private $svgNameList = [];
    private $svgFileHTML;
    private $iconListCSS = '.svgIconList{display:flex;flex-wrap:wrap}.svgIconList .item{cursor:pointer;width:100px;height:100px;display:flex;align-items:center;justify-content:center;padding:20px;border:1px solid #edf1f8}.svgIconList .item .icon{width:100%;max-height:50px;}.svgIconList .item:hover{background:#edf1f8;}';
    private $iconListJS = 'var itemlar=document.querySelectorAll(".item");itemlar.forEach(function(e){e.addEventListener("click",function(){var e=this.innerHTML,t=document.createElement("textarea");t.value=e,document.body.appendChild(t),t.select(),document.execCommand("copy"),document.body.removeChild(t)})});';

    public function __construct($sourceDir, $outputFile, $excludeFiles = [], $includeFiles = [])
    {
        $this->sourceDir = $sourceDir;
        $this->outputFile = $outputFile;
        $this->excludeFiles = $excludeFiles;
        $this->includeFiles = $includeFiles;
        $outPutFileInfo = pathinfo($outputFile);
        $this->excludeFiles[] = $outPutFileInfo['filename'];
    }

    public function generateSprite($minify = true)
    {
        $this->scanDirectory($this->sourceDir);

        if (empty($this->svgFilesData)) {
            echo "Klasörde SVG dosyası bulunamadı.";
            exit;
        }

        $svgSymbols = "";
        foreach ($this->svgFilesData as $svgData) {
            $filePath = $svgData['filePath'];
            $fileName = $svgData['fileName'];
            $svgName = pathinfo($filePath, PATHINFO_FILENAME);
            $svgContent = file_get_contents($filePath);
            $viewBox = $this->getViewBoxFromSvg($svgContent);
            $svgContent = $this->cleanUpSvgContent($svgContent);
            $svgSymbols .= $this->separatePaths($svgData, $viewBox, $svgContent);
            $this->svgNameList[] = $svgName;
        }


        $spriteContent = $this->createSpriteCode($svgSymbols, $minify);
        $this->saveSpriteFile($this->outputFile, $spriteContent);
        return $this;
    }

    private function saveSpriteFile($outputPath, $svgContent)
    {
        $this->svgFileHTML = $svgContent;
        file_put_contents($outputPath, $svgContent);
        return $this;
    }

    private function createSpriteCode($spriteContent, $minify = true)
    {
        if ($minify) {
            $spriteContent = $this->minifySvg($spriteContent);
        }
        $svgCode = '<svg width="0" height="0" class="hidden iconset">' . "\n";
        $svgCode .= $spriteContent;
        $svgCode .= '</svg>';
        return $svgCode;
    }

    private function createSymbol($svgName, $viewBox, $svgContent)
    {

        return '<symbol id="' . $svgName . '" xmlns="http://www.w3.org/2000/svg" viewBox="' . $viewBox . '">' . "\n" . $svgContent . "\n" . '</symbol>' . "\n";
    }

    private function separatePaths($svgData, $viewBox, $svgContent)
    {
        $newContent = "";
        $pattern = '/<path\s.*?\s*\/?>/s';


        $svgName = $svgData['fileName'];
        preg_match_all($pattern, $svgContent, $matches);


        $path_data = $matches[0];
        $pathCount = 0;
        $this->svgFilesData[$svgName]["path"] = false;
        if (count($path_data) > 1) {
            foreach ($path_data as $pathCode) {
                $pathCount++;
                $newPathName = $svgName . "-" . $pathCount;
                $newContent .= $this->createSymbol($newPathName, $viewBox, $pathCode);

            }
            $this->svgFilesData[$svgName]["path"] = true;
            $this->svgFilesData[$svgName]["pathCount"] = $pathCount;
        } else if (count($path_data) == 1) {
            $newContent .= $this->createSymbol($svgName, $viewBox, $svgContent);
        }
        return $newContent;
    }

    private function getViewBoxFromSvg($svgContent)
    {
        preg_match('/viewBox="([^"]+)"/', $svgContent, $viewBoxMatches);
        return $viewBoxMatches[1] ?? '0 0 100 100';
    }

    private function cleanUpSvgContent($svgContent)
    {

        $svgContent = preg_replace('/\s(data-name|fill|stroke)="[^"]+"/', '', $svgContent);
        $svgContent = preg_replace('/id="(?:Group|Rectangle|Path)_[^"]+"/', '', $svgContent);
        $svgContent = preg_replace('/<!--(.*?)-->/', '', $svgContent);
        $svgContent = preg_replace('/<\?xml(.+?)\?>/', '', $svgContent);
        $svgContent = preg_replace('/<svg[^>]+>/', '', $svgContent);
        $svgContent = preg_replace('/<\/svg>/', '', $svgContent);
        if (!preg_match('/viewBox="/i', $svgContent)) {
            $svgContent = preg_replace('/<svg/i', '<svg viewBox="0 0 100 100"', $svgContent, 1);
        }
        $svgContent = trim($svgContent);

        return $svgContent;
    }

    private function scanDirectory($dir)
    {
        $svgFiles = [];
        $files = scandir($dir);

        foreach ($files as $file) {
            $fileData = [];
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = rtrim($dir, "/") . "/" . $file;

            if (is_file($filePath) && pathinfo($file, PATHINFO_EXTENSION) === 'svg') {
                $filename = pathinfo($file, PATHINFO_FILENAME);

                if (
                    (!empty($this->includeFiles) && (in_array($filename, $this->includeFiles) || isset($this->includeFiles[$filename]))) ||
                    (empty($this->includeFiles) && !in_array($filename, $this->excludeFiles))
                ) {
                    $fileData['fileName'] = $filename;
                    $fileData['filePath'] = $filePath;
                    $fileData['pathCount'] = 1;
                    if (isset($this->includeFiles[$filename])) {
                        $fileData = array_merge($fileData, $this->includeFiles[$filename]);

                    } else {

                        $fileData['path'] = true;
                    }
                    $svgFiles[$filename] = $fileData;
                }
            }
        }
        $this->svgFilesData = $svgFiles;
        return true;
    }

    private function minifySvg($svg)
    {
        $svg = preg_replace('/[\r\n\t]+/', ' ', $svg);
        $svg = preg_replace('/> </', '><', $svg);
        $svg = preg_replace('/<g[^>]*><\/g>/', '', $svg);

        return $svg;
    }

    public function getIconList($inline = true)
    {
        if (count($this->svgNameList) > 0) {
            $returnHTML = ($inline) ? $this->svgFileHTML : "";

            $returnHTML .= '<style>' . $this->iconListCSS . '</style>';
            $returnHTML .= '<div class="svgIconList">';

            foreach ($this->svgNameList as $iconName) {

                $returnHTML .= $this->getIconHTML($iconName, $inline);

            }

            $returnHTML .= '</div>';
            $returnHTML .= '<script>' . $this->iconListJS . '</script>';

            return $returnHTML;
        }

        return false;
    }


    private function getIconHTML($iconName, $inline = true)
    {
        $svgData = $this->svgFilesData[$iconName];
        $fileName = $svgData['fileName'];
        $outputHTML = "";

        $externalPath = $inline ? '' : $this->outputFile;
        if ($svgData['path']) {
            $outputHTML .= '<div class="item" title="' . $fileName . '">  ';
            $outputHTML .= '<svg class="icon"> ';
            for ($i = 1; $i <= $svgData['pathCount']; $i++) {
                $newIconName = $iconName . "-" . $i;
                $outputHTML .= '<use class="path' . $i . '" xlink:href="' . $externalPath . '#' . $newIconName . '"></use>';
            }
            $outputHTML .= '</svg>';
            $outputHTML .= '</div>';
        } else {
            $outputHTML = ' <div class="item" title="' . $fileName . '"><svg class="icon"><use xlink:href="' . $externalPath . '#' . $iconName . '"></use></svg></div>';
        }
        return $outputHTML;

    }
}

?>
