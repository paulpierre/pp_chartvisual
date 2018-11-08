<?php
global $controllerID,$controllerObject,$controllerFunction,$controllerData;

/** =============================
 *  Chart Visual Chart Controller
 *  =============================
 *
 *  api.chartvisual.com/cv/$currency_pair/$currency_time_frame/$size
 *
 */


switch($controllerFunction)
{

    case 'pairs':


        if(!empty($currency_pairs_supported))
        {
            api_response(array(
                'code'=> RESPONSE_SUCCESS,
                'data'=> $currency_pairs_supported
            ));

        } else {
            api_response(array(
                'code'=> RESPONSE_ERROR,
                'data'=> array('message'=>'Unable to retrieve supported currency pairs.')
            ));
        }
        break;

    default:

        $currency_pair = $controllerFunction;
        $currency_time_frame = $controllerID;
        $data_size = $controllerData;

        /**
         *  Error check to make sure the params are supported for time frame and currency pair
         */


        if(!in_array(strtolower($currency_pair),$currency_pairs_supported))
        {
            api_response(array(
                'code'=> RESPONSE_ERROR,
                'data'=> array('message'=>'Currency pair ' . $currency_pair . ' not supported for this time frame.')
            ));
        }

        if(!in_array(strtolower($currency_time_frame),$currency_time_frames_supported))
        {
            api_response(array(
                'code'=> RESPONSE_ERROR,
                'data'=> array('message'=>'Time frame ' . $currency_time_frame . ' not supported with this currency pair.')
            ));
        }


        if(isset($cv_instance)) unset($cv_instance);
        $cv_instance = new CV;
        $cv_data = $cv_instance->get_cv_data($currency_pair,$currency_time_frame,$data_size);
        $cv_laser = $cv_instance->get_laser_summary($currency_pair,$currency_time_frame);


        if($cv_data)
        {
            api_response(array(
                'code'=> RESPONSE_SUCCESS,
                'data'=> array(
                    'result'=> $cv_data['data'],
                    'csi'=>$cv_data['csi'],
                    'market'=>$cv_data['market'],
                    'laser'=>$cv_data['laser'],
                    'scalper'=>$cv_data['scalper'],
                    'laser_summary'=>$cv_laser
               )
            ));

        } else {
            api_response(array(
                'code'=> RESPONSE_ERROR,
                'data'=> array('message'=>'Unable to retrieve market data in the database for ' . $currency_pair .' - ' .  $currency_time_frame . '.')
            ));
        }

        break;

}
