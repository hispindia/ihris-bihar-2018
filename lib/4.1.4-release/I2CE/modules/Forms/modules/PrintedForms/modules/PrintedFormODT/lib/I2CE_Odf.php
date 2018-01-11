<?php
/**
* © Copyright 2010 IntraHealth International, Inc.
* 
* This File is part of I2CE 
* 
* I2CE is free software; you can redistribute it and/or modify 
* it under the terms of the GNU General Public License as published by 
* the Free Software Foundation; either version 3 of the License, or
* (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful, 
* but WITHOUT ANY WARRANTY; without even the implied warranty of 
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License 
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
* @package I2CE
* @subpackage I2CE
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.4
* @since v4.0.4
* @filesource 
*/ 
/** 
* Class I2CE_PrintedForm_Render
* 
* @access public
*/


class I2CE_Odf extends Odf {

    public function setField($key,$fieldObj,$extra = array()) {
        ///really should turn this into magic methods in the I2CE_FormField_Image class
        if ($fieldObj instanceof I2CE_FormField_IMAGE) {
            return $this->setImageFromField($key,$fieldObj,$extra);
        } else if  ($fieldObj instanceof I2CE_FormField) {
            $replacement = $fieldObj->getDisplayValue();
            $this->setVars($key,$replacement);
            return true;
        }
    }


    /**
     * Assign a template variable as a picture
     *
     * @param string $key name of the variable within the template
     * @param string $value path to the picture
     * @throws OdfException
     * @return odf
     */
    protected function setImageFromField($key,$fieldObj, $extra=array()) {
        $filename = 'ihris' . $fieldObj->getHTMLName() . '.' . $fieldObj->getExtension();        
        $filename = preg_replace('/[^a-zA-Z0-9\.]/', '', $filename);
        $filename = md5($filename)  . strrchr($filename,'.');
              
        $file = $filename;
        $img_width = "1.000in";
        $img_height = "1.000in";                        
        //get the ratio 
        $px_width = $fieldObj->getImageWidth();
        $px_height = $fieldObj->getImageHeight();
        if ($px_height == 0 || $px_width == 0) {
            I2CE::raiseError("Bad image");
            return false;
        }
        $measure =false;
        $unit = false;
        if (array_key_exists('width',$extra) &&  2 == sscanf($extra['width'],"%f%s", $measure,$unit)  ) {
            $calc = ((float) $px_height) * $measure / ((float) $px_width);
            $m_measure =false;
            $m_unit = false;
            if (array_key_exists('maxheight',$extra) && 2 == sscanf($extra['maxheight'],"%f%s", $m_measure,$m_unit) && $calc > $m_measure ) {
                //we need to rescale  the width and use the maximum height, m_measure
                $calc = ((float) $px_width) * $m_measure / ((float) $px_height);
                $img_width  =  sprintf("%f%s",$calc , $unit);
                $img_height =  sprintf("%f%s",$m_measure , $unit);
            } else if (array_key_exists('height',$extra) && 2 == sscanf($extra['height'],"%f%s", $m_measure,$m_unit)) {
                //no rescaling.
                $img_width = $extra['width'];
                $img_height =  $extra['height'];
            } else {
                $img_width = $extra['width'];
                $img_height =  sprintf("%f%s",$calc , $unit);
            }                            
        }else  if (array_key_exists('height',$extra) &&  2 == sscanf($extra['height'],"%f%s", $measure,$unit)   ) {
            $calc = ((float) $px_width) * $measure / ((float) $px_height);
            $m_measure =false;
            $m_unit = false;
            if (array_key_exists('maxwidth',$extra) && 2 == sscanf($extra['maxwidth'],"%f%s", $m_measure,$m_unit) && $calc > $m_measure ) {
                //we need to rescale  the height and use the maximum width, m_measure
                $calc = ((float) $px_height) * $m_measure / ((float) $px_width);
                $img_height  =  sprintf("%f%s",$calc , $unit);
                $img_width =  sprintf("%f%s",$m_measure , $unit);
            } else if (array_key_exists('width',$extra) && 2 == sscanf($extra['width'],"%f%s", $m_measure,$m_unit)) {
                //no rescaling. (asssuming valid width and height were entered this should be pick up in elseif of the first parent block above.. but keeping it for symmetry)
                $img_height =  $extra['height'];
                $img_width = $extra['width'];
            } else {
                $img_height = $extra['height'];
                $img_width =  sprintf("%f%s",$calc , $unit);
            } 
        } else {
            $width *= self::PIXEL_TO_CM;
            $img_width = $width . 'cm';
            $height *= self::PIXEL_TO_CM;
            $img_height = $height . 'cm';
        }
        
        $xml = <<<IMG
            <draw:frame draw:style-name="fr1" draw:name="$filename" text:anchor-type="as-char" svg:y="0in" svg:width="{$img_width}" svg:height="{$img_height}" draw:z-index="3"><draw:image xlink:href="Pictures/$file" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/></draw:frame>
IMG;
        $this->image_blobs[$filename] = $fieldObj->getValue();
        $this->setVars($key, $xml, false);
        return true;
    }


    protected $image_blobs = array();



    /**
     * Internal save
     *
     * @throws OdfException
     * @return void
     */
    protected function _save()
    {
        $res=$this->file->open($this->tmpfile);
        $this->_parse();
        if (! $this->file->addFromString('content.xml', $this->contentXml)) {
            throw new OdfException('Error during file export addFromString');
        }
        foreach ($this->images as $imageKey => $imageValue) {
            $this->file->addFile($imageKey, 'Pictures/' . $imageValue);
            $this->addImageToManifest($imageValue);
        }
        foreach ($this->image_blobs as $imageKey => $imageBlob) {
            $this->file->addFromString('Pictures/' . $imageKey, $imageBlob);
            $this->addImageToManifest($imageKey);
        }
        if (! $this->file->addFromString('META-INF/manifest.xml', $this->manifestXml)) {
            throw new OdfException('Error during file export: manifest.xml');
        }
        $this->file->close();
    }



    /**
     * Update Manifest file according to added image files
     *
     * @param string	$file		Image file to add into manifest content
     */
    public function addImageToManifest($file)
    {
        $extension = explode('.', $file);
        $add = ' <manifest:file-entry manifest:media-type="image/'.$extension[1].'" manifest:full-path="Pictures/'.$file.'"/>'."\n";
        $this->manifestXml = str_replace('</manifest:manifest>', $add.'</manifest:manifest>', $this->manifestXml);
    }

    
    
  }
