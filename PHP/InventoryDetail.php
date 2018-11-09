<?php
set_time_limit(0);

class Csg_Model_InventoryDetail extends Zend_Db_Table_Abstract
{
	/**
	 * The default table name
	 */
	protected $_name = 'inventory_detail';
	protected $_dependentTables = array('Csg_Model_PO');
	protected $db;
	
	public function getOpenInventoryByCustomer($cId) 
	{
		/* 
		SELECT inventory_detail.*, po.*
		  FROM    csg.inventory_detail inventory_detail
		       INNER JOIN
		          csg.po po
		       ON (inventory_detail.po_number = po.po_number)
		 WHERE po.status = 'open' AND po.cust_id = '23'
		*/
		
		$idModel = new self();
		$db = $idModel->getAdapter();
		
		$where1 = $db->quoteInto('inventory_detail.cusNumber = ?', $cId);
		
		$sql = "SELECT inventory_detail.*, po.* FROM inventory_detail inventory_detail
						INNER JOIN
						po po
						ON (inventory_detail.po_number = po.po_number)
						WHERE $where1 AND po.status = 'open'";
		
		//$stmt = $db->prepare($sql);
		//$result = $stmt->execute(array($cId,'open'));
		$stmt = $db->query($sql);
		$inventory = array();
		
		while ($row = $stmt->fetch()) {
			$inventory[] = $row;
		}
		
		return $inventory;		
	}

	public function getInventoryPage($sortname, $sortorder, $query)
	{
		$data = array();
		$sort = $sortname . " " . $sortorder;
		
		//set where clause
		if (isset($query) && $query != false && !is_null($query)) {
			$where = $query . ' AND deleted = 0 AND auctionEnded = 0';
		} else {
			$where = 'deleted = 0 AND auctionEnded = 0';
		}
		
		//L::VD($where);exit;

		$inventoryModel = new self();
		$select = $inventoryModel->select();
		
		if (!is_null($where)) {
			$resultRows = $inventoryModel->fetchAll(
					$inventoryModel->select()
						->where($where)
						->order($sort)
					);
		} else {
			$resultRows = $inventoryModel->fetchAll(
					$inventoryModel->select()
					->order($sort)
			);
		}
		
		foreach ($resultRows as $row) {
			
			$data[] = array('edit' => "<a href=\"#\" name=\"". $row['id_id'] ."\" id=\"inventory_editLink\"><img style=\"BORDER-BOTTOM: 0px; BORDER-LEFT: 0px; BORDER-TOP: 0px; BORDER-RIGHT: 0px\" border=\"0\" hspace=\"0\" alt=\"\" align=\"middle\" src=\"../images/001_45.png\"></a>",//$row['id_id'],
							'partNumber' => $row['partNumber'],
							'description' => $row['description'],
							'po_number' => $row['po_number'],
							'location_id' => $row['location_id'],
							'serial_number' => $row['serial_number'],
							'qty' => $row['qty'],
							'clei' => $row['clei'],
							'eci' => $row['eci']
					);
		}
		
		return $data;
	}
	
