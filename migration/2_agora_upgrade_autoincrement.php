<?php
/**
 * Adds autoincrement flags.
 *
 * Copyright 2011-2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (GPL). If you
 * did not receive this file, see http://www.horde.org/licenses/gpl.
 *
 * @author   Vilius Šumskas <vilius@lnk.lt>
 * @category Horde
 * @license  http://www.horde.org/licenses/gpl GPL
 * @package  Agora
 */
class AgoraUpgradeAutoIncrement extends Horde_Db_Migration_Base
{
    /**
     * Upgrade.
     */
    public function up()
    {
        $this->changeColumn('agora_files', 'file_id', 'autoincrementKey');
        if (in_array('agora_files_seq', $this->tables())) {
            $this->dropTable('agora_files_seq');
        }

        $this->changeColumn('agora_forums', 'forum_id', 'autoincrementKey');
        if (in_array('agora_forums_seq', $this->tables())) {
            $this->dropTable('agora_forums_seq');
        }

        $this->changeColumn('agora_messages', 'message_id', 'autoincrementKey');
        if (in_array('agora_messages_seq', $this->tables())) {
            $this->dropTable('agora_messages_seq');
        }
    }

    /**
     * Downgrade
     */
    public function down()
    {
        $this->changeColumn('agora_files', 'file_id', 'integer', array('autoincrement' => false));
        $this->changeColumn('agora_forums', 'forum_id', 'integer', array('autoincrement' => false));
        $this->changeColumn('agora_messages', 'message_id', 'integer', array('autoincrement' => false));
    }

}
