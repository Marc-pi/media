<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Api;

use Closure;
use Module\Media\Form\MediaEditFilter;
use Module\Media\Form\MediaEditForm;
use Module\Media\Form\MediaEditFullForm;
use Pi;
use Pi\Application\Api\AbstractApi;
use Pi\File\Transfer\Upload;
use Pi\Filter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Predicate\In;

class Doc extends AbstractApi
{
    /**
     * Module name
     * @var string
     */
    protected $module = 'media';

    /**
     * Get model
     *
     * @param string $name
     *
     * @return Pi\Application\Model\Model
     */
    protected function model($name = 'doc')
    {
        $model = Pi::model($name, $this->module);

        return $model;
    }

    /**
     * Canonize doc meta data
     *
     * @param array $data
     *
     * @return array
     */
    protected function canonize(array $data)
    {
        if (!isset($data['attributes'])) {
            $attributes = array();
            $columns = $this->model()->getColumns();
            foreach (array_keys($data) as $key) {
                if (!in_array($key, $columns)) {
                    $attributes[$key] = $data[$key];
                }
            }
            if ($attributes) {
                $data['attributes'] = $attributes;
            }
        }

        return $data;
    }

    /**
     * Add an application
     *
     * @param array $data
     *
     * @return int
     */
    public function addApplication(array $data)
    {
        $model  = $this->model('application');
        $row    = $model->find($data['appkey'], 'appkey');
        if (!$row) {
            $row = $model->createRow($data);
        } else {
            $row->assign($data);
        }
        $row->save();

        return (int) $row->id;
    }

    /**
     * Add a doc
     *
     * @param array $data
     *
     * @return int
     */
    public function add(array $data)
    {
        $data = $this->canonize($data);
        if (!isset($data['time_created'])) {
            $data['time_created'] = time();
        }
        $row = $this->model()->createRow($data);

        try{
            $row->save();
        }catch(\Exception $e){
//            echo $e->getMessage();
        }

        return (int) $row->id;
    }

    /**
     * Upload a doc and save meta
     *
     * @TODO not completed
     *
     * @param array  $params
     * @param string $method
     *
     * @return int doc id
     */
    public function upload(array $params, $currentId = null, $fromModule = 'media')
    {
        @ignore_user_abort(true);
        @set_time_limit(0);

        $options    = Pi::service('media')->getOption('local', 'options');
        $rootPath   = $options['root_path'];

        if (extension_loaded('intl') && !normalizer_is_normalized($params['filename'])) {
            $params['filename'] = normalizer_normalize($params['filename']);
        }

        $filter = new Filter\Urlizer;
        $slug = $filter($params['filename'], '-', true);

        $firstChars = str_split(substr($slug, 0, 3));

        $relativeDestination = '/original/' . implode('/', $firstChars) . '/';

        $destination = $rootPath . $relativeDestination;

        $finalPath = $destination . $slug;
        $finalSlug = $slug;

        $filenameBase = pathinfo($slug, PATHINFO_FILENAME);
        $filenameExt = pathinfo($slug, PATHINFO_EXTENSION);

        $i = 1;
        while(is_file($finalPath)){
            $finalSlug = $filenameBase . '-'. $i++ . '.' . $filenameExt;
            $finalPath = $destination . $finalSlug;
        }

        Pi::service('file')->mkdir($destination);

        $params['filename'] = $finalSlug;

        $success = false;

        $uploader = new Upload(array(
            'destination'   => $destination,
            'rename'        => $finalSlug,
        ));
        $maxSize = Pi::config(
            'max_size',
            $this->module
        );
        if ($maxSize) {
            $uploader->setSize($maxSize * 1024);
        }

        $extensions = Pi::config(
            'extension',
            $this->module
        );

        if($extensions && $extArray = explode(',', $extensions)){
            $uploader->setExtension($extArray);
        }

        $imageMinW = Pi::config(
            'image_minw',
            $fromModule
        ) ?: Pi::config(
            'image_minw',
            'media'
        );

        $imageMinH = Pi::config(
            'image_minh',
            $fromModule
        ) ?: Pi::config(
            'image_minh',
            'media'
        );

        $imageSizeControl = array();

        if($imageMinW && $imageMinH){
            $imageSizeControl['minwidth'] = $imageMinW;
            $imageSizeControl['minheight'] = $imageMinH;
        }

        $imageMaxW = Pi::config(
            'image_maxw',
            'media'
        );

        $imageMaxH = Pi::config(
            'image_maxh',
            'media'
        );

        if($imageMaxW && $imageMaxH){
            $imageSizeControl['maxwidth'] = $imageMaxW;
            $imageSizeControl['maxheight'] = $imageMaxH;
        }

        if($imageSizeControl){
            $uploader->setImageSize($imageSizeControl);
        }

        if(!empty($params['filekey'])){
            $result = $uploader->isValid($params['filekey']);
        } else {
            $result = $uploader->isValid();
        }


        if ($result) {
            if(!empty($params['filekey'])){
                $uploader->receive($params['filekey']);
            } else {
                $uploader->receive();
            }

            $filename = $uploader->getUploaded();
            if (is_array($filename)) {
                $filename = current($filename);
            }
            // Fetch file attributes
            $fileinfoList = $uploader->getFileInfo();
            $fileinfo = current($fileinfoList);
            if (!isset($params['mimetype'])) {
                $params['mimetype'] = mime_content_type($fileinfo['tmp_name']);
            }
            if (!isset($params['size'])) {
                $params['size'] = $fileinfo['size'];
            }
            $success = true;
        }

        if ($success) {
            $params['path'] = $relativeDestination;
            $params['filename'] = $finalSlug;
            $params['id'] = $currentId ?: $this->add($params);
            $result = $params;
        } else {
            $params['upload_errors'] = $uploader->getMessages();
            $result = $params;
        }

        return $result;
    }
    
