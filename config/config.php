<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * Module config
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
return [
    'category' => [
        [
            'name'  => 'general',
            'title' => _t('General'),
        ],
        [
            'name'  => 'form',
            'title' => _t('Form'),
        ],
        [
            'name'  => 'validator',
            'title' => _t('Validator'),
        ],
        [
            'name'  => 'image',
            'title' => _t('Image'),
        ],
    ],

    'item' => [
        // General
        'page_limit'                  => [
            'category'    => 'general',
            'title'       => _t('List page limit'),
            'description' => _t('Maximum count of media resources on a list page.'),
            'value'       => 20,
            'filter'      => 'int',
        ],
        'license_values'              => [
            'category'    => 'general',
            'title'       => _t('License values'),
            'description' => _t('Use `|` as delimiter to separate license terms'),
            'edit'        => 'text',
            'value'       => '',
        ],
        // Form
        'form_description'            => [
            'category'    => 'form',
            'title'       => _t('Show description on form'),
            'description' => '',
            'edit'        => 'checkbox',
            'filter'      => 'number_int',
            'value'       => 1,
        ],
        'form_season'                 => [
            'category'    => 'form',
            'title'       => _t('Show season on form'),
            'description' => '',
            'edit'        => 'checkbox',
            'filter'      => 'number_int',
            'value'       => 1,
        ],
        'form_license_type'           => [
            'category'    => 'form',
            'title'       => _t('Show license on form'),
            'description' => '',
            'edit'        => 'checkbox',
            'filter'      => 'number_int',
            'value'       => 1,
        ],
        'form_copyright'              => [
            'category'    => 'form',
            'title'       => _t('Show copyright on form'),
            'description' => '',
            'edit'        => 'checkbox',
            'filter'      => 'number_int',
            'value'       => 1,
        ],
        // Media
        'extension'                   => [
            'category'    => 'validator',
            'title'       => _t('File extension'),
            'description' => _t('Extensions for files allowed to upload.'),
            'value'       => 'jpg,jpeg,png,gif', //'pdf,rar,zip,doc,txt,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif',
        ],
        'max_size'                    => [
            'category'    => 'validator',
            'title'       => _t('Max file size'),
            'description' => _t(
                'Maximum size for files allowed to upload (in KB). Caution : on front side, max file size messages are displayed in octets (ko). Here you set in bytes (Kb) ! For 4Mo, you have to set 4 * 1024 = 4096 kb. Also, front limit is rounded to lower Mb integer after conversion, so 4096 Kb == 4Mo and 4095 kb == 3Mo'
            ),
            'value'       => 2048,
            'filter'      => 'int',
        ],
        // Image
        'image_maxw'                  => [
            'category'    => 'image',
            'title'       => _t('Max Image width (upload)'),
            'description' => '',
            'edit'        => 'text',
            'filter'      => 'number_int',
            'value'       => 2000,
        ],
        'image_maxh'                  => [
            'category'    => 'image',
            'title'       => _t('Max Image height (upload)'),
            'description' => '',
            'edit'        => 'text',
            'filter'      => 'number_int',
            'value'       => 2000,
        ],
        'image_minw'                  => [
            'category'    => 'image',
            'title'       => _t('Min Image width (upload)'),
            'description' => 'This config can be overriden by custom module values',
            'edit'        => 'text',
            'filter'      => 'number_int',
            'value'       => 600,
        ],
        'image_minh'                  => [
            'category'    => 'image',
            'title'       => _t('Min Image height (upload)'),
            'description' => 'This config can be overriden by custom module values',
            'edit'        => 'text',
            'filter'      => 'number_int',
            'value'       => 600,
        ],
        'image_quality'               => [
            'category'    => 'image',
            'title'       => _t('Image quality'),
            'description' => _t('Between 0 to 100 and support both of JPG and PNG, default is 75. Can be overridden by custom module config'),
            'edit'        => 'text',
            'filter'      => 'number_int',
            'value'       => 90,
        ],
        'image_ratio_w'               => [
            'category'    => 'image',
            'title'       => _t('Image ratio width'),
            'description' => _t('Example : "3" for 3/2 ratio'),
            'edit'        => 'text',
            'filter'      => 'number_int',
            'value'       => 3,
        ],
        'image_ratio_h'               => [
            'category'    => 'image',
            'title'       => _t('Image ratio height'),
            'description' => _t('Example : "2" for 3/2 ratio'),
            'edit'        => 'text',
            'filter'      => 'number_int',
            'value'       => 2,
        ],
        'image_watermark'             => [
            'category'    => 'image',
            'title'       => _t('Add Watermark'),
            'description' => '',
            'edit'        => 'checkbox',
            'filter'      => 'number_int',
            'value'       => 0,
        ],
        'image_watermark_source'      => [
            'category'    => 'image',
            'title'       => _t('Watermark Image'),
            'description' => '',
            'edit'        => 'text',
            'filter'      => 'string',
            'value'       => '',
        ],
        'image_watermark_position'    => [
            'title'       => _t('Watermark Position'),
            'description' => '',
            'filter'      => 'text',
            'value'       => 'bottom-right',
            'category'    => 'image',
            'edit'        => [
                'type'    => 'select',
                'options' => [
                    'options' => [
                        'top-left'     => _t('Top Left'),
                        'top-right'    => _t('Top Right'),
                        'bottom-left'  => _t('Bottom Left'),
                        'bottom-right' => _t('Bottom Right'),
                    ],
                ],
            ],
        ],
        'image_default_copyright'     => [
            'category' => 'image',
            'title'    => _t('Default copyright'),
            'edit'     => 'text',
            'value'    => '',
        ],
        'freemium_max_gallery_images' => [
            'category' => 'image',
            'title'    => _t('Max gallery images for freemium related items'),
            'edit'     => 'text',
            'filter'   => 'number_int',
            'value'    => 2,
        ],
        'freemium_alert_msg'          => [
            'category' => 'image',
            'title'    => _t('Alert message for freemium item'),
            'edit'     => 'text',
            'value'    => _t("Freemium item limitations... You can't do this action"),
        ],
        'image_largew'                => [
            'category'    => 'image',
            'title'       => _t('Large Image width'),
            'description' => 'This config can be overriden by custom module values. Used for min crop size.',
            'edit'        => 'text',
            'filter'      => 'number_int',
            'value'       => 1200,
        ],
        'image_largeh'                => [
            'category'    => 'image',
            'title'       => _t('Large Image height'),
            'description' => 'This config can be overriden by custom module values. Used for min crop size.',
            'edit'        => 'text',
            'filter'      => 'number_int',
            'value'       => 1200,
        ],
        'image_itemw'                 => [
            'category'    => 'image',
            'title'       => _a('Item Image width'),
            'description' => '',
            'edit'        => 'text',
            'filter'      => 'number_int',
            'value'       => 800,
        ],
        'image_itemh'                 => [
            'category'    => 'image',
            'title'       => _a('Item Image height'),
            'description' => '',
            'edit'        => 'text',
            'filter'      => 'number_int',
            'value'       => 800,
        ],
        'image_mediumw'               => [
            'category'    => 'image',
            'title'       => _a('Medium Image width'),
            'description' => '',
            'edit'        => 'text',
            'filter'      => 'number_int',
            'value'       => 500,
        ],
        'image_mediumh'               => [
            'category'    => 'image',
            'title'       => _a('Medium Image height'),
            'description' => '',
            'edit'        => 'text',
            'filter'      => 'number_int',
            'value'       => 500,
        ],
        'image_thumbw'                => [
            'category'    => 'image',
            'title'       => _a('Thumb Image width'),
            'description' => '',
            'edit'        => 'text',
            'filter'      => 'number_int',
            'value'       => 250,
        ],
        'image_thumbh'                => [
            'category'    => 'image',
            'title'       => _a('Thumb Image height'),
            'description' => '',
            'edit'        => 'text',
            'filter'      => 'number_int',
            'value'       => 250,
        ],
    ],
];
