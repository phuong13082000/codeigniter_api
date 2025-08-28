<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBookings extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'room_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'check_in' => ['type' => 'DATE'],
            'check_out' => ['type' => 'DATE'],
            'guests' => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'status' => ['type' => 'ENUM("pending","confirmed","cancelled")', 'default' => 'pending'],
            'total_price' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => '0.00'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('room_id', 'rooms', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('bookings');
    }

    public function down()
    {
        $this->forge->dropTable('bookings');
    }
}
