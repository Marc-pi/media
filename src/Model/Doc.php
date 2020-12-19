<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Model;

use Pi;
use Pi\Application\Model\Model;

/**
 * Model class for Doc
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Doc extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $rowClass = 'Module\Media\Model\Doc\RowGateway';

    /**
     * {@inheritDoc}
     */
    protected $columns = array(
        'id',
        'path',
        'filename',
        'attributes',
        'mimetype',
        'title',
        'description',
        'active',
        'time_created',
        'time_updated',
        'time_deleted',
        'appkey',
        'uid',
        'count',
        'season',
        'updated_by',
        'license_type',
        'copyright',
        'cropping',
        'featured',
        'latitude',
        'longitude',
    );

    /**
     * {@inheritDoc}
     */
    protected $encodeColumns = array(
        'attributes'     => true,
    );
}
