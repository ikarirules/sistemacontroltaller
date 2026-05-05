<?php

use yii\db\Migration;

class m260505_000001_create_cierre_tables extends Migration
{
    public function safeUp()
    {
        $this->createTable('cierre', [
            'id'            => $this->primaryKey(),
            'fecha_desde'   => $this->date()->notNull(),
            'fecha_hasta'   => $this->date()->notNull(),
            'saldo_sistema' => $this->integer()->notNull()->defaultValue(0),
            'saldo_real'    => $this->integer()->notNull()->defaultValue(0),
            'diferencia'    => $this->integer()->notNull()->defaultValue(0),
            'observaciones' => $this->string(500)->null(),
            'created_at'    => $this->dateTime()->notNull(),
        ]);

        $this->createTable('cierre_plataforma', [
            'id'         => $this->primaryKey(),
            'id_cierre'  => $this->integer()->notNull(),
            'plataforma' => $this->string(50)->notNull(),
            'monto'      => $this->integer()->notNull()->defaultValue(0),
        ]);

        $this->addForeignKey(
            'fk_cierre_plataforma_cierre',
            'cierre_plataforma',
            'id_cierre',
            'cierre',
            'id',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_cierre_plataforma_cierre', 'cierre_plataforma');
        $this->dropTable('cierre_plataforma');
        $this->dropTable('cierre');
    }
}
