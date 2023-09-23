<?php

class SvgSpriteGenerator
{
    private $sourceDir;
    private $outputFile;
    private $excludeFiles;
    private $includeFiles;
    private $iconListCSS = '.svgIconList{display:flex;flex-wrap:wrap}.svgIconList .item{cursor:pointer;width:100px;height:100px;display:flex;align-items:center;justify-content:center;padding:20px;border:1px solid #edf1f8}.svgIconList .item .icon{width:100%;max-height:50px;}.svgIconList .item:hover{background:#edf1f8;}';
    private $iconListJS = 'var itemlar=document.querySelectorAll(".item");itemlar.forEach(function(e){e.addEventListener("click",function(){var e=this.innerHTML,t=document.createElement("textarea");t.value=e,document.body.appendChild(t),t.select(),document.execCommand("copy"),document.body.removeChild(t)})});';
    private static $svgNameList;
    private static $svgFileHTML;

    public function __construct($sourceDir, $outputFile, $excludeFiles = [], $includeFiles = [])
    {
        $this->sourceDir = $sourceDir;
        $this->outputFile = $outputFile;
        $this->excludeFiles = $excludeFiles;
        $this->includeFiles = $includeFiles;
    }

    public function generateSprite()
    {
        $svgFiles = $this->scanDirectory($this->sourceDir);

        if (empty($svgFiles)) {
            echo "Klasörde SVG dosyası bulunamadı.";
            exit;
        }

        $svgSprite = '<svg width="0" height="0" class="hidden iconset">' . "\n";

        foreach ($svgFiles as $svgFile) {
            $svgName = pathinfo($svgFile, PATHINFO_FILENAME);
            $svgContent = file_get_contents($svgFile);

            preg_match('/viewBox="([^"]+)"/', $svgContent, $viewBoxMatches);
            $viewBox = $viewBoxMatches[1] ? $viewBoxMatches[1] : '0 0 100 100';

            $svgContent = preg_replace('/\s(data-name|fill|stroke)="[^"]+"/', '', $svgContent);
            $svgContent = preg_replace('/id="Group_[^"]+"/', '', $svgContent);
            $svgContent = preg_replace('/id="Rectangle_[^"]+"/', '', $svgContent);
            $svgContent = preg_replace('/id="Path_[^"]+"/', '', $svgContent);

            $svgContent = preg_replace('/<!--(.*?)-->/', '', $svgContent);
            $svgContent = preg_replace('/<\?xml(.+?)\?>/', '', $svgContent);

            $svgContent = preg_replace('/<svg[^>]+>/', '', $svgContent);
            $svgContent = preg_replace('/<\/svg>/', '', $svgContent);

            if (!preg_match('/viewBox="/i', $svgContent)) {
                $svgContent = preg_replace('/<svg/i', '<svg viewBox="0 0 100 100"', $svgContent, 1);
            }
            $svgContent = trim($svgContent);

            $svgSprite .= "<symbol id=\"$svgName\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"$viewBox\">\n$svgContent\n</symbol>\n";
            self::$svgNameList[] = $svgName;
        }
        $svgSprite .= '</svg>';
        $svgSprite = $this->minifySvg($svgSprite);
        self::$svgFileHTML = $svgSprite;
        file_put_contents($this->outputFile, $svgSprite);

        return true;
    }

    private function minifySvg($svg)
    {
        $svg = preg_replace('/[\r\n\t]+/', ' ', $svg);
        $svg = preg_replace('/> </', '><', $svg);
        $svg = preg_replace('/<g[^>]*><\/g>/', '', $svg);
        return $svg;
    }

    public function getIconList($inline=true)
    {

        if (count(self::$svgNameList) > 0) {
            if($inline){
                $returnHTML = self::$svgFileHTML;
            }else{
                $returnHTML="";
            }

            $returnHTML .= '<style>' . $this->iconListCSS . '</style>';
            $returnHTML .= '<div class="svgIconList">';
            foreach (self::$svgNameList as $iconName) {
                if($inline) {
                    $returnHTML .= ' <div class="item"><svg class="icon"><use xlink:href="#' . $iconName . '"></use></svg></div>';
                }else{
                    $returnHTML .= ' <div class="item"><svg class="icon"><use xlink:href="'.$this->outputFile.'#' . $iconName . '"></use></svg></div>';

                }
            }
            $returnHTML .= '</div>';
             $returnHTML .= '<script>' . $this->iconListJS . '</script>';
            return $returnHTML;
        }
        return false;
    }

    private function scanDirectory($dir)
    {
        $svgFiles = [];
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $filePath = $dir . '/' . $file;
            if (is_file($filePath) && pathinfo($file, PATHINFO_EXTENSION) === 'svg') {
                $filename = pathinfo($file, PATHINFO_FILENAME);
                if (
                    (!empty($this->includeFiles) && in_array($filename, $this->includeFiles)) ||
                    (empty($this->includeFiles) && !in_array($filename, $this->excludeFiles))
                ) {
                    $svgFiles[] = $filePath;
                }
            }
        }
        return $svgFiles;
    }
}

?>
