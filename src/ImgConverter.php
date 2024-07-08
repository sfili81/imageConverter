<?php

namespace sfili81\ImgConverter;

/**
 * @link 
 * @copyright Copyright (c) 2024 Sfiligoi Federico
 * @license Apache-2.0
 */

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\db\Exception;

class ImgConverter extends Widget {

    /**
	 * @var string image source relative to the @webroot Yii2 alias (required)
	 */
	public $src;

	/**
	 * @var string path to the generated WebP file format or null
	 */
	private $_webp = null;

    /**
	 * @var int quality value for the generated WebP/AVIF file 
	 */
    public $quality = 80;

	/**
	 * @var string path to the generated AVIF file formator null
	 */
	private $_avif = null;

    /**
	 * @var array (optional)
	 */
    public $options;

    public function init() {
		parent::init();

        if(!isset($this->src)){
            throw new Exception("Property src is missing");
        }
        if(empty($this->src)){
            throw new Exception("Property src is empty");
        }
        //check file exists
        if(!file_exists(Yii::getAlias('@webroot') . '' .$this->src)){
            throw new Exception("Image file doesn't exist");
        }

        //process image only if extension for the original image isn't webp or avif
        $ext = pathinfo($this->src, PATHINFO_EXTENSION);
        if($ext !== "webp" && $ext !== "avif"){
            $this->_webp = $this->convertImage($this->src, ".webp", "imagewebp", $this->quality);
            $this->_avif = $this->convertImage($this->src, ".avif", "imageavif", $this->quality);
        }
	}

    public function run() {     
		// our unoptimized image (include all the possible attributes)
		$img = Html::img(Yii::getAlias('@web') . $this->src, 
			$this->options,
		);

		// was WebP/AVIF image generated from our unoptimized image?
		if ($this->_webp != null || $this->_avif != null)
		{
            if($this->_avif) $this->_avif = Yii::getAlias('@web') .$this->_avif;
            if($this->_webp) $this->_webp = Yii::getAlias('@web') .$this->_webp;
            
			// include it within <picture> tag
			$html = "<picture>";

			if($this->_avif) $html .= Html::tag("source", [], ["srcset" => $this->_avif, "type" => "image/avif"]);
			if($this->_webp) $html .= Html::tag("source", [], ["srcset" => $this->_webp, "type" => "image/webp"]);

			// fallback image (unoptimized)
			$html .= $img;
			$html .= "</picture>";

		}
		else
		{
			$html = $img;
		}

		// if lightbox attribute is present - wrap the image into a lightbox friendly
		// <a href link
		/*if ($this->lightbox_data)
		{
			return Html::a($html, $this->lightbox_src, [ "data-lightbox" => $this->lightbox_data, "data-title" => $this->lightbox_title ] );
		}*/

		//return $html;
        return $html;
	}

    private function convertImage(string $inputFile , string $fileExtension, $conversionFunction, int $quality ): null|string {

        $fileType = exif_imagetype(Yii::getAlias('@webroot') . '' .$inputFile);

        $file_info = pathinfo($inputFile);

		$output_filename = $file_info["filename"] . $fileExtension;

		$output_short_path = $file_info["dirname"] . "/" . $output_filename;

        if(file_exists(Yii::getAlias('@webroot') . '' . $output_short_path)){
            switch ($fileExtension) {
                case 'webp':
                    return $this->_webp = $output_short_path;
                case 'avif':    
                    return $this->_avif = $output_short_path;
            }
        } 

        switch ($fileType) {
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif(Yii::getAlias('@webroot').$inputFile);
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg(Yii::getAlias('@webroot').$inputFile);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng(Yii::getAlias('@webroot').$inputFile);
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            /*case IMAGETYPE_WEBP:
                //rename($inputFile, $outputFile); 
                return;*/
            default:
                break;
        }

        $conversion = call_user_func($conversionFunction,$image,Yii::getAlias('@webroot'). $output_short_path, $quality);
        //dd(call_user_func($conversionFunction,$image, $output_short_path, $quality));
        
        if($conversion){
            return $this->_webp = $output_short_path;
        }else{
            return null;
        }
        
    }

}//end class
