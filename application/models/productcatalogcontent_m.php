<?php
/*
 * model for handle operation table inb_inventory_item(client_id),inb_inventory_stock(client_id),inb_document
 *
 */

/**
 * @property Clientoptions_m $clientoptions_m
 * @property Invsync_m $invsync_m
 * @property Colormap_m $colormap_m
 */
class Productcatalogcontent_m extends MY_Model {

    var $db = null;
    var $table = 'inb_document';
    var $tableInv = 'inb_inventory_item';
    var $tableInvStock = 'inb_inventory_stock';
    var $tableInvItems = 'inv_items';
    var $tableClient ='client';
    var $sorts = array(1 => "id");
    var $pkField = "id";
    var $path = "";
    var $pathInboundForm = "";
    var $attrList = array();
    var $paraplouClientId = 6;//please change according to each environment

    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('mysql', TRUE);
        $this->path = BASEPATH ."../public/inbound/catalog_product";
        $this->pathInboundForm = BASEPATH ."../public/inbound/inbound_form";
        $this->relation = array(
            array("type" => "inner", "table" => $this->tableClient, "link" => "{$this->table}.client_id  = {$this->tableClient}.{$this->pkField} where {$this->table}.status = 4")
        );

        $this->select = array("{$this->table}.doc_number", "{$this->table}.client_id", "{$this->table}.note", "{$this->table}.type", "{$this->table}.status", "{$this->table}.created_at", "{$this->table}.updated_at", "{$this->table}.created_by", "{$this->table}.filename", "{$this->table}.id", "{$this->tableClient}.client_code  ");
        $this->filters = array($this->table.".client_id"=>$this->table."_client_id", "doc_number"=>"doc_number");
        $this->listWhere['equal'] = array();
        $this->listWhere['like'] = array("doc_number","name");
    }

    function getInboundDocumentList()
    {
        $this->db = $this->load->database('mysql', TRUE);
        $iTotalRecords = $this->_doGetTotalRow();
        $iDisplayLength = intval($this->input->post('iDisplayLength'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($this->input->post('iDisplayStart'));
        $sEcho = intval($this->input->post('sEcho'));

        $records = array();
        $records["aaData"] = array();

        $statList= array(
            0 =>array("Pending", "info"),
            1 => array("Configure Attribute-Set","success"),
            2 => array("Form Inbounding","primary"),
            3 => array("Ready to Import 3PL","default"),
            4 => array("Ready to Import Mage","warning"),
            9 => array("Extracting","danger"),
            99=> array("Invalid", "danger")
        );

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $_row = $this->_doGetRows($iDisplayStart, $iDisplayLength);
        $no=0;
        foreach($_row->result() as $_result) {
            $status=$statList[$_result->status];
            $btnAction = "";
            if($_result->type == 1){
                if($_result->status == 4){
                    $btnAction .= '<br /><br /><a href="'.base_url().'productcatalogcontent/importItemMage?client='.$_result->client_id.'&doc='.$_result->id.'"  enabled="enabled" class="btn btn-xs default"><i class="glyphicon glyphicon-export" ></i> Import Item to MAGE</a>';
                }
                if($_result->status == 1 or $_result->status == 2 or $_result->status == 3 or $_result->status == 4){
                    $records["aaData"][] = array(
                        '<input type="checkbox" name="id[]" value="'.$_result->id.'">',
                        $no=$no+1,
                        $_result->client_code,
                        $_result->doc_number,
                        '<span class="label label-sm label-'.($status[1]).'">'.($status[0]).'</span>',
                        $_result->note,
                        $btnAction

                    );
                }
            }
        }
        $records["sEcho"] = $sEcho;
        $records["iTotalRecords"] = $iTotalRecords;
        $records["iTotalDisplayRecords"] = $iTotalRecords;
        return $records;
    }


    public function getParamInboundMage($client, $doc){
        $param = array();

        //get data from table inb_inventory_item_<client_id>
        $result = $this->getInboundInvItem($client, $doc);
        foreach($result as $item) {
            $sku_config = $item['sku_config'];
            $sku_simple = $item['sku_simple'];
            $sku_description = $item['sku_description'];
            $weight = $item['weiight'];
            $cost = $item['cost'];
            $upc = explode("|",$item['upc']);
            $attribute_set = $upc[0];
            $size = $upc[1];
            $color = $upc[2];
            if($this->paraplouClientId == $client){
                $clientId = $upc[3];
            }
            $price = $item['price'];
            $qty = $item['total_qty'];
            $attribute_set_id = $item['attribute_set'];

            if($this->paraplouClientId == $client){
                //for paraplou
                $param[] = array(
                    "sku_config" => $sku_config,
                    "sku_simple" => $sku_simple,
                    "sku_description" => $sku_description,
                    "weight" => $weight,
                    "cost" => $cost,
                    "attribute_set" => $attribute_set,
                    "size" => $size,
                    "color" => $color,
                    "price" => $price,
                    "qty" => $qty,
                    "attribute_set_id" => $attribute_set_id,
                    "client_id" => $clientId
                );
            }else{
                //for non paraplou
                $param[] = array(
                    "sku_config" => $sku_config,
                    "sku_simple" => $sku_simple,
                    "sku_description" => $sku_description,
                    "weight" => $weight,
                    "cost" => $cost,
                    "attribute_set" => $attribute_set,
                    "size" => $size,
                    "color" => $color,
                    "price" => $price,
                    "qty" => $qty,
                    "attribute_set_id" => $attribute_set_id
                );
            }

        }
        return $param;
    }

    function getInboundInvItem($client, $doc, $po_type=null){
        if(!$client) return array();
        if(is_null($po_type)) $po_type = 'NEW';
        if($po_type == 'ALL') $po_type = '';
        $mysql = $this->load->database('mysql', TRUE);
        if($po_type != ''){
            $query = $mysql->get_where('inb_inventory_item_'.$client, array('doc_number'=>$doc, 'po_type'=>$po_type));
        }else{
            $query = $mysql->get_where('inb_inventory_item_'.$client, array('doc_number'=>$doc));
        }
        $rows = $query->result_array();
        return $rows;
    }

}