    /**
     * Update doc
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, array $data)
    {
        $row = $this->model()->find($id);
        if ($row) {
            $data = $this->canonize($data);
            if (array_key_exists('id', $data)) {
                unset($data['id']);
            }
            if (empty($data['time_updated'])) {
                $data['time_updated'] = time();
            }

            if (isset($data['active']) && $data['active'] != $row->active) {
                if($data['active'] == 2){
                    $data['time_deleted'] = time();
                }

                if($data['active'] != 2){
                    $data['time_deleted'] = '';
                }
            }

            $row->assign($data);

            if($uid = Pi::user()->getId()){
                $row->updated_by = $uid;
            }

            $row->save();

            return true;
        }

        return false;
    }
    
    /**
     * Active media
     * 
     * @param int $id
     * @param bool $flag
     *
     * @return bool
     */
    public function activate($id, $flag = true)
    {
        $result = $this->update($id, array('active' => $flag ? 1 : 0));

        return $result;
    }

    /**
     * Get media attributes
     * 
     * @param int|int[] $id
     * @param string|string[] $attribute
     * @return mixed
     */
    public function get($id, $attribute = array())
    {
        $model  = $this->model();
        $select = $model->select()->where(array('id' => $id));
        if ($attribute) {
            $columns = (array) $attribute;
            $columns = array_merge($columns, array('id'));
            $select->columns($columns);
        }
        $rowset = $model->selectWith($select);
        $result = array();
        foreach ($rowset as $row) {
            if ($attribute && is_scalar($attribute)) {
                $result[$row->id] = $row->$attribute;
            } else {
                $result[$row->id] = $row->toArray();
                if (!in_array('id', (array) $attribute)) {
                    unset($result[$row->id]['id']);
                }
            }
        }
        if (is_scalar($id)) {
            if (isset($result[$id])) {
                $result = $result[$id];
            } else {
                $result = array();
            }
        }

        return $result;
    }
    
    /**
     * Get attributes of media resources
     * 
     * @param int[] $ids
     * @param string|string[] $attribute
     * @return array
     */
    public function mget($ids, $attribute = array())
    {
        $result = $this->get($ids, $attribute);

        return $result;
    }
    
