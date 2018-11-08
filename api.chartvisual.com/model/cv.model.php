<?php

/** ==========================
 *  ChartVisual Combined Model
 *  ==========================
 */

class CV extends Database {


    public function get_csi_data($currency_pair,$time_frame,$result_limit='')
    {
        $db_columns = array(
            'id as csi_id',
            'bull as csi_bull',
            'bear as csi_bear',
            'DATE_FORMAT(time,"%Y-%m-%d %H:%i:%s") as time'

        );

        
        $db_table = strtoupper($currency_pair);

        $col = implode(',',$db_columns);

        //$range = 'WHERE time BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE()';
        $range = 'WHERE tf=\'' . strtoupper($time_frame) . '\'';

        $q = 'SELECT ' . $col .' FROM ' . $db_table .' ' . $range  .' ' . $result_limit;
        $result = $this->db_query($q,DATABASE_CSI);
        return (!empty($result))?$result:false;
    }


    public function get_laser_data($currency_pair,$time_frame,$result_limit='')
    {
        $db_columns = array(
            'id as laser_id',
            'bull as laser_bull',
            'bear as laser_bear',
            'buy as laser_buy',
            'sell as laser_sell',
            'av as laser_av',
            'mm as laser_mm',
            'DATE_FORMAT(time,"%Y-%m-%d %H:%i:%s") as time'

        );

        $db_table = strtoupper($currency_pair);

        $col = implode(',',$db_columns);

        //$range = 'WHERE time BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE()';
        $range = 'WHERE tf=\'' . strtoupper($time_frame) . '\'';

        $q = 'SELECT ' . $col .' FROM ' . $db_table .' ' . $range  .' ' . $result_limit;
        $result = $this->db_query($q,DATABASE_LASER);
        return (!empty($result))?$result:false;
    }


    public function get_laser_summary($currency_pair,$time_frame,$result_limit='')
    {
        $db_columns = array(
            'bull',
            'bear',
            'buy',
            'sell',
            'av',
            'mm',
            'DATE_FORMAT(time,"%Y-%m-%d %H:%i:%s") as time'
        );

        $result_limit = '(bull > 0 && bear > 0) ORDER BY time DESC';

        $db_table = strtoupper($currency_pair);

        $col = implode(',',$db_columns);
        $range = 'WHERE tf=\'' . strtoupper($time_frame) .'\' AND ';

        $q = 'SELECT ' . $col .' FROM ' . $db_table .' ' . $range  .' ' . $result_limit;
        //error_log($q);
        $result = $this->db_query($q,DATABASE_LASER);


        $q = 'SELECT av,mm FROM ' . $db_table .'  WHERE av > 0 && mm > 0';
        $_result = $this->db_query($q,DATABASE_LASER);


        $laser_mm = $_result[0]['mm'];
        $laser_av = $_result[0]['av'];



        $_profit = 0;
        $_profits = Array();

        for($i=0;$i<=count($result);$i++)
        {

            if(!isset($result[$i+1])) break;

            $_p=0;

            //bull trend
            if($result[$i]['sell'] > 0)
            {
                //error_log('bull: ' .$result[$i]['bull'] . ' : ' . $result[$i+1]['bull'] . ':' . $result[$i]['bull'] - $result[$i+1]['bull']);
                $_p = $result[$i]['bull'] - $result[$i+1]['bull'];
                if($_p>0) {
                    $_profit++;
                    $_profits[]=$_p;
                }
            }
            //bear trend
             if($result[$i]['buy']>0)
            {
                //error_log('bear: ' . $result[$i]['bear'] . ' : ' . $result[$i+1]['bear'] . ':' . $result[$i+1]['bear'] - $result[$i]['bear']);
                $_p = $result[$i+1]['bear'] - $result[$i]['bear'];
                if($_p >0)
                {
                    $_profit++;
                    $_profits[]=$_p;
                }
            }

        }
        arsort($_profits);
        //print '<pre>'. print_r($_profits,true);
        //exit();
        return (!empty($result))?array('trades'=>count($result),'profitable_trades'=>$_profit,'profit_max'=>$_profits[0],'av'=>$laser_av,'mm'=>$laser_mm):false;
    }




    public function get_scalper_data($currency_pair,$time_frame,$result_limit='')
    {
        $db_columns = array(
            'id as scalper_id',
            'bg as scalper_bg',
            'buy as scalper_buy',
            'sell as scalper_sell',
            'DATE_FORMAT(time,"%Y-%m-%d %H:%i:%s") as time'

        );

        $db_table = strtoupper($currency_pair);

        $col = implode(',',$db_columns);

        //$range = 'WHERE time BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE()';
        $range = 'WHERE tf=\'' . strtoupper($time_frame) .'\'';

        $q = 'SELECT ' . $col .' FROM ' . $db_table .' ' . $range  .' ' . $result_limit;
        $result = $this->db_query($q,DATABASE_SCALPER);
        return (!empty($result))?$result:false;
    }


    public function get_candle_stick_data($currency_pair, $time_frame,$result_limit='')
    {
        $db_columns = array(
            'id',
            'open',
            'high',
            'close',
            'low',
            'DATE_FORMAT(time,"%Y-%m-%d %H:%i:%s") as time'
        );
        $range = 'WHERE tf=\'' . strtoupper($time_frame) .'\'';

        $db_table = strtoupper($currency_pair);
        $col = implode(',',$db_columns);
        $q = 'SELECT ' . $col .' FROM ' . $db_table . ' ' . $range .' ' . $result_limit;
        $result = $this->db_query($q,DATABASE_CANDLESTICKS);
        return (!empty($result))?$result:false;
    }

    public function get_currency_pairs()
    {
        $q = 'show tables from ' . DATABASE_CANDLESTICKS;
        $result = $this->db_query($q,DATABASE_CANDLESTICKS);

        $pair_array = Array();
        foreach($result as $item)
        {
            //$_i = explode('_',$item['Tables_in_topforex_1k_prices']);
            $_i = $item['Tables_in_topforex_1k_prices'];

            if(!in_array(strtolower($_i[0]),$pair_array)) $pair_array[] = strtolower($_i);
        }
        return (!empty($pair_array))?$pair_array:false;
    }

    public function get_cv_data($currency_pair,$time_frame,$limit='')
    {
        $result_limit=(strlen($limit)>0)?'LIMIT ' . $limit:'';

        $cv_market = $this->get_candle_stick_data($currency_pair,$time_frame,$result_limit);
        $cv_laser = $this->get_laser_data($currency_pair,$time_frame,$result_limit);
        $cv_csi = $this->get_csi_data($currency_pair,$time_frame,$result_limit);
        $cv_scalper = $this->get_scalper_data($currency_pair,$time_frame,$result_limit);
        $_i = $this->array_extend($cv_market, $cv_laser);
        $_d = $this->array_extend($_i, $cv_scalper);

        $result = Array(
            'data'=>$this->array_extend($_d,$cv_csi),
            'csi'=>($cv_csi)?true:false,
            'laser'=>($cv_laser)?true:false,
            'market'=>($cv_market)?true:false,
            'scalper'=>($cv_scalper)?true:false
            );
        return $result;
    }

    public function array_extend($a, $b) {
        foreach($b as $k=>$v) {
            if( is_array($v) ) {
                if( !isset($a[$k]) OR isset($v[0])) {
                    $a[$k] = $v;
                } else {
                    $a[$k] = $this->array_extend($a[$k], $v);
                }
            } else {
                $a[$k] = $v;
            }
        }
        return $a;
    }


}


