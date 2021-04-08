<?php

namespace Ced\CsFedexShipping\Model;
//use Ced\CsFedexShipping\Model\BarcodeGenerator;
use Magento\Framework\App\Filesystem\DirectoryList;
class BarcodeGeneratorPNG extends BarcodeGenerator{

    /**
     * Return a PNG image representation of barcode (requires GD or Imagick library).
     *
     * @param string $code code to print
     * @param string $type type of barcode:
     * @param int $widthFactor Width of a single bar element in pixels.
     * @param int $totalHeight Height of a single bar element in pixels.
     * @param array $color RGB (0-255) foreground color for bar elements (background is transparent).
     * @return string image data or false in case of error.
     * @public
     */
    /*public function getBarcode($code, $type, $widthFactor = 1, $totalHeight = 100, $color = array(0, 0, 0))
    {
        $barcodeData = $this->getBarcodeData($code, $type);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $filesystem = $objectManager->get('Magento\Framework\Filesystem');
        $this->_rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        // calculate image size
        $width = ($barcodeData['maxWidth'] * $widthFactor);
        $height = $totalHeight;

        if (function_exists('imagecreate')) {
            // GD library
            $imagick = false;
            $png = imagecreate($width, $height);
            $colorBackground = imagecolorallocate($png, 255, 255, 255);
            imagecolortransparent($png, $colorBackground);
            $colorForeground = imagecolorallocate($png, $color[0], $color[1], $color[2]);
        } elseif (extension_loaded('imagick')) {
            $imagick = true;
            $colorForeground = new \imagickpixel('rgb(' . $color[0] . ',' . $color[1] . ',' . $color[2] . ')');
            $png = new \Imagick();
            $png->newImage($width, $height, 'none', 'png');
            $imageMagickObject = new \imagickdraw();
            $imageMagickObject->setFillColor($colorForeground);
        } else {
            return false;
        }

        // print bars
        $positionHorizontal = 0;
        foreach ($barcodeData['bars'] as $bar) {
            $bw = round(($bar['width'] * $widthFactor), 3);
            $bh = round(($bar['height'] * $totalHeight / $barcodeData['maxHeight']), 3);
            if ($bar['drawBar']) {
                $y = round(($bar['positionVertical'] * $totalHeight / $barcodeData['maxHeight']), 3);
                // draw a vertical bar
                if ($imagick && isset($imageMagickObject)) {
                    $imageMagickObject->rectangle($positionHorizontal, $y, ($positionHorizontal + $bw), ($y + $bh));
                } else {
                    imagefilledrectangle($png, $positionHorizontal, $y, ($positionHorizontal + $bw) - 1, ($y + $bh),
                        $colorForeground);
                }
            }
            $positionHorizontal += $bw;
        }
        ob_start();
        if ($imagick && isset($imageMagickObject)) { die('====');
            $png->drawImage($imageMagickObject);
            echo $png;
        } else { //die('0-0-0-');
            $imagePath = $this->_rootDirectory->getAbsolutePath('pub/media/').'barcode.png';
            imagepng($png,$imagePath);
            imagedestroy($png);
        }
        $image = ob_get_clean();

        return $imagePath;
    }*/