    /**
     * Get doc statistics
     * 
     * @param int|int[] $id
     * @return int|array
     */
    public function getStats($id)
    {
        $model  = $this->model('doc');
        $rowset = $model->select(array('id' => $id));
        $result = array();
        foreach ($rowset as $row) {
            $result[$row->id] = $row->count;
        }
        if (is_scalar($id)) {
            if (isset($result[$id])) {
                $result = $result[$id];
            } else {
                $result = array();
            }
        }

        return $result;
    }
    
    /**
     * Get statistics of docs
     * 
     * @param int[] $ids
     * @return array
     */
    public function getStatsList(array $ids)
    {
        $result = $this->getStats($ids);

        return $result;
    }
    
    /**
     * Get file IDs by given condition
     *
     * @param array  $condition
     * @param int    $limit
     * @param int    $offset
     * @param string|array $order
     *
     * @return int[]
     */
    public function getIds(
        array $condition,
        $limit  = 0,
        $offset = 0,
        $order  = ''
    ) {
        $result = $this->getList(
            $condition,
            $limit,
            $offset,
            $order,
            array('id')
        );
        array_walk($result, function ($data, $key) use (&$result) {
            $result[$key] = (int) $data['id'];
        });

        return $result;
    }

    /**
     * Get list by condition
     *
     * @param array $condition
     * @param int $limit
     * @param int $offset
     * @param string $order
     * @param array $attr
     * @param null $keyword
     * @param bool $orphan
     * @return array
     */
    public function getList(
        array $condition,
        $limit  = 0,
        $offset = 0,
        $order  = '',
        array $attr = array(),
        $keyword = null,
        $orphanOnly = false
    ) {
        if(isset($condition['keyword'])){
            unset($condition['keyword']);
        }

        $model  = $this->model();
        $select = $model->select()->where($condition);
        if ($limit) {
            $select->limit($limit);
        }
        if ($offset) {
            $select->offset($offset);
        }

        if ($attr) {
            $select->columns($attr);
        }

        $linkModel = Pi::model('link', $this->module);

        $select->join(array('link' => $linkModel->getTable()), $model->getTable() . '.id = link.media_id', array('using_count' => new \Laminas\Db\Sql\Expression('COUNT(DISTINCT object_id)')), \Laminas\Db\Sql\Select::JOIN_LEFT);
        $select->group($model->getTable() . '.id');

        if($orphanOnly){
            $select->where('link.id IS NULL');
        }

        if($keyword && trim($keyword)){

            $keyword = trim($keyword);
            $keywordArray = explode(' ', $keyword);
            $keywordBoolean = '+' . trim(implode(' +', $keywordArray));

            $select->where(
                new \Laminas\Db\Sql\Predicate\Expression("MATCH(".$model->getTable() . ".title, ".$model->getTable() . ".description, ".$model->getTable() . ".filename) AGAINST (? IN BOOLEAN MODE) OR ".$model->getTable() . ".title LIKE ? OR ".$model->getTable() . ".description LIKE ? OR ".$model->getTable() . ".filename LIKE ?", $keywordBoolean, '%' . $keyword . '%', '%' . $keyword . '%', '%' . $keyword . '%')
            );
            $select->columns(array_merge($select->getRawState($select::COLUMNS), array(
                new \Laminas\Db\Sql\Expression("((MATCH(".$model->getTable() . ".title) AGAINST (?) * 2) + (MATCH(".$model->getTable() . ".description) AGAINST (?) * 1) + (MATCH(".$model->getTable() . ".filename) AGAINST (?) * 1)) AS score", array($keyword, $keyword, $keyword)),
            )));
            $select->order('score DESC, time_created DESC');
        } else {
            if ($order) {
                $select->order($order);
            }
        }

        $rowset = $model->selectWith($select);
        $result = array();
        foreach ($rowset as $row) {
            $result[$row->id] = $row->toArray();
        }

        return $result;
    }
    
    /**
     * Get media count by condition
     * 
     * @param array $condition
     * @return int
     */
    public function getCount(array $condition = array())
    {
        $result = $this->model()->count($condition);
        
        return $result;
    }
    
