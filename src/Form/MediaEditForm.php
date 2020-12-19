<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Form;

use Pi;
use Pi\Form\Form as BaseForm;

/**
 * Class for initializing form of edit media page
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */ 
class MediaEditForm extends BaseForm
{
    public function __construct($name = null, $option = array())
    {
        $module = Pi::service('module')->current();
        $this->config = Pi::service('registry')->config->read($module);
        $this->thumbUrl = (isset($option['thumbUrl'])) ? $option['thumbUrl'] : '';
        parent::__construct($name);
    }

    /**
     * Initalizing form 
     */
    public function init()
    {

        $this->setAttribute("enctype", "multipart/form-data");

        $this->add(array(
            'name'       => 'title',
            'options'    => array(
                'label'     => __('Title'),
            ),
            'attributes' => array(
                'type'      => 'text',
                'required' => true,
            ),
        ));

        if ($this->config['form_description']) {
            $this->add(array(
                'name' => 'description',
                'options' => array(
                    'label' => __('Description'),
                ),
                'attributes' => array(
                    'type' => 'textarea',
                    'cols' => 10,
                    'rows' => 5,
                    'required' => false,
                ),
            ));
        }

        $this->add(array(
            'name' => 'file',
            'options' => array(
                'label' => __($this->thumbUrl ? "Change file" : "File"),
            ),
            'attributes' => array(
                'type' => 'file',
            ),
        ));

        if ($this->thumbUrl) {
            $this->add(array(
                'name' => 'imageview',
                'type' => 'Module\Media\Form\Element\ImageCrop', // Laminas\Form\Element\Image
                'options' => array(
                    'label' => __('Uploaded image'),
                ),
                'attributes' => array(
                    'src' => $this->thumbUrl,
                ),
            ));

            $this->add(array(
                'name' => 'cropping',
                'type' => 'hidden',
                'options' => array(
                    'label' => __('Cropping data'),
                ),
            ));
        }

        $this->add(array(
            'name'       => 'season',
            'type'       => 'select',
            'options'    => array(
                'label'     => __('Season'),
                'value_options' => array(
                    '' => __('No season'),
                    4 => __('Spring'),
                    1 => __('Summer'),
                    3 => __('Autumn'),
                    2 => __('Winter'),
                ),
            ),
        ));



        if($this->config['form_license_type'] && isset($this->config['license_values']) && !empty($this->config['license_values'])){

            $licenseValues = explode('|', $this->config['license_values']);

            $values = array(
                '' => __('Choose a license type'),
            );

            foreach($licenseValues as $licenseValue){
                $values[] = $licenseValue;
            }

            $this->add(array(
                'name'       => 'license_type',
                'type'       => 'select',
                'options'    => array(
                    'label'     => __('Licence type'),
                    'value_options' => $values,
                ),
            ));
        }

        if ($this->config['form_copyright']) {
            $this->add(array(
                'name' => 'copyright',
                'options' => array(
                    'label' => __('Copyright'),
                ),
            ));
        }

        $this->add(array(
            'name'       => 'id',
            'attributes' => array(
                'id'        => 'id',
                'type'      => 'hidden',
            ),
        ));

        $this->add(array(
            'name'       => 'submit',
            'attributes' => array(               
                'value'     => __('Submit'),
            ),
            'type'       => 'submit',
        ));
    }
}
