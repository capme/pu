<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once dirname(__FILE__) . '/tcpdf/examples/tcpdf_include.php';
require_once dirname(__FILE__) . '/tcpdf/tcpdf.php';

class Va_pdf extends TCPDF
{
    function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false)
    {
        parent::__construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false);
    }
}
?>