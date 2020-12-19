<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Controller\Admin;

use Module\Media\Form\TestForm;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Pi;

/**
 * Test controller
 * 
 * @author Frédéric TISSOT <contact@espritdev.fr>
 */
class ToolsController extends ActionController
{
    /**
     * List all media
     * 
     * @return ViewModel
     */
    public function indexAction()
    {
        $this->view()->assign(array(
            'title'      => _a('Tools'),
        ));
    }

    public function fillDescriptionAction()
    {
        $mediaModel = Pi::model('doc', 'media');
        $select = $mediaModel->select();

        $select->where(array(
            new \Laminas\Db\Sql\Predicate\Like('description', ''),
            new \Laminas\Db\Sql\Predicate\NotLike('filename', ''),
        ));

        $mediaCollection = Pi::model('doc', 'media')->selectWith($select);

        if($mediaCollection->count()){
            foreach($mediaCollection as $mediaEntity){

                if($mediaEntity->title){
                    $mediaEntity->description = ucfirst($mediaEntity->title);
                } else {
                    preg_match('#(.*)\.(.*)$#', $mediaEntity->filename, $matches);

                    $mediaEntity->description = ucfirst(str_replace('-', ' ', $matches[1]));
                }

                $mediaEntity->save();
            }

            $messenger = $this->plugin('flashMessenger');
            $messenger->addSuccessMessage(__('Descriptions are filled successfully'));
        } else {
            $messenger = $this->plugin('flashMessenger');
            $messenger->addMessage(__('Description are filled yet'));
        }

        $this->redirect()->toRoute(null, array('action' => 'index'));
    }

    public function removeOrphanedAction(){

        $mediaModel = Pi::model('doc', 'media');
        $linkModel = Pi::model('link', 'media');
        $select = $mediaModel->select();
        $select->join(array('link' => $linkModel->getTable()), "link.media_id = " . $mediaModel->getTable() . '.id', array());

        $mediaCollection = Pi::model('doc', 'media')->selectWith($select);

        $activeMediaPath = array();
        foreach($mediaCollection as $mediaEntity){
            $activeMediaPath[] = $mediaEntity->path . $mediaEntity->filename;
        }

        $options    = Pi::service('media')->getOption('local', 'options');
        $rootPath   = $options['root_path'];

        $hasRemove = 0;

        /**
         * Clean cache media
         */
        $dir = $rootPath . '/processed';

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));

        foreach ($iterator as $file) {
            if ($file->isDir()){
                continue;
            }
            $filepath = str_replace($rootPath, '', $file->getPathname());

            $exploded = explode('/', $filepath);

            unset($exploded[2]);

            $filepath = implode('/', $exploded);
            $filepath = str_replace('processed', 'original', $filepath);

            if(!in_array($filepath, $activeMediaPath)){
                unlink($file->getPathname());
                $hasRemove++;
            }
        }

        $messenger = $this->plugin('flashMessenger');

        if($hasRemove){
            $messenger->addSuccessMessage($hasRemove . __('Orphaned media have been removed'));
        } else {
            $messenger->addMessage(__('There is no orphaned media currently'));
        }

        $this->redirect()->toRoute(null, array('action' => 'index'));
    }

    public function cleanSoftDeletedMediaAction()
    {
        $options    = Pi::service('media')->getOption('local', 'options');
        $rootPath   = $options['root_path'];

        $mediaModel = Pi::model('doc', 'media');
        $select = $mediaModel->select();
        $select->where('time_deleted > 0');

        $mediaCollection = Pi::model('doc', 'media')->selectWith($select);

        $removedMedia = array();

        foreach($mediaCollection as $mediaEntity){
            $fullPath = $rootPath . $mediaEntity->path . $mediaEntity->filename;

            if(is_file($fullPath)){
                unlink($fullPath);
            }

            $mediaEntity->delete();
            $removedMedia[] = $fullPath;
        }

        $messenger = $this->plugin('flashMessenger');

        if($removedMedia){
            $messenger->addSuccessMessage(__('Soft-deleted media have been hard removed'));
        } else {
            $messenger->addMessage(__('There is no soft-deleted media currently'));
        }

        $this->redirect()->toRoute(null, array('action' => 'index'));
    }
}
