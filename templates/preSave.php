if($this->isNew() or $this->isColumnModified('<?php echo $table->getCommonName(); ?>.parent_id')){
    $this->fixPath();
}
