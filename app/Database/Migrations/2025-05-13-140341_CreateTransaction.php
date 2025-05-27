<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransaction extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_transaction' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'seller_uid' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ],
            'buyer_uid' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ],
            'transaction_time' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'product_data' => [
                'type' => 'JSON',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ]
        ]);
        $this->forge->addPrimaryKey('id_transaction');
        $this->forge->createTable('transaction');
    }

    public function down()
    {
        $this->forge->dropTable('transaction');
    }
}
