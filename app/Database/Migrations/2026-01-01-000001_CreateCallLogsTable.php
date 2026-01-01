<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCallLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'call_sid' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'phone_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'lead_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['initiated', 'ringing', 'in-progress', 'completed', 'failed', 'busy', 'no-answer'],
                'default' => 'initiated',
            ],
            'duration' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'conversation_summary' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'lead_interested' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'lead_email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
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

        $this->forge->addKey('id', true);
        $this->forge->addKey('call_sid');
        $this->forge->addKey('phone_number');
        $this->forge->createTable('call_logs');
    }

    public function down()
    {
        $this->forge->dropTable('call_logs');
    }
}
