<?php

/**
 * Class SyncInventory
 * @property Threepl_lib $threepl_lib
 * @property Client_m $client_m
 */
class SyncInventory extends CI_Controller {
    public function fetch() {
        $this->load->library('Threepl_lib');
        $this->load->model( array('client_m', 'invsync_m'));

        $clients = $this->client_m->getClients();
        foreach($clients as $client) {
            log_message('debug', 'get active inventory from dart for::'.$client['client_code']);
            $data = $this->threepl_lib->getActiveInventory($client['client_code']);
            $this->invsync_m->save($data, $client);
        }
    }
}