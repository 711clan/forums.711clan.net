<?php

class v3Arcade_Database_Analyse
{
	// Data Dictionary
	var $dd;
	
	// Contains all the current tables in the database.
	var $tablecache = array();
	
	function v3Arcade_Database_Analyse(&$dd)
	{
		$this->dd = $dd;
	}
	
	// Add all tables that are in the data dictionary, but not in the database.
	function addtables()
	{
		global $db;
		
		foreach ($this->dd as $key => $val)
		{
			if (!$this->tablecache[TABLE_PREFIX . $key])
			{
				$db->query_write($this->createtable($key));
			}
		}
		$this->cachetables();
	}
	
	// Create a new table based on the contents of the data dictionary.
	function createtable($newtable)
	{
		$newsql = "CREATE TABLE " . TABLE_PREFIX . $newtable . " (";
		
		foreach ($this->dd[$newtable]['fields'] as $fieldname => $field)
		{
			$newsql .= "$fieldname $field[type]" . iif($field['length'], "($field[length])");
			$newsql .= iif ($field['unsigned'], " unsigned");
			$newsql .= iif ($field['notnull'], " NOT NULL");
			$newsql .= iif ($field['autoincrement'], " auto_increment");
			$newsql .= iif ($field['default'], " default '$field[default]'");
			$newsql .= ', ';
		}
		
		$newsql .= 'PRIMARY KEY (' . $this->dd[$newtable]['key'] . '))';
	
		return $newsql;
	}
	
	// Cache the table names in the current database.
	function cachetables()
	{
		global $db;
		
		$tables = $db->query("SHOW TABLES");
		while (list($name) = $db->fetch_row($tables))
		{
			$this->tablecache[$name] = true;
		}
	}
	
	function syncfields()
	{
		global $db;
		
		foreach ($this->dd as $key => $val)
		{
			if ($this->tablecache[TABLE_PREFIX . $key])
			{
				$fields = $db->query("SHOW COLUMNS FROM " . TABLE_PREFIX . $key);
				while ($field = $db->fetch_row($fields))
				{
					if (!$val['fields'][$field[0]])
					{
						// This field was removed from the data dictionary, so remove it from the database.
						// $db->query_write("ALTER TABLE " . TABLE_PREFIX . $key . " DROP $field[0]");
						// Commented out, because we're adding fields to tables that have fields not included in $dd.
					} else {
						// The field is in both the data dictionary and the database, so compare.
						
						// Check type/length/unsigned
						if (strtolower($field[1])!= strtolower($val['fields'][$field[0]]['type'] . iif($val['fields'][$field[0]]['length'], '(' . $val['fields'][$field[0]]['length'] . ')') . iif ($val['fields'][$field[0]]['unsigned'], " unsigned")))
						{
							$field['update']=true;
						}
												
						// Null
						if ($field[2]=='YES' AND $val['fields'][$field[0]]['notnull']==1)
						{
							$field['update']=true;
						}
						
						// Null Reverse
						if ($field[2]=='NO' AND $val['fields'][$field[0]]['notnull']==0)
						{
							$field['update']=true;
						}
						
						// Default
						if ($field[4] != $val['fields'][$field[0]]['default'])
						{
							$field['update']=true;
						}
						
						// Primary key
						if ($field[3]=='PRI')
						{
							$primarykey = $field[0];
						}
						
						// Autoincrement
						if ($field[5]=='auto_increment')
						{
							$field['update']=true;
						}

						if ($field['update']==true)
						{
							$db->query_write("ALTER TABLE " . TABLE_PREFIX . $key . " CHANGE $field[0] $field[0] " . $val['fields'][$field[0]]['type'] . iif($val['fields'][$field[0]]['length'], '(' . $val['fields'][$field[0]]['length'] . ')') . iif ($val['fields'][$field[0]]['unsigned'], " unsigned") . iif ($val['fields'][$field[0]]['notnull'], " NOT NULL") . iif ($val['fields'][$field[0]]['default'], " default '" . $val['fields'][$field[0]]['default'] . "'") . iif ($val['fields'][$field[0]]['default'], " default '" . $val['fields'][$field[0]]['default'] . "'") . iif ($val['fields'][$field[0]]['autoincrement'], " auto_increment"));
						}
						
					}				
					
					unset($val['fields'][$field[0]]);
				}
			}

			foreach((array)$val['fields'] as $newfield => $field)
			{
				$newsql = "ALTER TABLE " . TABLE_PREFIX . "$key ADD";
				$newsql .= " $newfield $field[type]" . iif($field['length'], "($field[length])");
				$newsql .= iif ($field['unsigned'], " unsigned");
				$newsql .= iif ($field['notnull'], " NOT NULL");
				$newsql .= iif ($field['autoincrement'], " auto_increment");
				$newsql .= iif ($field['default'], " default '$field[default]'");
				$db->query_write($newsql);
			}
	
		}
	}
	
}

?>