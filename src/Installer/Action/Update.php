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
namespace Module\Media\Installer\Action;

use Pi;
use Pi\Application\Installer\Action\Update as BasicUpdate;
use Pi\Application\Installer\SqlSchema;
use Laminas\EventManager\Event;

class Update extends BasicUpdate
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('update.pre', array($this, 'updateSchema'));
        parent::attachDefaultListeners();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function updateSchema(Event $e)
    {
        $moduleVersion = $e->getParam('version');

        // Set doc model
        $docModel = Pi::model('doc', $this->module);
        $docTable = $docModel->getTable();
        $docAdapter = $docModel->getAdapter();

        // Set link model
        $linkModel = Pi::model('link', $this->module);
        $linkTable = $linkModel->getTable();
        $linkAdapter = $linkModel->getAdapter();

        // Set test model
        $testModel = Pi::model('test', $this->module);
        $testTable = $testModel->getTable();
        $testAdapter = $testModel->getAdapter();

        if (version_compare($moduleVersion, '1.0.4', '<')) {

            $sql =<<<SQL
ALTER TABLE %s
  DROP `url`,
  DROP `path`,
  DROP `name`,
  DROP `size`,
  DROP `module`,
  DROP `type`,
  DROP `token`,
  DROP `ip`,
SQL;

            $sql = sprintf($sql, $docTable);
            try {
                $docAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.0.5', '<')) {

            $sql =<<<SQL
ALTER TABLE %s ADD `season` TINYINT NULL AFTER `count`, ADD `updated_by` INT NULL AFTER `season`, ADD `license_type` VARCHAR(255) NULL AFTER `updated_by`, ADD `copyright` VARCHAR(255) NULL AFTER `license_type`, ADD `cropping` TEXT NULL AFTER `geoloc_longitude`, ADD `featured` TINYINT NOT NULL AFTER `cropping`;
SQL;

            $sql = sprintf($sql, $docTable);
            try {
                $docAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.0.6', '<')) {

            $sql =<<<SQL
CREATE TABLE %s ( `id` INT NOT NULL AUTO_INCREMENT , `title` VARCHAR(255) NOT NULL , PRIMARY KEY (`id`));
SQL;

            $sql = sprintf($sql, $testTable);
            try {
                $testAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table create query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.0.7', '<')) {

            $sql =<<<SQL
ALTER TABLE %s ADD `main_image` INT NULL AFTER `title`, ADD `additional_images` VARCHAR(255) NULL AFTER `main_image`;
SQL;

            $sql = sprintf($sql, $testTable);
            try {
                $testAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table create query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.0.8', '<')) {

            $sql =<<<SQL
CREATE TABLE %s (
    `id` INT NOT NULL AUTO_INCREMENT ,
    `module` VARCHAR(20) NOT NULL ,
    `object_name` VARCHAR(50) NOT NULL ,
    `object_id` INT NOT NULL ,
    `field` VARCHAR(50) NOT NULL ,
    `media_id` INT NOT NULL ,
    PRIMARY KEY (`id`)
);
SQL;

            $sql = sprintf($sql, $linkTable);
            try {
                $linkAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table create query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.0.9', '<')) {

            $sql =<<<SQL
ALTER TABLE %s ADD INDEX( `media_id`);
SQL;

            $sql = sprintf($sql, $linkTable);
            try {
                $linkAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.0.10', '<')) {
            $sql = sprintf("ALTER TABLE %s ADD FULLTEXT `search_idx` (`title`, `description`);", $docTable);
            try {
                $docAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }

            $sql = sprintf("ALTER TABLE %s ADD FULLTEXT `search_title_idx` (`title`);", $docTable);

            try {
                $docAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }

            $sql = sprintf("ALTER TABLE %s ADD FULLTEXT `search_description_idx` (`description`);", $docTable);

            try {
                $docAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.0.15', '<')) {
            $sql = sprintf("ALTER TABLE %s ADD FULLTEXT `search_2_idx` (`title`, `description`, `filename`);", $docTable);
            try {
                $docAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }

            $sql = sprintf("ALTER TABLE %s ADD FULLTEXT `search_filename_idx` (`filename`);", $docTable);

            try {
                $docAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.1.2', '<')) {
            $sql = sprintf("ALTER TABLE %s CHANGE `featured` `featured` TINYINT(4) NOT NULL DEFAULT '0';", $docTable);
            try {
                $docAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }

            $sql = sprintf("ALTER TABLE %s CHANGE `id` `id` INT(10) NOT NULL AUTO_INCREMENT;", $linkTable);
            try {
                $linkAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }

            $sql = sprintf("ALTER TABLE %s CHANGE `module` `module` VARCHAR(20) NOT NULL DEFAULT '';", $linkTable);
            try {
                $linkAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }

            $sql = sprintf("ALTER TABLE %s CHANGE `object_name` `object_name` VARCHAR(50) NOT NULL DEFAULT '';", $linkTable);
            try {
                $linkAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }

            $sql = sprintf("ALTER TABLE %s CHANGE `object_id` `object_id` INT(10) NOT NULL DEFAULT 0;", $linkTable);
            try {
                $linkAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }

            $sql = sprintf("ALTER TABLE %s CHANGE `field` `field` VARCHAR(50) NOT NULL DEFAULT '';", $linkTable);
            try {
                $linkAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }

            $sql = sprintf("ALTER TABLE %s CHANGE `media_id` `media_id` INT(10) NOT NULL DEFAULT 0;", $linkTable);
            try {
                $linkAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.1.3', '<')) {

            $sql = sprintf("ALTER TABLE %s DROP `geoloc_latitude`;", $docTable);
            try {
                $docAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }


            $sql = sprintf("ALTER TABLE %s DROP `geoloc_longitude`;", $docTable);
            try {
                $docAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.1.10', '<')) {
            $sql = sprintf("ALTER TABLE %s ADD `latitude` VARCHAR(16) NOT NULL DEFAULT '';", $docTable);

            try {
                $docAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }

            $sql = sprintf("ALTER TABLE %s ADD `longitude` VARCHAR(16) NOT NULL DEFAULT '';", $docTable);

            try {
                $docAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
        }

        return true;
    }
}
