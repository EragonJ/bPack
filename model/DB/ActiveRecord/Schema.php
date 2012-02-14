<?php

class bPack_DB_ActiveRecord_Schema
{
	public function getSchema()
	{
		$database_backend  = $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);

		if($database_backend == 'sqlite')
		{
			$schema_sql = '';

			$schema_sql .= "CREATE TABLE `{$this->table_name}` ";

			$field_schema = array();

			foreach($this->table_column as $col => $col_info)
			{
				if(isset($col_info['tag']) && (strpos( $col_info['tag'],'primary_key') !== FALSE))
				{
					$col_type = 'INTEGER PRIMARY KEY';
				}
				else
				{
					$col_type = '';
				}

				$field_schema[] = " $col $col_type";
			}

			$schema_sql .= "(".implode(',', $field_schema).");";
		}
		else
		{
			#mysql

			$schema_sql = "CREATE TABLE  IF NOT EXISTS `{$this->table_name}` ";
			$index_sql = '';

			$field_schema = array();
			foreach($this->table_column as $col => $col_info)
			{
				if(isset($col_info['tag']) && (strpos( $col_info['tag'],'primary_key') !== FALSE))
				{
					$col_type = 'int(20) unsigned not null AUTO_INCREMENT';
					$index_sql .= ',PRIMARY KEY (`'.$col.'`)';
				}
				else
				{
					if(strpos(strtolower($col_info['type']),'int') === FALSE)
					{
						$col_type = $col_info['type'];
					}
					else
					{
						$col_type = $col_info['type'] . ' unsigned';
					}
				}

				$field_schema[] = " `$col` $col_type NOT NULL";
			}

			$schema_sql .= "(".implode(',', $field_schema).' ' .$index_sql.")  ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
		}
		
		return $schema_sql;
	} 

	public function destroySchema()
	{
		return $this->connection->exec('DROP TABLE `'.$this->table_name.'`;');
	}

	public function createSchema()
	{
		return $this->connection->exec($this->getSchema());
	}
}
