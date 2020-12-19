<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Model\Doc;

use \Pi;

class RowGateway extends \Pi\Db\RowGateway\RowGateway
{
    public function save($rePopulate = true, $filter = true)
    {
        Pi::api('doc', 'media')->removeImageCache($this);

        if(isset($this->season) && $this->season == 0){
            $this->season = null;
        }

        if(!isset($this->cropping) || !$this->cropping){

            $options    = Pi::service('media')->getOption('local', 'options');
            $rootPath   = $options['root_path'];

            $ratio = Pi::api('doc', 'media')->getRatio();

            $filepath = $rootPath . $this->path . $this->filename;
            list($width, $height) = getimagesize($filepath, $info);

            $exif = exif_read_data($filepath);

            if(!empty($exif['Orientation']) && in_array($exif['Orientation'], array(5,6,7,8))){
                /**
                 * Switch width / height according to image orientation
                 */
                $oldWidth = $width;
                $oldHeight = $height;
                $width = $oldHeight;
                $height = $oldWidth;
            }

            $currentRatio = $width / $height;

            /**
             * Default ratio
             */
            $cropParams = array(
                'x' => 0,
                'y' => 0,
                'width' => $width,
                'height' => $height,
                'scaleX' => 1,
                'scaleY' => 1,
            );

            if ($currentRatio > $ratio) {
                /**
                 * Too much width
                 */
                $newWidth = round($height * $ratio);
                $offset = round(($width - $newWidth) / 2);

                $cropParams['width'] = $newWidth;
                $cropParams['x'] = $offset;

            } else if($currentRatio < $ratio){
                /**
                 * To much height
                 */
                $newHeight = round($width / $ratio);
                $offset = round(($height - $newHeight) / 2);

                $cropParams['height'] = $newHeight;
                $cropParams['y'] = $offset;
            }

            $this->cropping = json_encode($cropParams);
        }

        $return = parent::save($rePopulate, $filter);

        $this->removePageCacheForTargets();

        return $return;
    }

    public function delete()
    {
        Pi::api('doc', 'media')->removeImageCache($this);

        $this->removePageCacheForTargets();

        return parent::delete();
    }


    public function removePageCacheForTargets()
    {
        if(isset($this->id) && $this->id){
            $select = Pi::model('link', 'media')->select();
            $select->where(array(
                'media_id' => $this->id
            ));

            $linkCollection = Pi::model('link', 'media')->selectWith($select);

            foreach($linkCollection as $link){
                $module = $link->module;
                $object_name = $link->object_name;
                $object_id = $link->object_id;

                $entity = Pi::model($object_name, $module)->find($object_id);

                /**
                 * Saving trigger flush cache internaly
                 */
                if($entity && isset($entity->id)){
                    $entity->save();
                }

                /**
                 * Try flushing extra object as event is an extended story object
                 */
                // ToDo : $module should be `news` or `event` ?
                if($module == 'news' && Pi::service('module')->isActive('event')){
                    $entity = Pi::model('extra', 'event')->find($object_id);
                    if($entity && isset($entity->id)){
                        $entity->save();
                    }
                }
            }
        }
    }
}