	public function getSearchInventory($sortname = null, $sortorder = null, $query)
	{
		$data = array();
		$sort = $sortname . " " . $sortorder;
		
		$sh_by_pn = Csg_Model_SalesHistory::getAllByPN();
		$sh_by_clei = Csg_Model_SalesHistory::getAllByClei();
		
		//set where clause
		if (isset($query) && $query != false && !is_null($query)) {
			$where = $query . ' AND deleted = 0';
		} else {
			$where = 'deleted = 0';
		}
		
		$inventoryModel = new self();
		$db = $inventoryModel->getAdapter();
		$select = $inventoryModel->select();
		
		if (!is_null($where)) {
			$resultRows = $inventoryModel->fetchAll(
					$inventoryModel->select()
						->where($where)
						->order($sort)
					);
		} else {
			$resultRows = $inventoryModel->fetchAll(
					$inventoryModel->select()
					->order($sort)
			);
		}

		$poNums = array();
		foreach ($resultRows as $row) {
			
			if (!isset($poNums[$row['po_number']])) {
				$result = $row->findDependentRowset('Csg_Model_PO')->toArray();
				$poNums[$row['po_number']] = $result[0]['consignment'];
			}
			
			if (empty($row['auctionId'])) {
				$auctionLink = "<a id=\"auctionLink_1\" name=\"". $row['id_id'] ."\" href=\"#\">Auction<br>Item</a>";
				$lotLink = "<a id=\"lotLink_1\" name=\"". $row['id_id'] ."\" href=\"#\">Add to<br>Lot</a>";
			} else {
				if ($row['auctionEnded'] == 1) {
					$auctionLink = 'Sold';
					$lotLink = 'Sold';
				} else {
					$auctionLink = 'Listed';
					$lotLink = 'Listed';
				}
			}
			
			if (isset($sh_by_clei[$row['clei']])) {
				$sh = $sh_by_clei[$row['clei']]['unit_price'];
			} elseif (isset($sh_by_pn[$row['partNumber']])) {
				$sh = $sh_by_pn[$row['partNumber']]['unit_price'];
			} else {
				$sh = 0;
			}
			
			$data[] = array('auction' => $auctionLink,
							'lot' => $lotLink,
							'poNumber' => $row['po_number'],
							'auctionId' => $row['auctionId'],
							'w' => "<a id=\"inventory1ImageLink4_1\" name=\"". $row['id_id'] ."\" href=\"#\"><img src=\"/images/printer-blue.png\" style=\"BORDER-BOTTOM: 0px; BORDER-LEFT: 0px; BORDER-TOP: 0px; BORDER-RIGHT: 0px\"></a>",
							//'d' => "<a id=\"inventory1ImageLink3_1\" name=\"". $row['id_id'] ."\" href=\"#\"><img src=\"/images/001_45.png\" style=\"BORDER-BOTTOM: 0px; BORDER-LEFT: 0px; BORDER-TOP: 0px; BORDER-RIGHT: 0px\"></a>",
							's' => "<a id=\"inventory1ImageLink2_1\" name=\"". $row['id_id'] ."\" href=\"/search/sales-history/id/". $row['id_id'] ."\" target=\"_blank\"><img src=\"/images/US-dollar.png\" style=\"BORDER-BOTTOM: 0px; BORDER-LEFT: 0px; BORDER-TOP: 0px; BORDER-RIGHT: 0px\"></a>",
							'p' => "<a id=\"inventory1ImageLink1_2\" name=\"". $row['id_id'] ."\" href=\"/search/purchase-history/id/". $row['id_id'] ."\" target=\"_blank\"><img src=\"/images/001_34.png\" style=\"BORDER-BOTTOM: 0px; BORDER-LEFT: 0px; BORDER-TOP: 0px; BORDER-RIGHT: 0px\"></a>",
							'partNumber' => $row['partNumber'],
							'description' => $row['description'],
							'clei' => $row['clei'],
							'serial_number' => $row['serial_number'],
							'eci' => $row['eci'],
							'qty' => $row['qty'],
							'soldTotal' => $row['qtySold'],
							'mfg' => L::N($row['mfgPrice']),
							'list' => L::N($row['listPrice']),
							'csg' => L::N($row['csgPrice']),
							'cost' => L::N($row['Cost']),
							'sh' => L::N($sh),
							'auctionEnded' => $row['auctionEnded'],
							'consignment' => (isset($poNums[$row['po_number']]['consignment'])) ? $poNums[$row['po_number']]['consignment'] : null,
					);
		}

		return $data;
	}
	
