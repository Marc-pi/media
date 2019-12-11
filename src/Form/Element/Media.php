<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
namespace Module\Media\Form\Element;

use Pi;

class Media extends \Zend\Form\Element\Text
{
    /**
     * @return array
     */
    public function getAttributes()
    {
        $fromModule = $this->getOption('module') ?: 'media';
        $fromUid = $this->getOption('uid') ?: null;

        // Load language
        Pi::service('i18n')->load(array('module/media', 'default'));

        $modalTitle = __("Media gallery");
        $saveBtnTitle = __("Add selected media");

        $jQueryHelper = Pi::service('view')->gethelper('jQuery');
        $jQueryHelper();

        $assetHelper = Pi::service('view')->gethelper('assetModule');
        $jsHelper = Pi::service('view')->gethelper('js');
        $jsHelper($assetHelper('js/dropzone.js', 'media'));
        $jsHelper($assetHelper('js/jquery-ui.custom.min.js', 'media'));
        $jsHelper($assetHelper('js/jquery.dataTables.min.js', 'media'));
        $jsHelper($assetHelper('js/dataTables.bootstrap4.min.js', 'media'));

        $jsHelper($assetHelper('js/exif.js', 'media'));
        $jsHelper($assetHelper('js/load-image.all.min.js', 'media'));
        $jsHelper($assetHelper('js/modal.js', 'media'));

        $cssHelper = Pi::service('view')->gethelper('css');
        $cssHelper($assetHelper('css/dropzone.css', 'media'));
        $cssHelper($assetHelper('css/dataTables.bootstrap4.min.css', 'media'));
        $cssHelper($assetHelper('css/media.css', 'media'));

        $max = Pi::service('module')->config('max_size', 'media');
        $uploadMaxSize = floor($max / 1024) * 1024 . __(' kb');
        $uploadMaxSizeMb = floor(Pi::service('module')->config('max_size', 'media') / 1024);

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

        $uploadMinDimensions = $imageMinW . ' x ' . $imageMinH . " px";
        $uploadMaxDimensions = Pi::service('module')->config('image_maxw', 'media') . ' x ' . Pi::service('module')->config('image_maxh', 'media') . " px";

        $uploadUrl = Pi::service('url')->assemble(Pi::engine()->section() == 'admin' ? 'admin' : 'default', array(
            'module' => 'media',
            'controller' => 'modal',
            'action' => 'upload',
        ));

        $uploadUrl .= '?from_module=' . $fromModule;

        $listUrl = Pi::service('url')->assemble(Pi::engine()->section() == 'admin' ? 'admin' : 'default', array(
            'module' => 'media',
            'controller' => 'modal',
            'action' => 'list',
        ));

        $currentSelectedMediaUrl = Pi::service('url')->assemble(Pi::engine()->section() == 'admin' ? 'admin' : 'default', array(
            'module' => 'media',
            'controller' => 'modal',
            'action' => 'currentSelectedMedia',
        ));

        $formlistUrl = Pi::service('url')->assemble(Pi::engine()->section() == 'admin' ? 'admin' : 'default', array(
            'module' => 'media',
            'controller' => 'modal',
            'action' => 'formlist',
        ));

        $mediaFormAction = Pi::service('url')->assemble(Pi::engine()->section() == 'admin' ? 'admin' : 'default', array(
            'module' => 'media',
            'controller' => 'modal',
            'action' => 'mediaform',
        ));

        $maxGalleryImages = Pi::service('module')->config('freemium_max_gallery_images', $fromModule);
        $freemiumMsg = Pi::service('module')->config('freemium_alert_msg', $fromModule);


        $closeTitle = __("Close");
        $confirmTitle = __("Confirm");
        $freemiumAlertTitle = __("Alert Freemium account limitations");
        $seasonAlertTitle = __("Alert season duplicates");
        $seasonAlertMsg = __("Alert season duplicates : you must have online one picture of each season only");
        $confirmDeleteHeaderTitle = __("Delete media");
        $confirmDeleteActionTitle = __("Do you confirm you want to delete this media ? In case you delete it inadvertently, you can active it back in your My Media submenu");

        $maxAlertTitle = __("Max media alert");
        $errorAlertTitle = __("Error during upload");
        $errorAlertBefore = __("Oops ! You reached the limits set for files : please modify your file(s) !");
        $errorAlertAfter = __("Given the quality of the telecom networks and to allow an optimal experience for all in all circumstances during the upload (mobile + desktop), we have been forced to set up a high maximum limit of the weight of the uploaded files.");

        $maxAlertMsg = __("Max media alert : you have reach maximum of picture for this field");

        $checkedMediaTitle = __("Your selection (you can unlink each media)");
        $formModalTitle = __("Edit");
        $formModalSaveBtn = __("Save");
        $formModalCancelBtn = __("Cancel");

        $colThumbnail = __("Thumbnail");
        $colTitle = __("Title");
        $colDate = __("Date");
        $colSeason = __("Season");
        $sProcessing = __("Loading in progress");
        $sSearch = __("Search&nbsp;:");

        $sLengthMenu = __("Show _MENU_ elements");
        $sInfo = __("Show elements _START_ to _END_ on _TOTAL_ elements");
        $sInfoEmpty = __("Show elements 0 to 0 on 0 element");
        $sInfoFiltered = __("(filtered from _MAX_ elements)");
        $sLoadingRecords = __("Loading in progress...");
        $sZeroRecords = __("No element to display");
        $sEmptyTable = __("No data available in the table");
        $sFirst = __("First");
        $sPrevious = __("Previous");
        $sNext = __("Next");
        $sLast = __("Last");

        $uploadMsg = sprintf(__("Drop files here to upload new files<br /><span class='badge badge-warning'>Max Size = %s and min dimensions = %s</span><br />(or select existing files below)"), $uploadMaxSize, $uploadMinDimensions);
        $dictFileTooBig = __("File size is to high ({{filesize}}kb). Max : {{maxFilesize}}kb");


        $customFilters = '<div><label for="show_checked_items_first"><input type="checkbox" name="show_checked_items_first" id="show_checked_items_first" value="1" /> '.__("Show checked items first").'</label>';

        if(Pi::engine()->section() == 'admin' && $fromUid){
            $customFilters .= '&nbsp;&nbsp;&nbsp;<label for="show_uid_media"><input type="checkbox" name="show_uid_media" id="show_uid_media" value="'.$fromUid.'" /> '.__("Show Item UID media").'</label>';
        }

        $customFilters .= '</div>';

        $modalHtml = <<<HTML
        
<div id="addMediaModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{$modalTitle} <span class="max"></span></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
            
                <div id="dropzone-media-form" class="dropzone"></div>
                <br />
                
                <div id="media_gallery">
                
                    {$customFilters}
                    <table class="table table-striped" data-sprocessing="{$sProcessing}" data-ssearch="{$sSearch}" data-slengthmenu="{$sLengthMenu}" data-sinfo="{$sInfo}" data-sinfoempty="{$sInfoEmpty}" data-sinfofiltered="{$sInfoFiltered}" data-sloadingrecords="{$sLoadingRecords}" data-szerorecords="{$sZeroRecords}" data-semptytable="{$sEmptyTable}" data-sfirst="{$sFirst}" data-sprevious="{$sPrevious}" data-snext="{$sNext}" data-slast="{$sLast}">
                        <thead>
                        <tr>
                            <th></th>
                            <th>{$colThumbnail}</th>
                            <th class="media-th-title">$colTitle</th>
                            <th>{$colDate}</th>
                            <th>{$colSeason}</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <hr />
                <div id="selectedMediaListModal">
                    <h4>{$checkedMediaTitle} :</h4>
                    <ul class="list"></ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{$formModalCancelBtn}</button>
                <button id="mediaModalSaveBtn" type="button" class="btn btn-primary" data-dismiss="modal">{$saveBtnTitle}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="removeMediaModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" >$confirmDeleteHeaderTitle</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                $confirmDeleteActionTitle
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">$closeTitle</button>
                <button id="removeMediaModalBtn" type="button" class="btn btn-primary">$confirmTitle</button>
            </div>
        </div>
    </div>
</div>

<script>
// Fix for bootstrap bug. When you open 2 modals (first open second), there no scrollbar
$('body').on('hidden.bs.modal', function () {
    if($('.modal.show').length > 0)
    {
        $('body').addClass('modal-open');
    }
});
</script>

<div class="modal fade" id="editMediaModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" >{$formModalTitle}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body editMediaModalContent">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button id="editMediaModalSaveBtn" type="button" class="btn btn-primary">{$formModalSaveBtn}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="freemiumAlert" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" >{$freemiumAlertTitle}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                {$freemiumMsg}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="seasonAlert" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" >{$seasonAlertTitle}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                {$seasonAlertMsg}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="maxAlert" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" >{$maxAlertTitle}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                {$maxAlertMsg}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="errorAlert" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{$errorAlertTitle}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p>{$errorAlertBefore}</p>
                <div id="errorAlertContent"></div>
                <br />
                <p>{$errorAlertAfter}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    var uploadUrl = "{$uploadUrl}";
    var uploadMaxSize = "{$uploadMaxSize}";
    var uploadMaxSizeMb = "{$uploadMaxSizeMb}";
    var uploadMinDimensions = "{$uploadMinDimensions}";
    var uploadMaxDimensions = "{$uploadMaxDimensions}";
    var uploadMsg = "{$uploadMsg}"
    var dictFileTooBig = "{$dictFileTooBig}"
    var listUrl = "{$listUrl}";
    var currentSelectedMediaUrl = "{$currentSelectedMediaUrl}";
    var formlistUrl = "{$formlistUrl}";
    var mediaFormAction = "{$mediaFormAction}";
</script>
HTML;

        $addLabel = __("Choose or add new media file");
        $name = $this->getName();
        $isMediaGallery = $this->getOption('media_gallery') ? 1 : 0;
        $isFreemium = $this->getOption('is_freemium') ? 1 : 0;

//        echo $isFreemium; die();

        $isMediaSeason = ($this->getOption('media_season') && !$isFreemium) ? 1 : 0;
        $isMediaSeasonRecommended = $this->getOption('media_season_recommended') ? 1 : 0;
        $canConnectLists = $this->getOption('can_connect_lists') ? 1 : 0;

        $isMediaSeasonRecommendedMsg = $isMediaSeason ? __("We recommend you to fill 4 pictures for all seasons") : '';

        if($isMediaSeason){
            $isMediaGallery = 1;
        }

        $noMediaLabel = __('No media for now...');
        $loader = $assetHelper('image/spinner.gif', 'media');
        $maxGalleryImagesMsg = '';
        $maxGalleryImagesConstrain = '';

        if(!$isMediaGallery){
            $maxGalleryImagesConstrain = '';
            $maxGalleryImagesMsg = __('1 picture only');
        } elseif($isMediaSeason){
            $maxGalleryImagesConstrain = 4;
            $maxGalleryImagesMsg = __('1 picture per season only = 4 pictures');
        } else if($isFreemium && $maxGalleryImages > 0){
            $maxGalleryImagesConstrain = $maxGalleryImages;
            $maxGalleryImagesMsg = ($isFreemium && $maxGalleryImages > 0) ? sprintf(__('Max %s pictures'), $maxGalleryImages) : '';
        }

        $description = <<<HTML
        
<div class="card">
  <div class="card-header"><button class="btn btn-primary btn-sm" data-input-name="{$name}" data-media-season="{$isMediaSeason}" data-media-gallery="{$isMediaGallery}" data-max-gallery-images="{$maxGalleryImagesConstrain}" data-max-msg="{$maxGalleryImagesMsg}" data-toggle="modal" type="button" data-target="#addMediaModal">
    <span class="glyphicon glyphicon-picture"></span> {$addLabel}</button>
    &nbsp;&nbsp;&nbsp;<strong>{$maxGalleryImagesMsg}</strong> &nbsp;&nbsp;&nbsp; <span class="badge badge-warning label-lg additional_info d-none">{$isMediaSeasonRecommendedMsg}</span>
  </div>
  <div class="card-body">
    <div class="media-form-list media-form-list-{$name}" data-can-connect-lists="{$canConnectLists}" data-media-season="{$isMediaSeason}" data-media-season-recommended="{$isMediaSeasonRecommended}" data-input-name="{$name}" data-freemium="{$isFreemium}" data-max-gallery-images="{$maxGalleryImagesConstrain}">
        <div class="ajax-spinner d-none">
            <img src="{$loader}" class="ajax-spinner-loader" alt="" />
        </div>
        <ul class="sortable-list">
            <li class="ui-state-default">{$noMediaLabel}</li>
        </ul>
    </div>
  </div>
</div>

<div class="ajax-spinner-prototype d-none">
    <div class="ajax-spinner">
        <img src="{$loader}" class="ajax-spinner-loader" alt="" />
    </div>
</div>
HTML;

        if(!isset($GLOBALS['isMediaModalLoaded'])){
            $GLOBALS['isMediaModalLoaded'] = true;

            $cropView = new \Zend\View\Model\ViewModel;
            $cropView->setTemplate('media:front/partial/crop');
            $cropView->setVariable('module', 'media');
            $cropView->setVariable('controller', 'list');
            $cropHtml = Pi::service('view')->render($cropView);

            /* @var \Pi\Application\Service\View $view */
            $view = Pi::service('view');
            $view->getHelper('footScript')->addHtml($modalHtml);
            $view->getHelper('footScript')->addHtml($cropHtml);
        }

        $this->attributes['id'] = 'media_input_' . $this->getName();
        $this->attributes['class'] = 'media-input d-none';
        $this->attributes['description'] = $description;

        return $this->attributes;
    }
}