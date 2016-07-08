<?php

namespace Creonit\PropelTreeBehavior;

use Propel\Generator\Model\Behavior;
use Propel\Generator\Model\ForeignKey;

class TreeBehavior extends Behavior
{
    public function modifyTable()
    {
        $table = $this->getTable();

        $table->addColumn([
            'name' => 'path',
            'type' => 'varchar',
            'size' => 255
        ]);

        $table->addColumn([
            'name' => 'level',
            'type' => 'integer',
        ]);

        $table->addColumn([
            'name' => 'parent_id',
            'type' => 'integer',
            'require' => false,
        ]);


        $fk = new ForeignKey();
        $fk->setForeignTableCommonName($table->getCommonName());
        $fk->setForeignSchemaName($table->getSchema());
        $fk->setDefaultJoin('LEFT JOIN');
        $fk->setOnDelete(ForeignKey::CASCADE);
        $fk->setOnUpdate(ForeignKey::CASCADE);
        $fk->addReference('parent_id', 'id');
        $table->addForeignKey($fk);

       
    }

    public function objectMethods()
    {
        return $this->renderTemplate('objectMethods', ['table' => $this->getTable()]);
    }

    public function objectAttributes()
    {
        return $this->renderTemplate('objectAttributes');
    }
    

    public function preSave(){
        return $this->renderTemplate('preSave', ['table' => $this->getTable()]);
    }
}