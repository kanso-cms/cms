<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\database\query;

use kanso\framework\database\query\Builder;
use kanso\framework\database\query\Query;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group framework
 */
class AlterTest extends TestCase
{
    /**
     *
     */
    public function testAddColumn(): void
    {
        $query = 'ALTER TABLE `prefixed_my_table_name` ADD `thumbnail_id` INTEGER | UNSIGNED';

        $columns =
        [
            [
                'Field'   => 'id',
                'Type'    => 'int(11)',
                'Null'    => 'NO',
                'Key'     => 'PRI',
                'Default' => 'NULL',
                'Extra'   => 'auto_increment',
            ],
        ];

        $connectionHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('query')->withArgs(['SHOW COLUMNS FROM `prefixed_my_table_name`'])->andReturn($columns);

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs([$query]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->ALTER_TABLE('my_table_name')->ADD_COLUMN('thumbnail_id', 'INTEGER | UNSIGNED');
    }

    /**
     *
     */
    public function testDropColumn(): void
    {
        $query = 'ALTER TABLE `prefixed_my_table_name` DROP `id`';

        $columns =
        [
            [
                'Field'   => 'id',
                'Type'    => 'int(11)',
                'Null'    => 'NO',
                'Key'     => 'PRI',
                'Default' => 'NULL',
                'Extra'   => 'auto_increment',
            ],
        ];

        $connectionHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('query')->withArgs(['SHOW COLUMNS FROM `prefixed_my_table_name`'])->andReturn($columns);

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs([$query]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->ALTER_TABLE('my_table_name')->DROP_COLUMN('id');
    }

    /**
     *
     */
    public function testModifyColumn(): void
    {
        $query = 'ALTER TABLE `prefixed_my_table_name` MODIFY COLUMN `id` INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT';

        $columns =
        [
            [
                'Field'   => 'id',
                'Type'    => 'int(11)',
                'Null'    => 'NO',
                'Key'     => 'PRI',
                'Default' => 'NULL',
                'Extra'   => 'auto_increment',
            ],
        ];

        $connectionHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('query')->withArgs(['SHOW COLUMNS FROM `prefixed_my_table_name`'])->andReturn($columns);

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs([$query]);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->ALTER_TABLE('my_table_name')->MODIFY_COLUMN('id', 'INTEGER | UNSIGNED | PRIMARY KEY | UNIQUE | AUTO INCREMENT');
    }

    /**
     *
     */
    public function testAddPrimaryKey(): void
    {
        $columns =
        [
            [
                'Field'   => 'id',
                'Type'    => 'int(11)',
                'Null'    => 'NO',
                'Key'     => 'PRI',
                'Default' => 'NULL',
                'Extra'   => 'auto_increment',
            ],
        ];

        $connectionHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs(['SHOW COLUMNS FROM `prefixed_my_table_name`'])->andReturn($columns);

        $connectionHandler->shouldReceive('query')->withArgs(['SHOW KEYS FROM `prefixed_my_table_name` WHERE Key_name = \'PRIMARY\''])->andReturn([['Column_name' => 'id']]);

        $connectionHandler->shouldReceive('query')->withArgs(['ALTER TABLE `prefixed_my_table_name` MODIFY COLUMN `id` INT(11) NOT NULL UNIQUE, DROP PRIMARY KEY']);

        $connectionHandler->shouldReceive('query')->withArgs(['ALTER TABLE `prefixed_my_table_name` MODIFY COLUMN `id` INT(11) NOT NULL UNIQUE PRIMARY KEY']);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->ALTER_TABLE('my_table_name')->MODIFY_COLUMN('id')->ADD_PRIMARY_KEY();
    }

    /**
     *
     */
    public function testDropPrimaryKey(): void
    {
        $columns =
        [
            [
                'Field'   => 'id',
                'Type'    => 'int(11)',
                'Null'    => 'NO',
                'Key'     => 'PRI',
                'Default' => 'NULL',
                'Extra'   => 'auto_increment',
            ],
        ];

        $connectionHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs(['SHOW COLUMNS FROM `prefixed_my_table_name`'])->andReturn($columns);

        $connectionHandler->shouldReceive('query')->withArgs(['SHOW KEYS FROM `prefixed_my_table_name` WHERE Key_name = \'PRIMARY\''])->andReturn([['Column_name' => 'id']]);

        $connectionHandler->shouldReceive('query')->withArgs(['ALTER TABLE `prefixed_my_table_name` DROP PRIMARY KEY']);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->ALTER_TABLE('my_table_name')->MODIFY_COLUMN('id')->DROP_PRIMARY_KEY();
    }

    /**
     *
     */
    public function testAddNotNull(): void
    {
        $columns =
        [
            [
                'Field'   => 'id',
                'Type'    => 'int(11)',
                'Null'    => '',
                'Key'     => 'PRI',
                'Default' => 'NULL',
                'Extra'   => 'auto_increment',
            ],
        ];

        $connectionHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs(['SHOW COLUMNS FROM `prefixed_my_table_name`'])->andReturn($columns);

        $connectionHandler->shouldReceive('query')->withArgs(['SHOW KEYS FROM `prefixed_my_table_name` WHERE Key_name = \'PRIMARY\''])->andReturn([['Column_name' => 'id']]);

        $connectionHandler->shouldReceive('query')->withArgs(['UPDATE `prefixed_my_table_name` SET `id` = :not_null WHERE `id` IS NULL', ['not_null' => 0]]);

        $connectionHandler->shouldReceive('query')->withArgs(['ALTER TABLE `prefixed_my_table_name` MODIFY COLUMN `id` int(11) DEFAULT 0 AUTO_INCREMENT NOT NULL']);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->ALTER_TABLE('my_table_name')->MODIFY_COLUMN('id')->ADD_NOT_NULL();
    }

    /**
     *
     */
    public function testDropNotNull(): void
    {
        $columns =
        [
            [
                'Field'   => 'id',
                'Type'    => 'int(11)',
                'Null'    => 'NO',
                'Key'     => 'PRI',
                'Default' => 'NULL',
                'Extra'   => 'auto_increment',
            ],
        ];

        $connectionHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs(['SHOW COLUMNS FROM `prefixed_my_table_name`'])->andReturn($columns);

        $connectionHandler->shouldReceive('query')->withArgs(['SHOW KEYS FROM `prefixed_my_table_name` WHERE Key_name = \'PRIMARY\''])->andReturn([['Column_name' => 'id']]);

        $connectionHandler->shouldReceive('query')->withArgs(['ALTER TABLE `prefixed_my_table_name` MODIFY COLUMN `id` int(11) DEFAULT NULL AUTO_INCREMENT']);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->ALTER_TABLE('my_table_name')->MODIFY_COLUMN('id')->DROP_NOT_NULL();
    }

    /**
     *
     */
    public function testAddUnsigned(): void
    {
        $columns =
        [
            [
                'Field'   => 'id',
                'Type'    => 'int(11)',
                'Null'    => 'NO',
                'Key'     => 'PRI',
                'Default' => 'NULL',
                'Extra'   => 'auto_increment',
            ],
        ];

        $connectionHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs(['SHOW COLUMNS FROM `prefixed_my_table_name`'])->andReturn($columns);

        $connectionHandler->shouldReceive('query')->withArgs(['SHOW KEYS FROM `prefixed_my_table_name` WHERE Key_name = \'PRIMARY\''])->andReturn([['Column_name' => 'id']]);

        $connectionHandler->shouldReceive('query')->withArgs(['ALTER TABLE `prefixed_my_table_name` MODIFY COLUMN `id` int(11) UNSIGNED DEFAULT NULL NOT NULL AUTO_INCREMENT']);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->ALTER_TABLE('my_table_name')->MODIFY_COLUMN('id')->ADD_UNSIGNED();
    }

    /**
     *
     */
    public function testDropUnsigned(): void
    {
        $columns =
        [
            [
                'Field'   => 'id',
                'Type'    => 'int(11)',
                'Null'    => 'NO',
                'Key'     => 'PRI',
                'Default' => 'NULL',
                'Extra'   => 'auto_increment unsigned',
            ],
        ];

        $connectionHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs(['SHOW COLUMNS FROM `prefixed_my_table_name`'])->andReturn($columns);

        $connectionHandler->shouldReceive('query')->withArgs(['SHOW KEYS FROM `prefixed_my_table_name` WHERE Key_name = \'PRIMARY\''])->andReturn([['Column_name' => 'id']]);

        $connectionHandler->shouldReceive('query')->withArgs(['ALTER TABLE `prefixed_my_table_name` MODIFY COLUMN `id` int(11) DEFAULT NULL NOT NULL AUTO_INCREMENT']);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->ALTER_TABLE('my_table_name')->MODIFY_COLUMN('id')->DROP_UNSIGNED();
    }

    /**
     *
     */
    public function testSetAutoIncrement(): void
    {
        $columns =
        [
            [
                'Field'   => 'id',
                'Type'    => 'int(11)',
                'Null'    => 'NO',
                'Key'     => '',
                'Default' => 'NULL',
                'Extra'   => 'unsigned',
            ],
        ];

        $connectionHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs(['SHOW COLUMNS FROM `prefixed_my_table_name`'])->andReturn($columns);

        $connectionHandler->shouldReceive('query')->withArgs(['SHOW KEYS FROM `prefixed_my_table_name` WHERE Key_name = \'PRIMARY\''])->andReturn([]);

        $connectionHandler->shouldReceive('query')->withArgs(['ALTER TABLE `prefixed_my_table_name` MODIFY COLUMN `id` INT NOT NULL AUTO_INCREMENT UNIQUE PRIMARY KEY']);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->ALTER_TABLE('my_table_name')->MODIFY_COLUMN('id')->SET_AUTO_INCREMENT();
    }

    /**
     *
     */
    public function testDropAutoIncrement(): void
    {
        $columns =
        [
            [
                'Field'   => 'id',
                'Type'    => 'int(11)',
                'Null'    => 'NO',
                'Key'     => 'PRI',
                'Default' => 'NULL',
                'Extra'   => 'auto_increment unsigned',
            ],
        ];

        $connectionHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs(['SHOW COLUMNS FROM `prefixed_my_table_name`'])->andReturn($columns);

        $connectionHandler->shouldReceive('query')->withArgs(['SHOW KEYS FROM `prefixed_my_table_name` WHERE Key_name = \'PRIMARY\''])->andReturn([]);

        $connectionHandler->shouldReceive('query')->withArgs(['ALTER TABLE `prefixed_my_table_name` MODIFY COLUMN `id` INT NOT NULL UNIQUE']);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->ALTER_TABLE('my_table_name')->MODIFY_COLUMN('id')->DROP_AUTO_INCREMENT();
    }

    /**
     *
     */
    public function testSetSetDefault(): void
    {
        $columns =
        [
            [
                'Field'   => 'id',
                'Type'    => 'int(11)',
                'Null'    => 'NO',
                'Key'     => 'PRI',
                'Default' => 'foo',
                'Extra'   => 'auto_increment unsigned',
            ],
        ];

        $connectionHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs(['SHOW COLUMNS FROM `prefixed_my_table_name`'])->andReturn($columns);

        $connectionHandler->shouldReceive('query')->withArgs(['ALTER TABLE `prefixed_my_table_name` ALTER `id` SET DEFAULT bar']);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->ALTER_TABLE('my_table_name')->MODIFY_COLUMN('id')->SET_DEFAULT('bar');
    }

    /**
     *
     */
    public function testDropSetDefault(): void
    {
        $columns =
        [
            [
                'Field'   => 'id',
                'Type'    => 'int(11)',
                'Null'    => 'NO',
                'Key'     => 'PRI',
                'Default' => 'foo',
                'Extra'   => 'auto_increment unsigned',
            ],
        ];

        $connectionHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs(['SHOW COLUMNS FROM `prefixed_my_table_name`'])->andReturn($columns);

        $connectionHandler->shouldReceive('query')->withArgs(['ALTER TABLE `prefixed_my_table_name` MODIFY COLUMN `id` int(11) NOT NULL AUTO_INCREMENT UNSIGNED']);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->ALTER_TABLE('my_table_name')->MODIFY_COLUMN('id')->DROP_DEFAULT();
    }

    /**
     *
     */
    public function testAddUnique(): void
    {
         $columns =
        [
            [
                'Field'   => 'id',
                'Type'    => 'int(11)',
                'Null'    => 'NO',
                'Key'     => 'UNI',
                'Default' => 'foo',
                'Extra'   => 'auto_increment unsigned',
            ],
        ];

        $connectionHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs(['SHOW COLUMNS FROM `prefixed_my_table_name`'])->andReturn($columns);

        $connectionHandler->shouldReceive('query')->withArgs(['ALTER TABLE `prefixed_my_table_name` MODIFY COLUMN `id` int(11) DEFAULT foo NOT NULL UNIQUE AUTO_INCREMENT UNSIGNED']);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->ALTER_TABLE('my_table_name')->MODIFY_COLUMN('id')->ADD_UNIQUE();
    }

    /**
     *
     */
    public function testAddFrogeinKey(): void
    {
        $columns =
        [
            [
                'Field'   => 'id',
                'Type'    => 'int(11)',
                'Null'    => 'NO',
                'Key'     => 'UNI',
                'Default' => 'foo',
                'Extra'   => 'auto_increment unsigned',
            ],
        ];

        $connectionHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs(['SHOW COLUMNS FROM `prefixed_my_table_name`'])->andReturn($columns);

        $connectionHandler->shouldReceive('query')->withArgs(['ALTER TABLE `prefixed_my_table_name` ADD CONSTRAINT `fk_id_toTable_tes_fromT_my__onCol_id` FOREIGN KEY (`id`) REFERENCES prefixed_test_table(`id`)']);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->ALTER_TABLE('my_table_name')->MODIFY_COLUMN('id')->ADD_FOREIGN_KEY('test_table', 'id');
    }

    /**
     *
     */
    public function testDropFrogeinKey(): void
    {
        $columns =
        [
            [
                'Field'   => 'id',
                'Type'    => 'int(11)',
                'Null'    => 'NO',
                'Key'     => 'UNI',
                'Default' => 'foo',
                'Extra'   => 'auto_increment unsigned',
            ],
        ];

        $connectionHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs(['SHOW COLUMNS FROM `prefixed_my_table_name`'])->andReturn($columns);

        $connectionHandler->shouldReceive('query')->withArgs(['ALTER TABLE `prefixed_my_table_name` DROP FOREIGN KEY `fk_id_toTable_tes_fromT_my__onCol_id`']);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->ALTER_TABLE('my_table_name')->MODIFY_COLUMN('id')->DROP_FOREIGN_KEY('test_table', 'id');
    }

    /**
     *
     */
    public function testChainable(): void
    {
        $columns =
        [
            [
                'Field'   => 'id',
                'Type'    => 'int(11)',
                'Null'    => '',
                'Key'     => 'PRI',
                'Default' => 'foo',
                'Extra'   => 'auto_increment',
            ],
        ];

        $connectionHandler = $this->mock('\kanso\framework\database\connection\ConnectionHandler');

        $connectionHandler->shouldReceive('tablePrefix')->andReturn('prefixed_');

        $connectionHandler->shouldReceive('cleanQuery')->andReturnUsing(function($sql)
        {
            return trim(preg_replace('/\s+/', ' ', $sql));
        });

        $connectionHandler->shouldReceive('query')->withArgs(['SHOW COLUMNS FROM `prefixed_my_table_name`'])->andReturn($columns);

        $connectionHandler->shouldReceive('query')->withArgs(['ALTER TABLE `prefixed_my_table_name` MODIFY COLUMN `id` int(11) UNSIGNED DEFAULT foo AUTO_INCREMENT']);

        $connectionHandler->shouldReceive('query')->withArgs(['ALTER TABLE `prefixed_my_table_name` MODIFY COLUMN `id` int(11) NOT NULL DEFAULT foo AUTO_INCREMENT']);

        $connectionHandler->shouldReceive('query')->withArgs(['UPDATE `prefixed_my_table_name` SET `id` = :not_null WHERE `id` IS NULL', ['not_null' => 0]]);

        $connectionHandler->shouldReceive('query')->withArgs(['ALTER TABLE `prefixed_my_table_name` MODIFY COLUMN `id` int(11) DEFAULT foo AUTO_INCREMENT NOT NULL']);

        $connectionHandler->shouldReceive('query')->withArgs(['ALTER TABLE `prefixed_my_table_name` ADD CONSTRAINT `fk_id_toTable_tes_fromT_my__onCol_id` FOREIGN KEY (`id`) REFERENCES prefixed_test_table(`id`)']);

        $sql = new Builder($connectionHandler, new Query($connectionHandler));

        $sql->ALTER_TABLE('my_table_name')->MODIFY_COLUMN('id')->ADD_UNSIGNED()->ADD_NOT_NULL()->ADD_FOREIGN_KEY('test_table', 'id');
    }

}
