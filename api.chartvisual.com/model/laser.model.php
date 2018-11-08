<?php

/** ===========
 *  Laser Model
 *  ===========
 */

class Laser extends Database {

    public function get_laser_data($currency_pair,$time_frame,$limit)
    {

        $result_limit=(strlen($limit)>0)?' LIMIT ' . $limit:'';

        $db_columns = array(
            'id',
            'bull',
            'bear',
            'buy',
            'sell',
            'av',
            'mm',
            'DATE_FORMAT(time,"%Y-%m-%d %H:%i:%s") as time'

        );

        $db_table = strtoupper($currency_pair);// . '_' . strtoupper($time_frame);

        $col = implode(',',$db_columns);

        $range = 'WHERE tf=\'' . strtoupper($time_frame) .'\' AND  time BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE()';

        $q = 'SELECT ' . $col .' FROM ' . $db_table .' ' . $range . $result_limit;
        $result = $this->db_query($q,DATABASE_LASER);
        return (!empty($result))?$result:false;
    }
}


