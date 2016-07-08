public function getParent()
{
    return $this->get<?php echo $table->getPhpName(); ?>RelatedByParentId();
}

public function getParents($mode = 1)
{
    if(!$this->parent_id){
        if($mode & self::INCLUDE_SELF){
            if($mode & self::PARENT_PKS){
                return [$this->id];
            }else{
                return [$this];
            }
        }

        return [];
    }

    if($mode & self::PARENT_PKS){

        $pks = explode('/', trim($this->path, '/'));
        if($mode & self::INCLUDE_SELF){
            $pks[] = $this->id;
        }
        ~$mode & self::PARENT_REVERSE && $pks = array_reverse($pks);

        return $pks;

    }else{

        Child<?php echo $table->getPhpName(); ?>Query::create()->findById($pks = $this->getParents($mode | self::PARENT_PKS));

        $objects = [];
        foreach($pks as $pk){
            $objects[] = Child<?php echo $table->getPhpName(); ?>Query::create()->findPk($pk);
        }
        return $objects;

    }

}

public function getChildren($level = 0, $criteria = null)
{

    if($level instanceof Criteria){
        $criteria = $level;
        $level = 0;
    }

    $query = $this->getChildrenQuery($level);


    if(null !== $criteria){
        $query->mergeWith($criteria);
    }

    return $query->find();
}

public function getChildrenQuery($level = 0)
{
    $query = new Child<?php echo $table->getPhpName(); ?>Query;

    if ($level < 1){
        $query->filterByPath("{$this->getSelfPath()}%", ModelCriteria::LIKE)->find();

    } else if ($level > 1){
        $path = '^' . $this->getSelfPath() . '([0-9]+/){0,'.($level-1).'}$';
        $query->filterByPath($path, ' REGEXP ')->find();

    } else {
        $query->findByParentId($this->id);
    }

    return $query;
}




public function fixPath()
{
    $path = $this->parent_id ? $this->getParent()->getSelfPath() : '/';
    if(!$this->isNew()){
        $self = $this->getSelfPath();
        $size = mb_strlen($self);
        $connection = Propel::getConnection();
        $connection->exec("
				UPDATE `<?php echo $table->getCommonName(); ?>`
				SET
					`path` = CONCAT('$path{$this->id}', SUBSTRING(`path`, $size)),
					`level` = LENGTH(`path`)-LENGTH(REPLACE(`path`, '/', ''))-1

				WHERE `path` LIKE '$self%'
			");
    }
    $this->setLevel(substr_count($path, '/') - 1);
    $this->setPath($path);
}

public function getSelfPath()
{
    return $this->path . $this->id . '/';
}