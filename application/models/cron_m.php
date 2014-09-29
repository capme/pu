<?php

/**
 * 
 * @author <tech@velaasia.com>
 * This model used for every cron process to update statistic data from linnworks and 3PL
 * 
 */
class Cron_m extends CI_Model {
	function __construct()
    {
        parent::__construct();
    }

    /**
     * Update stock/invetory both for items and SKU and the report will appear on this url: /operations
     * @return void 
     */
	function sync_stocklog()
	{
		$mssql = $this->load->database('mssql', TRUE);
		
		// get Location
		$query = $mssql->get('[StockLocation]');
		$locations = $query->result_array();

		// get totalItem
		$sql = "
SELECT SUM([Quantity]) as TotalItem, Location, fkStockLocationId
FROM [StockLevel] sl
JOIN [StockLocation] sloc ON sloc.pkStockLocationId=sl.fkStockLocationId
GROUP BY fkStockLocationId, Location;
		;";
		$query = $mssql->query($sql);
		$item_records = $query->result_array();

		// get totalSku
		$sql = "
SELECT COUNT(si.ItemNumber) AS TotalSKU , Location, fkStockLocationId
FROM [StockItem] si 
INNER JOIN [StockLevel] sl ON si.pkStockItemID = sl.fkStockItemId 
INNER JOIN [StockLocation] sloc ON sloc.pkStockLocationId = sl.fkStockLocationId 
WHERE sl.[Quantity] > 0 
GROUP BY fkStockLocationId, Location, fkStockLocationId
		";
		$query = $mssql->query($sql);
		$sku_records = $query->result_array();


		$datetime = date('Y-m-d',time());

		$data = array();

		$totalSku = 0;
		$totalItem = 0;

		foreach($locations as $location){

			// cari totalItem
			foreach($item_records as $item){

				if($item['Location'] == $location['Location'] ){
					$totalItem = $item['TotalItem'];
					break;
				}
			}

			// cari totalSku
			foreach($sku_records as $sku){
				if($sku['Location'] == $location['Location']) {
					$totalSku = $sku['TotalSKU'];
					break;
				}
			}


			$data[] = array(
				'date' => $datetime,
				'totalItem' =>  $totalItem,
				'totalSku' => $totalSku,
				'client' => strtoupper($location['Location'])
			);

		}
		
		// @TODO: [3PL] sum totalItem and totalSku with 3pl data for each client. Then, insert process can be started

		// Insert it into MySQL
		$mysql = $this->load->database('mysql', TRUE);
		$orig_db_debug = $mysql->db_debug;
		$mysql->db_debug = FALSE;
		$mysql->insert_batch('dart_stocklog', $data); 
		$mysql->db_debug = $orig_db_debug;

	}

	/**
	 * Export current stock to csv file for each client
	 * @return void
	 */
	function export_stock(){
		$sql = "SELECT `pkClientId`, `client`, `export_path` FROM `clients` c 
		JOIN `client_settings` cs ON c.name=cs.client
		WHERE `export_path` IS NOT NULL";
		$mysql = $this->load->database('mysql', TRUE);
		$query = $mysql->query($sql);
		$rows = $query->result_array();

		// @TODO: fetch 3PL stock and sum the number before export it
		$mssql = $this->load->database('mssql', TRUE);
		foreach($rows as $row)
		{
		   $data = array();
		   $file = $row['export_path']."/export/stock.csv";
		   $sql = "SELECT si.ItemNumber AS SKU,
					SUM(sl.Quantity) - SUM(sl.InOrderBook) AS Available
					FROM [linnworks_finaware].[dbo].[StockLevel] sl
					INNER JOIN [linnworks_finaware].[dbo].[StockItem] si ON sl.fkStockItemId = si.pkStockItemId
					INNER JOIN [linnworks_finaware].[dbo].[StockLocation] slo ON sl.fkStockLocationId = slo.pkStockLocationId
					WHERE slo.pkStockLocationId IN ('".$row['pkClientId']."','00000000-0000-0000-0000-000000000000') 
					GROUP BY si.ItemNumber
					ORDER BY si.ItemNumber";
			#echo $sql;
			$export = $mssql->query($sql);
			if ($export->num_rows() > 0){
				foreach ($export->result_array() as $stock)
				{
					// if ($stock['Available'] != 0)
						$data[] = $stock;
				}
				$csv = $this->array2csv($data);
				file_put_contents($file, $csv);
			}
		}
	}

	private function array2csv(array &$array)
	{
	   if (count($array) == 0) {
		 return null;
	   }
	   ob_start();
	   $df = fopen("php://output", 'w');
	   fputcsv($df, array_keys(reset($array)));
	   foreach ($array as $row) {
		  fputcsv($df, $row);
	   }
	   fclose($df);
	   return ob_get_clean();
	}
	

}