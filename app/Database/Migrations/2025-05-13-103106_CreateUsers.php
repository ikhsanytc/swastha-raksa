<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsers extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'uid' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'password' => [
                'type' => 'TEXT',
            ],
            'profile_picture' => [
                'type' => 'TEXT',
                'null' => true,
                'default' => 'nophoto.jpg'
            ],
            'tipe_akun' => [
                'type' => 'ENUM',
                'constraint' => ['Pembeli', 'Penjual'],
                'default' => 'Pembeli',
            ],
            'data_toko' => [
                'type' => 'JSON',
                'null' => true,
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
        $this->forge->addKey('uid', true);
        $this->forge->addUniqueKey('username');
        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}