    /**
     * Get media url
     * 
     * @param int|int[] $id
     * @return string|array
     */
    public function getUrl($id)
    {
        $path = $this->get($id, 'path');
        $filename = $this->get($id, 'filename');

        return Pi::url('upload/media' .$path . $filename);
    }

    /**
     * Download a doc file
     *
     * @param int|int[] $id Doc id
     *
     * @return void
     */
    public function download($id)
    {
        $url = Pi::service('url')->assemble('default', array(
            'module'     => $this->module,
            'controller' => 'download',
            'action'     => 'index',
            'id'         => implode(',', (array) $id),
        ));

        header(sprintf('location: %s', $url));
    }
    
    /**
     * Delete docs
     * 
     * @param int|int[] $ids
     * @return bool
     */
    public function delete($ids)
    {
        $result = $this->model()->update(
            array('time_deleted' => time(), 'active' => 0),
            array('id' => $ids)
        );

        return $result;
    }
    
    /**
     * Transfer filesize
     * 
     * @param string  $value
     * @param bool    $direction
     * @return string|int 
     */
    protected function transferSize($value, $direction = true)
    {
        return Pi::service('file')->transformSize($value);
    }

    /**
     * Get Original Single link data
     * @param $value
     * @return array|bool
     */
    public function getSingleLinkData($value, $width = null, $height = null, $quality = null, $module = 'media'){
        $ids = explode(',', $value);

        if($ids){
            /**
             * helper get first entry (if field is seasonable, then first entry would be the current season (on submit item action, or manual trigger from BO does an automatic sort)
             */
            $id = array_shift($ids);
            $media = Pi::model('doc', $this->module)->find($id);

            if(!$media){
                return null;
            }

            $data = $media->toArray();
            $data['url'] = (string) Pi::api('resize', 'media')->resize($media);

            if($width && $height){
                $data['resized_url'] = (string) Pi::api('resize', 'media')->resize($media)->setConfigModule($module)->thumbcrop($width, $height)->quality($quality);
            } else if($width && is_string($width)){
                $data['resized_url'] = (string) Pi::api('resize', 'media')->resize($media)->setConfigModule($module)->thumbcrop($width)->quality($quality);
            }

            if(!$data['copyright']){
                $config = Pi::service('registry')->config->read('media');
                $data['copyright'] = $config['image_default_copyright'];
            }

            if($data['copyright'] && !preg_match('#©#', $data['copyright'])){
                $data['copyright'] = '© ' . $data['copyright'];
            }

            return $data;
        }

        return false;
    }

