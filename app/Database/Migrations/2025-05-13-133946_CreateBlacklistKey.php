<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBlacklistKey extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_blacklist' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
                'null' => true,
            ],
            'key' => [
                'type' => 'TEXT'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ]
        ]);
        $this->forge->addPrimaryKey('id_blacklist');
        $this->forge->createTable('blacklist_key');
    }

    public function down()
    {
        $this->forge->dropTable('blacklist_key');
    }
}
