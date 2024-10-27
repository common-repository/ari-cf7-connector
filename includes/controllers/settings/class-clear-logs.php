<?php
namespace Ari_Cf7_Connector\Controllers\Settings;

use Ari\Controllers\Controller as Controller;
use Ari\Utils\Response as Response;
use Ari_Cf7_Connector\Helpers\Helper as Helper;
use Ari_Cf7_Connector\Log\App_Log as App_Log;
use Ari_Cf7_Connector\Log\System_Log as System_Log;

class Clear_Logs extends Controller {
    public function execute() {
        check_admin_referer( 'acf7c' );

        $res = App_Log::clear() && System_Log::clear();
        if ( $res ) {
            Response::redirect(
                Helper::build_url(
                    array(
                        'page' => 'ari-cf7connector',

                        'action' => 'display',

                        'msg' => __( 'Logs cleared successfully.', 'contact-form-7-connector' ),

                        'msg_type' => ARICF7CONNECTOR_MESSAGETYPE_SUCCESS,
                    )
                )
            );
        } else {
            Response::redirect(
                Helper::build_url(
                    array(
                        'page' => 'ari-cf7connector',

                        'action' => 'display',

                        'msg' => __( 'The logs are not cleared.', 'contact-form-7-connector' ),

                        'msg_type' => ARICF7CONNECTOR_MESSAGETYPE_ERROR,
                    )
                )
            );
        }
    }
}