    /**
     * Get Original Gallery link data
     * @param $value
     * @return array|bool
     */
    public function getGalleryLinkData($value, $width = null, $height = null, $quality = null, $sortBySeason = false, $additionalImagesToAdd = array(), $module = 'media', $cropMode = false){
        if($value || preg_match('#,#', $additionalImagesToAdd)){
            $ids = explode(',', $value);

            if($additionalImagesToAdd){
                $additionalIds = explode(',', $additionalImagesToAdd);
                array_shift($additionalIds);

                $ids = array_merge($ids, $additionalIds);
            }

            $ids = array_filter($ids);
            $ids = array_unique($ids);

            if($ids){
                $model = Pi::model('doc', $this->module);
                $select = $model->select();

                $select->where(array(new In('id', $ids)));

                $manualSortExpression = new Expression('FIELD (id, '.implode(',', $ids).')');

                if($sortBySeason){
                    // no tags, then season, then manual order
                    $orderSeason = $this->getOrderSeason();
                    $select->order(array(
                        new Expression('season IS NOT NULL'),
                        new Expression('FIELD (season, '.$orderSeason.')'),
                        $manualSortExpression
                    ));
                } else {
                    $select->order(array($manualSortExpression));
                }

                $mediaCollection = Pi::model('doc', $this->module)->selectWith($select);

                $mediaArray = array();
                foreach($mediaCollection as $media){
                    $dataToInject = $media->toArray();
                    $dataToInject['url'] = (string) Pi::api('resize', 'media')->resize($media);

                    if($width && $height){
                        if($cropMode){
                            $dataToInject['resized_url'] = (string) Pi::api('resize', 'media')->resize($media)->setConfigModule($module)->thumbCrop($width, $height)->quality($quality);
                        } else {
                            $dataToInject['resized_url'] = (string) Pi::api('resize', 'media')->resize($media)->setConfigModule($module)->thumb($width, $height)->quality($quality);
                        }
                    } else if($width){

                        if(is_array($width)){
                            foreach($width as $w){
                                if($cropMode){
                                    $h = round($w * 2 / 3);
                                    $dataToInject['resized_url'][$w] = (string) Pi::api('resize', 'media')->resize($media)->setConfigModule($module)->thumbCrop($w,$h)->quality($quality);
                                } else {
                                    $dataToInject['resized_url'][$w] = (string) Pi::api('resize', 'media')->resize($media)->setConfigModule($module)->thumb($w,$w)->quality($quality);
                                }
                            }
                        } else {
                            if($cropMode){
                                $dataToInject['resized_url'] = (string) Pi::api('resize', 'media')->resize($media)->setConfigModule($module)->thumbCrop($width)->quality($quality);
                            } else {
                                $dataToInject['resized_url'] = (string) Pi::api('resize', 'media')->resize($media)->setConfigModule($module)->thumb($width)->quality($quality);
                            }
                        }
                    }

                    if(!$dataToInject['copyright']){
                        $config = Pi::service('registry')->config->read('media');
                        $dataToInject['copyright'] = $config['image_default_copyright'];
                    }

                    if($dataToInject['copyright'] && !preg_match('#©#', $dataToInject['copyright'])){
                        $dataToInject['copyright'] = '© ' . $dataToInject['copyright'];
                    }

                    $mediaArray[] = $dataToInject;
                }

                return $mediaArray;
            }
        }

        return array();
    }

    /**
     * Resize by media values
     */
    public function getSingleLinkUrl($value, $quality = null){

        $ids = explode(',', $value);

        if($ids){
            /**
             * helper get first entry (if field is seasonable, then first entry would be the current season (on submit item action, or manual trigger from BO does an automatic sort)
             */
            $docId = array_shift($ids);

            return Pi::api('resize', 'media')->resize($docId)->quality($quality);
        }

        return Pi::api('resize', 'media')->resize(null)->quality($quality);
    }

    public function getSingleLinkPictureTag($value, $sizes = array(320,768,1200,2000), $quality = null){

        $ids = explode(',', $value);

        if($ids){

            /**
             * helper get first entry (if field is seasonable, then first entry would be the current season (on submit item action, or manual trigger from BO does an automatic sort)
             */
            $id = array_shift($ids);

            $media = Pi::model('doc', $this->module)->find($id);
            if ($media) {
                $data = $media->toArray();
                $data['urls'] = array();

                $data['urls']['original'] = (string) Pi::api('resize', 'media')->resize($media);

                foreach($sizes as $size){
                    $size = (int) $size;
                    $data['urls'][$size] = (string) Pi::api('resize', 'media')->resize($media)->thumb($size, floor($size * 1.5))->quality($quality);
                }

                $pictureView = new \Laminas\View\Model\ViewModel;
                $pictureView->setTemplate('media:front/partial/picture-tag');
                $pictureView->setVariable('data', $data);
                $pictureHtml = Pi::service('view')->render($pictureView);
            } else {
                $pictureHtml = '';
            }

            return $pictureHtml;
        }

        return false;
    }

    /**
     * @param \Pi\Db\RowGateway\RowGateway $media
     */
    public function removeImageCache($media){
        if(!empty($media->id)){
            $path = 'upload/media' . $media->path . $media->filename;
            $path = str_replace('upload/media/original', '', $path);

            $pattern = 'upload/media/processed/*' . $path;
            foreach (glob($pattern) as $filename) {
                unlink($filename);
            }
        }
    }

