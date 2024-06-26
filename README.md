# SvgSpriteGenerator

`SvgSpriteGenerator` sınıfı, SVG dosyalarını birleştirip bir simge seti (sprite) oluşturarak web projelerinizde
kullanmanıza olanak tanır. Ayrıca, bu oluşturulan simge setini HTML ve CSS ile birlikte kullanmanızı sağlayan yardımcı
fonksiyonlar içerir.

## Kullanım

Sınıfı kullanmak için aşağıdaki adımları izleyebilirsiniz:

1. **SvgSpriteGenerator Sınıfını Dahil Etme:**

    ```php
    require_once 'class.svgsprit.php';
    ```

2. **SvgSpriteGenerator İnstance Oluşturma:**

    ```php
    $sourceDir = __DIR__ . "/svg"; // SVG dosyalarının bulunduğu klasör
    $outputFile = "svgsprite.svg"; // Oluşturulan sprite'ın kaydedileceği dosya
    $excludeFiles = ['exclude1', 'exclude2']; // Hariç tutulacak dosyaların listesi (opsiyonel)
    $includeFiles = ['include1', 'include2']; // Dahil edilecek dosyaların listesi (opsiyonel) 
     //  $includeFiles dizisi boş gönderilirse $sourceDir dizininde bulunan tüm svg dosyalarını ekleyecektir.
    $svgSpriteGenerator = new SvgSpriteGenerator($sourceDir, $outputFile, $excludeFiles, $includeFiles);
    ```

3. **Sprite Oluşturma ve Kaydetme:**

    ```php
    $svgSpriteGenerator->generateSprite();
    ```

   Bu adım, belirttiğiniz kayıtlı dosyaya SVG sprite'ı oluşturur.

4. **HTML ve CSS İle Kullanım:**

    ```php
    echo $svgSpriteGenerator->getIconList();
    ```

   Bu adım, oluşturulan sprite'ı HTML ve CSS ile birlikte kullanmak için gerekli olan HTML ve CSS kodunu üretir.

## Parametreler

- **$sourceDir (string):** SVG dosyalarının bulunduğu klasör.
- **$outputFile (string):** Oluşturulan sprite'ın kaydedileceği dosya.
- **$excludeFiles (array):** Hariç tutulacak dosyaların listesi (opsiyonel).
- **$includeFiles (array):** Dahil edilecek dosyaların listesi (opsiyonel).

## Metodlar

- **generateSprite($minify = true):** SVG sprite'ını oluşturur ve belirtilen dosyaya kaydeder.
- **getIconList($inline = true):** Oluşturulan sprite'ı HTML ve CSS ile birlikte kullanmak için gerekli kodu üretir.

### Örnekler


 ```php
 $sourceDir = __DIR__ . "/svg"; 
 $outputFile = "svgsprite.svg"; 
 $excludeFiles = []; 
 $includeFiles = [
 'cart', 
 'mapmarker'=>["path"=>false] // svg dosyasına bulunan path değerlerini tek bir katman halinde almak için kullanabilirsiniz false belirtmediğiniz takdirde her katmanlı  svg dosyasını path1 path2 şeklinde katmanlara bölecektir.
 ]; 
  //  $includeFiles dizisi boş gönderilirse $sourceDir dizininde bulunan tüm svg dosyalarını ekleyecektir.
 $svgSpriteGenerator = new SvgSpriteGenerator($sourceDir, $outputFile, $excludeFiles, $includeFiles);
 ```