	public function saveInventory($params)
	{
		//set qty
		if (is_null($params['quantity']) || $params['quantity'] == 0) {
			$qty = 1;
		} else {
			$qty = $params['quantity'];
		}
		
		//set PartNumber
		if (isset($params['Pn'])) {
			$partNumber = $params['Pn'];
		} elseif (isset($params['partNumber'])) {
			$partNumber = $params['partNumber'];
		} else {
			$partNumber = null;
		}
		
		$row = $this->createRow();
		
		$row->location_id = $params['location_id'];
		$row->partNumber = $partNumber;
		$row->serial_number = $params['serial_number'];
		$row->po_number = $params['po_number'];
		$row->qty = $qty;
		$row->clei = $params['clei'];
		$row->description = $params['description'];
		$row->entered_by = $this->getCurrentUser();
		$row->entered_date = $this->getCurrentTime();
		$row->changed_date = isset($params['changed_date']) ? $params['changed_date'] : null;
		$row->cusNumber = $params['cusNumber'];
		$row->category = $params['category'];
		$row->Series = isset($params['Series']) ? $params['Series'] : null;
		$row->Cost = isset($params['Cost']) ? $params['Cost'] : null;
		$row->eci = isset($params['eci']) ? $params['eci'] : null;
		$row->lastBuyPrice = isset($params['lastBuyPrice']) ? $params['lastBuyPrice'] : null;
		$row->condition = isset($params['condition']) ? $params['condition'] : null;
		$row->salesMethod = isset($params['salesMethod']) ? $params['salesMethod'] : null;

		//save the row
		$row->save();
	
	}
	
	public function updateInventoryCosts($params)
	{
		$inventoryModel = new self();
		
		$where  = $inventoryModel->getAdapter()->quoteInto('partNumber = ?', $params['partNumber']);
		$where2 = $inventoryModel->getAdapter()->quoteInto('clei = ?', $params['clei']);
		
		$result = $inventoryModel->update($params, $where);
		
		if ($result == 0) {
			$result = $inventoryModel->update($params, $where2);
		}
		
		return true;
	}
	
	public static function getCleis()
	{
		$inventoryModel = new self();
		$select = $inventoryModel->select();
		$select->from($inventoryModel, array('clei'));
		
		$resultRow = $inventoryModel->fetchAll($select);
		
		$rowArray = $resultRow->toArray();
		return $rowArray[0];
	}
	
	public static function getPartNumbers()
	{
		$inventoryModel = new self();
		$select = $inventoryModel->select();
		$select->columns(array('clei'));
		
		$resultRow = $inventoryModel->fetchAll(
				$inventoryModel->select()
			);
		
		$rowArray = $resultRow->toArray();
		return $rowArray[0];
	}
	
	public function getRowById($id)
	{
		$inventoryModel = new self();
		$select = $inventoryModel->select();
		
		$resultRow = $inventoryModel->fetchAll(
			$inventoryModel->select()
				     	   ->where("id_id = $id")
		);
		
		$rowArray = $resultRow->toArray();
		return $rowArray[0];
	}
	
	public function updateInventory($params)
	{
		$inventoryModel = new self();
		
		$data = array(
				'partNumber' => $params['partNumber'],
				'description' => $params['description'],
				'po_number' => $params['po_number'],
				'location_id' => $params['location_id'],
				'serial_number' => $params['serial_number'],
				'qty' => $params['qty'],
				'clei' => $params['clei'],
				'eci' => $params['eci'],
				'Cost' => $params['Cost'],
				'Series' => $params['Series'],
				'cusNumber' => $params['cusNumber'],
				'changed_by' => $this->getCurrentUser(),
				'changed_date' => $this->getCurrentTime()
		);
		
		$where = $inventoryModel->getAdapter()->quoteInto('id_id = ?', $params['id_id']);
		
		$inventoryModel->update($data, $where);
		
		return true;
	}
	
	public function deleteInventory($id)
	{
		$inventoryModel = new self();
		
		//1=deleted, 0=active
		$data = array(
				'deleted' => '1',
				'changed_by' => $this->getCurrentUser(),
				'changed_date' => $this->getCurrentTime()
		);
		
		$where = $inventoryModel->getAdapter()->quoteInto('id_id = ?', $id);
		
		$inventoryModel->update($data, $where);
		
		return true;
	}

	protected function getCurrentUser()
	{
		//set user name
		$auth = Zend_Auth::getInstance();

		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
			$user = $identity->userName;
		} else {
			$user = null;
		}

		return $user;
	}
	
	protected function getCurrentTime()
	{
		//set current date/time
		$now = Zend_Date::now();
		$changedDate  = $now->toString('MM/dd/YYYY HH:mm:ss');

		return $changedDate;
	}
}
