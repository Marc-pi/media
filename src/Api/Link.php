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
use Pi;
use Pi\Application\Api\AbstractApi;
use Pi\File\Transfer\Upload;
use Pi\Filter;

class Link extends AbstractApi
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
    protected function model($name = 'link')
    {
        $model = Pi::model($name, $this->module);

        return $model;
    }

    /**
     * Add a link
     *
     * @param array $data
     *
     * @return int
     */
    public function add(array $data)
    {
        $row = $this->model()->createRow($data);
        $row->save();

        return (int) $row->id;
    }

    /**
     * Get links by object
     *
     * @param array $params
     * @return \Laminas\Db\ResultSet\ResultSetInterface
     */
    public function getlistByObject($params)
    {
        $select = $this->model()->select();
        $select->where($params);

        $result = $this->model()->selectWith($select);

        return $result;
    }
    
    /**
     * Remove links by object
     * 
     * @param array $params
     * @return bool
     */
    public function deleteByObject($params)
    {
        $result = $this->model()->delete($params);

        return $result;
    }

    /**
     * Update links from doc to objects
     * @param \Pi\Db\RowGateway\RowGateway $object
     */
    public function updateLinks($object){

        $model = $object->getModel();

        if(!empty($object->id) && $mediaLinks = $model->getMediaLinks()){
            $tableWithoutPrefix = str_replace(Pi::db()->getTablePrefix(), '', $model->getTable());

            preg_match('#^([^_]*)_(.*)#', $tableWithoutPrefix, $matches);

            if(isset($matches[1], $matches[2])){
                $newLinks = array();

                $module = $matches[1];
                $object_name = $matches[2];
                $object_id = $object->id;

                $data = array(
                    'module' => $module,
                    'object_name' => $object_name,
                    'object_id' => $object_id,
                );

                $currentLinks = $this->getlistByObject($data)->toArray();
                foreach($currentLinks as $key => $currentLink){
                    unset($currentLinks[$key]['id']);
                }

                foreach($mediaLinks as $mediaLink){
                    if(isset($object->$mediaLink)){
                        $values = explode(',', $object->$mediaLink);

                        foreach($values as $value){
                            $data['field'] = $mediaLink;
                            $data['media_id'] = intval($value);

                            $newLinks[] = $data;
                        }
                    }
                }

                $linksToDelete = $currentLinks;
                $linksToAdd = array();

                foreach($newLinks as $newLink){
                    if(!in_array($newLink, $currentLinks)){
                        $linksToAdd[] = $newLink;
                    }else{
                        $key = array_search($newLink, $linksToDelete);

                        unset($linksToDelete[$key]);
                    }
                }

                foreach($linksToDelete as $linkToDelete){
                    $this->deleteByObject($linkToDelete);
                }

                foreach($linksToAdd as $linkToAdd){
                    $this->add($linkToAdd);
                }
            }
        }
    }

    /**
     * Remove links from doc to objects
     * @param \Pi\Db\RowGateway\RowGateway $object
     */
    public function removeLinks($object){

        $model = $object->getModel();

        if(!empty($object->id) && $mediaLinks = $model->getMediaLinks()){
            $tableWithoutPrefix = str_replace(Pi::db()->getTablePrefix(), '', $model->getTable());

            preg_match('#^([^_]*)_(.*)#', $tableWithoutPrefix, $matches);

            if(isset($matches[1], $matches[2])){
                $module = $matches[1];
                $object_name = $matches[2];
                $object_id = $object->id;

                $data = array(
                    'module' => $module,
                    'object_name' => $object_name,
                    'object_id' => $object_id,
                );

                $this->deleteByObject($data);
            }
        }
    }
}
