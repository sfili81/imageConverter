#Image converter on the fly

==========================

This widget generates (if note exist) images with extension webp and avif.


Installation

------------
The preferred way to install this extension is through [composer](https://getcomposer.org/download/).
Either run
```
php composer.phar require --prefer-dist sfili81/image-converter "*"
```
or add
```
"sfili81/image-converter": "*"
```
to the require section of your `composer.json` file.

##Usage
Once the extension is installed, simply use it in your code by  :

```php
use sfili81\ImgConverter\ImgConverter;
//..

echo ImgConverter::widget(['src'=>'/path/to/image/image.jpg', 'options'=>['class' => 'my-css-class', 'alt' => 'My Image']]); 

```

This will generate :
```html
<picture>
    <source type="image/avif" srcset="/path/to/image/image.avif">
    <source type="image/webp" srcset="/path/to/image/image.webp">
    <img class="my-css-class" src="/path/to/image/image.jpg" alt="My Image">
</picture>
```
You can pass `.png` or `.jpg` file to the widget. If you pass a `.webp` or `.avif` image the widget skip the image generation and return the following code:
```html
    <img class="my-css-class" src="/path/to/image/image.avif" alt="My Image">
```
