<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProducts extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'product_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'owner_uid' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'nama_product' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'jenis_product' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'harga_product' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'stok_product' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('product_id', true);
        $this->forge->addForeignKey('owner_uid', 'users', 'uid', 'CASCADE', 'CASCADE');
        $this->forge->createTable('products');
    }

    public function down()
    {
        $this->forge->dropTable('products');
    }
}
