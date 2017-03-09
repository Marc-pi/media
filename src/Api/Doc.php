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
use Zend\Db\Sql\Predicate\In;

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
        $row->save();

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
    public function upload(array $params, $currentId = null)
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
            $this->module
        );

        $imageMinH = Pi::config(
            'image_minh',
            $this->module
        );

        if($imageMinW && $imageMinH){
            $uploader->setImageSize(array('minwidth' => $imageMinW, 'minheight' => $imageMinH));
        }

        $imageMaxW = Pi::config(
            'image_maxw',
            $this->module
        );

        $imageMaxH = Pi::config(
            'image_maxh',
            $this->module
        );

        if($imageMaxW && $imageMaxH){
            $uploader->setImageSize(array('maxwidth' => $imageMaxW, 'maxheight' => $imageMaxH));
        }

        $result = $uploader->isValid();
        if ($result) {
            $uploader->receive();
            $filename = $uploader->getUploaded();
            if (is_array($filename)) {
                $filename = current($filename);
            }
            // Fetch file attributes
            $fileinfoList = $uploader->getFileInfo();
            $fileinfo = current($fileinfoList);
            if (!isset($params['mimetype'])) {
                $params['mimetype'] = $fileinfo['type'];
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
     * @param array  $condition
     * @param int    $limit
     * @param int    $offset
     * @param string|array $order
     * @param array $attr
     *
     * @return array
     */
    public function getList(
        array $condition,
        $limit  = 0,
        $offset = 0,
        $order  = '',
        array $attr = array()
    ) {
        $model  = $this->model();
        $select = $model->select()->where($condition);
        if ($limit) {
            $select->limit($limit);
        }
        if ($offset) {
            $select->offset($offset);
        }
        if ($order) {
            $select->order($order);
        }
        if ($attr) {
            $select->columns($attr);
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

        return Pi::url('/upload/media' .$path . $filename);
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
     * Get Original Single link url
     * @param $value
     * @return bool
     */
    public function getOriginalSingleLinkUrl($value){
        $ids = explode(',', $value);

        if($ids){
            /**
             * @todo get seasonable media
             */
            $media = Pi::model('doc', $this->module)->find(array_shift($ids));
            $publicPath = 'upload/media' . $media['path'] . $media['filename'];

            return Pi::url($publicPath);
        }

        return false;
    }

    /**
     * Get Original Gallery link data
     * @param $value
     * @return bool
     */
    public function getOriginalGalleryLinkData($value){
        $ids = explode(',', $value);

        if($ids){
            /**
             * @todo get seasonable media
             */

            $model = Pi::model('doc', $this->module);
            $select = $model->select();

            $select->where(array(
                new In('id', $ids),
            ));

            $mediaCollection = Pi::model('doc', $this->module)->selectWith($select);

            $mediaArray = array();
            foreach($mediaCollection as $media){
                $dataToInject = $media->toArray();
                $dataToInject['url'] = Pi::url('upload/media' . $media->path . $media->filename);
                $mediaArray[] = $dataToInject;
            }

            return $mediaArray;
        }

        return false;
    }

    /**
     * Resize by media values
     */
    public function getResizedSingleLinkUrl($value){

        $ids = explode(',', $value);

        if($ids){
            /**
             * @todo get seasonable media
             */
            return Pi::api('resize', 'media')->resize(array_shift($ids));
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
            /* @var $element \Zend\Form\Element */

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
}
