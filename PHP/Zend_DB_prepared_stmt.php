private function insertExternalInventory($records)
	{
		$params = $records;
		$count = count($records);
	
		$db = Zend_Db_Table_Abstract::getDefaultAdapter();
		
		//do 2000 inserts at a time for speed
		$counter = 1;
		$index = 0;
		$number = 2000;
		$times = (int)($count/2000);
		$remainder = $count % 2000;
		if ($remainder > 0) {
			$times++;
		}
			
		while ($counter <= $times) {
			
			if ($counter == $times) {
				$x = $remainder;
			} else {
				$x = 2000;
			}
			
			$query = "INSERT INTO inventory_detail_external (
					partNumber, 
					ownerId, 
					clei, 
					eci, 
					`category`, 
					qty, 
					description, 
					`condition`, 
					manufacturer,
					Series,
					importFileId
					) VALUES ";
			
			
			
			for ($i = 0; $i<$x; $i++) {
				$clei 		 = $params[$index]['clei'];
				$vendorId    = $params[$index]['ownerId'];
				$partNumber  = $params[$index]['partNumber'];
				$eci 	     = $params[$index]['eci'];
				$category    = (!empty($params[$index]['category'])) ? $params[$index]['category'] : 0;
				$qty	     = (!empty($params[$index]['qty'])) ? $params[$index]['qty'] : 1;
				$desc 		 = $params[$index]['description'];
				$condition   = $params[$index]['condition'];
				$mfc		 = $params[$index]['manufacturer'];
				$series		 = $params[$index]['Series'];
				$importFileId = $params[$index]['importFileId'];
				$query .= "(".$db->quote($partNumber).", ".$db->quote($vendorId).", ".$db->quote($clei).", ".$db->quote($eci).", ".$category.", ".$qty.", ".$db->quote($desc).", ".$db->quote($condition).", ".$db->quote($mfc).", ".$db->quote($series).", ".$importFileId.")";

				if ($i < ($x-1)) {
					$query .= ', ';
				}
				
				$index++;
			}
			
			//echo $query;exit;
			$stmt = $db->prepare($query);
			$stmt->execute();
		
			$counter++;
		}
	}