    public function getBarcode($code,$density = 1.5)
    {
        if(!defined('CODE128A_START_BASE')){
            define('CODE128A_START_BASE', 103);    
        }
        if(!defined('CODE128B_START_BASE')){
            define('CODE128B_START_BASE', 104);    
        }
        if(!defined('CODE128C_START_BASE')){
            define('CODE128C_START_BASE', 105);    
        }
        if(!defined('STOP')){
            define('STOP', 106);    
        }
        /*define('CODE128B_START_BASE', 104);
        define('CODE128C_START_BASE', 105);
        define('STOP', 106);*/
        //$barcodeData = $this->getBarcodeData($code, $type);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $filesystem = $objectManager->get('Magento\Framework\Filesystem');
        $this->_rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $code128_bar_codes      =   array(
                                212222, 222122, 222221, 121223, 121322, 131222, 122213, 122312, 132212, 221213, 221312, 231212, 112232, 122132, 122231, 113222, 123122, 123221, 223211, 221132, 221231,
                                213212, 223112, 312131, 311222, 321122, 321221, 312212, 322112, 322211, 212123, 212321, 232121, 111323, 131123, 131321, 112313, 132113, 132311, 211313, 231113, 231311,
                                112133, 112331, 132131, 113123, 113321, 133121, 313121, 211331, 231131, 213113, 213311, 213131, 311123, 311321, 331121, 312113, 312311, 332111, 314111, 221411, 431111,
                                111224, 111422, 121124, 121421, 141122, 141221, 112214, 112412, 122114, 122411, 142112, 142211, 241211, 221114, 413111, 241112, 134111, 111242, 121142, 121241, 114212,
                                124112, 124211, 411212, 421112, 421211, 212141, 214121, 412121, 111143, 111341, 131141, 114113, 114311, 411113, 411311, 113141, 114131, 311141, 411131, 211412, 211214,
                                211232, 23311120
                            );
        
        //Get the width and height of the barcode
        //Determine the height of the barcode, which is >= .5 inches
        
        $width          =   (((11 * strlen($code)) + 35) * ($density/72)); // density/72 determines bar width at image DPI of 72
        $height         =   ($width * .15 > .7) ? $width * .15 : .7;
        
        $px_width       =   round($width * 72);
        $px_height      =   ($height * 72);
        
        //Create a true color image at the specified height and width
        //Allocate white and black colors
        
        $img        =   imagecreatetruecolor($px_width, $px_height);
        $white      =   imagecolorallocate($img, 255, 255, 255);
        $black      =   imagecolorallocate($img, 0, 0, 0);
        
        //Fill the image white
        //Set the line thickness (based on $density)
        
        imagefill($img, 0, 0, $white);
        imagesetthickness($img, $density);
        
        //Create the checksum integer and the encoding array
        //Both will be assembled in the loop
        
        $checksum   =   CODE128C_START_BASE;
        $encoding   =   array($code128_bar_codes[CODE128C_START_BASE]);
        
        //Add Code 128 values from ASCII values found in $code
        
        for($i = 0; $i < strlen($code); $i++) {
            
            //Add checksum value of character
            
            $checksum   +=  (ord(substr($code, $i, 1)) - 32) * ($i + 1);
            
            //Add Code 128 values from ASCII values found in $code
            //Position is array is ASCII - 32
            
            array_push($encoding, $code128_bar_codes[(ord(substr($code, $i, 1))) - 32]);
            
        }
        
        //Insert the checksum character (remainder of $checksum/103) and STOP value
                
        array_push($encoding, $code128_bar_codes[$checksum%103]);
        array_push($encoding, $code128_bar_codes[STOP]);
        
        //Implode the array as string
        
        $enc_str    =   implode($encoding);
        
        //Assemble the barcode
        
        for($i = 0, $x = 0, $inc = round(($density/72) * 100); $i < strlen($enc_str); $i++) {
            
            //Get the integer value of the string element
            
            $val    =   intval(substr($enc_str, $i, 1));
            
            //Create lines/spaces
            //Bars are generated on even sequences, spaces on odd
            
            for($n = 0; $n < $val; $n++, $x+=$inc) { if($i%2 == 0) imageline($img, $x, 0, $x, $px_height, $black); }
            
        }
        ob_start();
        $imagePath = $this->_rootDirectory->getAbsolutePath('pub/media/').'barcode.png';
        imagepng($img,$imagePath);
        
        //Get the image from the output buffer
        
        $output_img     =   ob_get_clean();
        
        //Return the image
        //echo '<img src="data:image/png;base64,' . base64_encode($output_img) . '" />'; die;

        //return $img;
        /*ob_start();
       
        $imagePath = $this->_rootDirectory->getAbsolutePath('pub/media/').'barcode.png';
        imagepng($png,$imagePath);
        imagedestroy($png);
        $image = ob_get_clean();*/

        return $imagePath;
    }


}