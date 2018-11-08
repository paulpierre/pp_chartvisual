<?php

/** ===========
 *  CSI Model
 *  ===========
 */

class CSI extends Database {


    public function get_csi_data($currency_pair,$time_frame,$limit='')
    {
        $result_limit=(strlen($limit)>0)?' LIMIT ' . $limit:'';


        $db_columns = array(
            'id',
            'bull',
            'bear',
            'DATE_FORMAT(time,"%Y-%m-%d %H:%i:%s") as time'

        );

        //$db_table = strtoupper($currency_pair) . '_' . strtoupper($time_frame);
        $db_table = strtoupper($currency_pair);// . '_' . strtoupper($time_frame);

        $col = implode(',',$db_columns);

        $range = 'WHERE tf=\'' . strtoupper($time_frame) .'\' AND time BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE()';

        $q = 'SELECT ' . $col .' FROM ' . $db_table .' ' . $range . $result_limit;
        $result = $this->db_query($q,DATABASE_CSI);
        return (!empty($result))?$result:false;
    }
}


