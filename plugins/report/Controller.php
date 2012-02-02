<?php
/**
 * @package report
 * @copyright (C) 2004 - 2005 Moxley Data Systems
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Modern Merchant is Free Software
 */

/**
 */
class report_Controller extends admin_Controller
{
    function runMonthlyAction()
    {
        $dbh = mm_getDatabase();
        
        // Get the start of month, one year ago
        $m = intval(date("m"));
        $y = intval(date("y"));
        $yearago = date("Y-m-d", mktime(0,0,0, $m, 1, $y-1));
        
        $sql = "select sum(total) as total, month(order_date) as month,"
            ." year(order_date) as year from mm_order"
            ." where order_date >= ".dq($yearago)." and order_date <= now()"
            ." group by year,month order by year,month";
        $totals = $dbh->getAllAssoc($sql);
        $max = 0;
        for ( $i=0; $i<count($totals); $i++ )
        {
            $data =& $totals[$i];
            if ($data['total'] > $max) $max = $data['total'];
            $data['time'] = mktime(0,0,0, $data['month'], 1, $data['year']);
        }
        $this->max = $max;
        $this->totals = $totals;
        $this->title = "Sales By Month";
    }
}