    /**
     * Get invalid fields from media object
     * @param $media
     * @return array
     */
    public function getInvalidFields($media){
        $form = new MediaEditForm('media');
        $form->setInputFilter(new MediaEditFilter());

        $form->setData($media);
        $form->isValid();

        $invalidFields = array();

        foreach($form->getElements() as $element){
            /* @var $element \Laminas\Form\Element */

            if($element->getName() == 'id') continue;

            $filter = $form->getInputFilter()->get($element->getName());


            if(!$filter->isValid()){
                $invalidFields[] = $element->getName();
            }
        }

        return $invalidFields;
    }

    public function hasInvalidFields($media){

        return (bool) $this->getInvalidFields($media);
    }

    public function getSlugFilename($filename){
        $filter = new Filter\Urlizer;
        $slug = $filter($filename, '-', true);

        return $slug;
    }

    public function getMediaPath($filename){
        $slug = $this->getSlugFilename($filename);

        $firstChars = str_split(substr($slug, 0, 3));

        $relativeDestination = '/original/' . implode('/', $firstChars) . '/';

        return $relativeDestination;
    }


    /**
     * Insert media item
     * @param $mediaData
     * @param $originalImagePath
     * @return mixed
     */
    public function insertMedia($mediaData, $originalImagePath){
        $options    = Pi::service('media')->getOption('local', 'options');
        $rootPath   = $options['root_path'];

        if(is_file($originalImagePath)){
            $baseFilename = basename($originalImagePath);

            $path = Pi::api('doc', 'media')->getMediaPath($baseFilename);
            $slug = Pi::api('doc', 'media')->getSlugFilename($baseFilename);

            $mediaData['mimetype'] = mime_content_type($originalImagePath);
            $mediaData['path'] = $path;
            $mediaData['filename'] = $slug;

            $destination = $rootPath . $path . $slug;

            Pi::service('file')->mkdir($rootPath . $path);

            if(!is_file($destination)){
                @copy($originalImagePath, $destination);
            }

            $mediaEntity = Pi::model('doc', 'media')->select(array('filename' => $slug))->current();

            if(!$mediaEntity || !$mediaEntity->id){
                $mediaEntity = Pi::model('doc', 'media')->createRow($mediaData);
                $mediaEntity->save();
            }

            return $mediaEntity->id;

        } else {
            Pi::service('audit')->log("migrate_media", "Media can't be created - original file does not exist");
            Pi::service('audit')->log("migrate_media", $originalImagePath);
            Pi::service('audit')->log("migrate_media", json_encode($mediaData));
        }
    }

    /**
     * Get order season
     * @param null $forceCurrentDate
     * @return null|string
     */
    public function getOrderSeason($forceCurrentDate = null){

        if($forceCurrentDate){
            $currentDate = date('m/d', $forceCurrentDate);
        } else {
            $currentDate = date('m/d');
        }

        $seasonDates = array(
            1 => Pi::config('season_summer_at', 'guide'),
            2 => Pi::config('season_winter_at', 'guide'),
            3 => Pi::config('season_autumn_at', 'guide'),
            4 => Pi::config('season_spring_at', 'guide'),
        );

        asort($seasonDates);
        end($seasonDates);
        $currentSeason = key($seasonDates);
        reset($seasonDates);

        foreach($seasonDates as $season => $seasonDate){
            if($currentDate >= $seasonDate){
                $currentSeason = $season;
            }
        }

        $orderSeason = null;

        switch($currentSeason){
            case 1:
                $orderSeason = '1,4,3,2';
                break;
            case 2:
                $orderSeason = '2,3,4,1';
                break;
            case 3:
                $orderSeason = '3,1,4,2';
                break;
            case 4:
                $orderSeason = '4,1,3,2';
                break;
            default:
                break;
        }

        return $orderSeason;
    }

    public function getRatio(){
        // default ratio
        $ratio = 3/2;

        /**
         * Get custom ratio
         */
        $config = Pi::service('registry')->config->read('media');
        if($config['image_ratio_w'] && $config['image_ratio_h']){
            $ratio = $config['image_ratio_w'] / $config['image_ratio_h'];
        }

        return $ratio;
    }
}
