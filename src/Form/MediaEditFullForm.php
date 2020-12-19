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
 * @author Frédéric TISSOT <contact@espritdev.fr>
 */
class MediaEditFullForm extends MediaEditForm
{
    /**
     * Initalizing form
     */
    public function init()
    {
        $this->add(
            [
                'name'       => 'filename',
                'options'    => [
                    'label' => __('Filename'),
                ],
                'attributes' => [
                    'required' => true,
                ],
            ]
        );

        parent::init();

        $this->remove('submit');


        $this->add(
            [
                'name'    => 'uid',
                'options' => [
                    'label' => __('User ID'),
                ],
            ]
        );

        $this->add(
            [
                'name'    => 'featured',
                'type'    => 'checkbox',
                'options' => [
                    'label' => __('Featured'),
                ],
            ]
        );

        // latitude
        $this->add(
            [
                'name'       => 'latitude',
                'options'    => [
                    'label' => __('Location latitude'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                ],
            ]
        );

        // longitude
        $this->add(
            [
                'name'       => 'longitude',
                'options'    => [
                    'label' => __('Location longitude'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => '',
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'submit',
                'attributes' => [
                    'value' => __('Submit'),
                ],
                'type'       => 'submit',
            ]
        );
    }
}
