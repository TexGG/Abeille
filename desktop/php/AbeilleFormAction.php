<?php
    require_once dirname(__FILE__).'/../../../../core/php/core.inc.php';
    require_once dirname(__FILE__)."/../../core/class/Abeille.class.php";

    $parameters_info = Abeille::getParameters();

    function getInfosFromNe( $item, $value, $client ) {
      $deviceId = substr( $item, strpos($item,"-")+1 );
      echo "deviceId: ".substr( $item, strpos($item,"-")+1 )."<br>";
      $device = eqLogic::byId(substr( $item, strpos($item,"-")+1 ));
      $address = substr($device->getLogicalId(),8);
      echo "address: ".$address."<br>\n";
      $EP = $device->getConfiguration('mainEP');
      echo "EP: ".$EP."<br>\n";

      // Get Name
      $client->publish('CmdAbeille/Ruche/ActiveEndPoint',           'address='.$address,                             0);
      $client->publish('CmdAbeille/Ruche/SimpleDescriptorRequest',  'address='.$address.'&endPoint='.$EP,            0);
      $client->publish('CmdAbeille/Ruche/IEEE_Address_request',     'address='.$address,                             0);
      $client->publish('CmdAbeille/Ruche/getName',                  'address='.$address.'&destinationEndPoint='.$EP, 0);
      $client->publish('CmdAbeille/Ruche/getLocation',              'address='.$address.'&destinationEndPoint='.$EP, 0);
      $client->publish('CmdAbeille/Ruche/getGroupMembership',       'address='.$address.'&DestinationEndPoint='.$EP, 0);
      // $client->publish('CmdAbeille/Ruche/getSceneMembership',   'address='.$address.'&DestinationEndPoint='.$EP.'&groupID='.$grouID, 0);
      // $client->publish('CmdAbeille/Ruche/ReadAttributeRequest', 'address='.$address.'&DestinationEndPoint='.$EP'.'&ClusterId='.$clusterId'.'&attributId='.$attributId'.'&Proprio='.$proprio', 0);

    }

    // ***********************************************************************************************
    // MQTT
    // ***********************************************************************************************
    function connect($r, $message)
    {
        // log::add('AbeilleMQTTCmd', 'info', 'Mosquitto: Connexion à Mosquitto avec code ' . $r . ' ' . $message);
        // config::save('state', '1', 'Abeille');
    }

    function disconnect($r)
    {
        // log::add('AbeilleMQTTCmd', 'debug', 'Mosquitto: Déconnexion de Mosquitto avec code ' . $r);
        // config::save('state', '0', 'Abeille');
    }

    function subscribe()
    {
        // log::add('AbeilleMQTTCmd', 'debug', 'Mosquitto: Subscribe to topics');
    }

    function logmq($code, $str)
    {
        // if (strpos($str, 'PINGREQ') === false && strpos($str, 'PINGRESP') === false) {
        // log::add('AbeilleMQTTCmd', 'debug', 'Mosquitto: Log level: ' . $code . ' Message: ' . $str);
        // }
    }

    function message($message)
    {
        // var_dump( $message );
    }
    // https://github.com/mgdm/Mosquitto-PHP
    // http://mosquitto-php.readthedocs.io/en/latest/client.html
    $client = new Mosquitto\Client();

    // http://mosquitto-php.readthedocs.io/en/latest/client.html#Mosquitto\Client::onConnect
    $client->onConnect('connect');

    // http://mosquitto-php.readthedocs.io/en/latest/client.html#Mosquitto\Client::onDisconnect
    $client->onDisconnect('disconnect');

    // http://mosquitto-php.readthedocs.io/en/latest/client.html#Mosquitto\Client::onSubscribe
    $client->onSubscribe('subscribe');

    // http://mosquitto-php.readthedocs.io/en/latest/client.html#Mosquitto\Client::onMessage
    $client->onMessage('message');

    // http://mosquitto-php.readthedocs.io/en/latest/client.html#Mosquitto\Client::onLog
    $client->onLog('logmq');

    // http://mosquitto-php.readthedocs.io/en/latest/client.html#Mosquitto\Client::setWill
    $client->setWill('/jeedom', "Client AbeilleFormAction died !!!", $parameters_info['AbeilleQos'], 0);

    // http://mosquitto-php.readthedocs.io/en/latest/client.html#Mosquitto\Client::setReconnectDelay
    $client->setReconnectDelay(1, 120, 1);

    try {

        $client->setCredentials( "jeedom", "jeedom" );
        $client->connect( "localhost", 1883, 60 );
        $client->subscribe( "#", 0 ); // !auto: Subscribe to root topic

        echo "Group: ".$_POST['groupID'].$_POST['groupIdScene1'].$_POST['groupIdScene2']."<br>";
        echo "Action: ".$_POST['submitButton']."<br>";

        switch ($_POST['submitButton']) {

            // Group
            case 'Add Group':
                foreach ( $_POST as $item=>$Value ) {
                    if ( strpos("-".$item, "eqSelected") == 1 ) {
                        echo "Id: ".substr( $item, strpos($item,"-")+1 )."<br>";
                        $device = eqLogic::byId(substr( $item, strpos($item,"-")+1 ));
                        $address = substr($device->getLogicalId(),8);
                        $EP = $device->getConfiguration('mainEP');
                        $client->publish('CmdAbeille/Ruche/addGroup',           'address='.$address.'&DestinationEndPoint='.$EP.'&groupAddress='.$_POST['group'], 0);
                        sleep(1);
                        $client->publish('CmdAbeille/Ruche/getGroupMembership', 'address='.$address.'&DestinationEndPoint='.$EP, 0);
                        sleep(1);
                    }
                }
                break;
                
            case 'Set Group Remote':
                foreach ( $_POST as $item=>$Value ) {
                    if ( strpos("-".$item, "eqSelected") == 1 ) {
                        echo "Id: ".substr( $item, strpos($item,"-")+1 )."<br>";
                        $device = eqLogic::byId(substr( $item, strpos($item,"-")+1 ));
                        $address = substr($device->getLogicalId(),8);
                        $EP = $device->getConfiguration('mainEP');
                        $client->publish('CmdAbeille/Ruche/commissioningGroupAPS',           'address='.$address.'&groupId='.$_POST['group'], 0);
                    }
                }
                break;
                
            case 'Remove Group':
                foreach ( $_POST as $item=>$Value ) {
                    if ( strpos("-".$item, "eqSelected") == 1 ) {
                        echo "Id: ".substr( $item, strpos($item,"-")+1 )."<br>";
                        $device = eqLogic::byId(substr( $item, strpos($item,"-")+1 ));
                        $address = substr($device->getLogicalId(),8);
                        $EP = $device->getConfiguration('mainEP');
                        $client->publish('CmdAbeille/Ruche/removeGroup',        'address='.$address.'&DestinationEndPoint='.$EP.'&groupAddress='.$_POST['group'], 0);
                        sleep(1);
                        $client->publish('CmdAbeille/Ruche/getGroupMembership', 'address='.$address.'&DestinationEndPoint='.$EP, 0);
                        sleep(1);
                    }
                }
                break;
            case 'Get Group':
                foreach ( $_POST as $item=>$Value ) {
                    if ( strpos("-".$item, "eqSelected") == 1 ) {
                        echo "Id: ".substr( $item, strpos($item,"-")+1 )."<br>";
                        $device = eqLogic::byId(substr( $item, strpos($item,"-")+1 ));
                        $address = substr($device->getLogicalId(),8);
                        $EP = $device->getConfiguration('mainEP');
                        $client->publish('CmdAbeille/Ruche/getGroupMembership', 'address='.$address.'&DestinationEndPoint='.$EP, 0);
                    }
                }
                break;

            // Scene
            case 'View Scene':
                foreach ( $_POST as $item=>$Value ) {
                    if ( strpos("-".$item, "eqSelected") == 1 ) {
                        echo "Id: ".substr( $item, strpos($item,"-")+1 )."<br>";
                        $device = eqLogic::byId(substr( $item, strpos($item,"-")+1 ));
                        $address = substr($device->getLogicalId(),8);
                        $EP = $device->getConfiguration('mainEP');
                        $client->publish('CmdAbeille/Ruche/viewScene',           'address='.$address.'&DestinationEndPoint='.$EP.'&groupID='.$_POST['groupIdScene2'].'&sceneID='.$_POST['sceneID'], 0);

                    }
                }
                break;

            case 'Store Scene':
                foreach ( $_POST as $item=>$Value ) {
                    if ( strpos("-".$item, "eqSelected") == 1 ) {
                        echo "Id: ".substr( $item, strpos($item,"-")+1 )."<br>";
                        $device = eqLogic::byId(substr( $item, strpos($item,"-")+1 ));
                        $address = substr($device->getLogicalId(),8);
                        $EP = $device->getConfiguration('mainEP');
                        $client->publish('CmdAbeille/Ruche/storeScene',           'address='.$address.'&DestinationEndPoint='.$EP.'&groupID='.$_POST['groupIdScene2'].'&sceneID='.$_POST['sceneID'], 0);

                    }
                }
                break;

            case 'Recall Scene':
                foreach ( $_POST as $item=>$Value ) {
                    if ( strpos("-".$item, "eqSelected") == 1 ) {
                        echo "Id: ".substr( $item, strpos($item,"-")+1 )."<br>";
                        $device = eqLogic::byId(substr( $item, strpos($item,"-")+1 ));
                        $address = substr($device->getLogicalId(),8);
                        $EP = $device->getConfiguration('mainEP');
                        $client->publish('CmdAbeille/Ruche/recallScene',           'address='.$address.'&DestinationEndPoint='.$EP.'&groupID='.$_POST['groupIdScene2'].'&sceneID='.$_POST['sceneID'], 0);
                    }
                }
                break;

            case 'scene Group Recall':
                if (0) {
                foreach ( $_POST as $item=>$Value ) {
                    if ( strpos("-".$item, "eqSelected") == 1 ) {
                        echo "Id: ".substr( $item, strpos($item,"-")+1 )."<br>";
                        $device = eqLogic::byId(substr( $item, strpos($item,"-")+1 ));
                        $address = substr($device->getLogicalId(),8);
                        $EP = $device->getConfiguration('mainEP');
                        $client->publish('CmdAbeille/Ruche/sceneGroupRecall',       'groupID='.$_POST['groupIdScene2'].'&sceneID='.$_POST['sceneID'], 0);
                    }
                }
                }
                else {
                    $client->publish('CmdAbeille/Ruche/sceneGroupRecall',       'groupID='.$_POST['groupIdScene2'].'&sceneID='.$_POST['sceneID'], 0);
                }
                break;

            case 'Add Scene':
                foreach ( $_POST as $item=>$Value ) {
                    if ( strpos("-".$item, "eqSelected") == 1 ) {
                        echo "Id: ".substr( $item, strpos($item,"-")+1 )."<br>";
                        $device = eqLogic::byId(substr( $item, strpos($item,"-")+1 ));
                        $address = substr($device->getLogicalId(),8);
                        $EP = $device->getConfiguration('mainEP');
                        $client->publish('CmdAbeille/Ruche/addScene',           'address='.$address.'&DestinationEndPoint='.$EP.'&groupID='.$_POST['groupIdScene2'].'&sceneID='.$_POST['sceneID'].'&sceneName=aa', 0);
                    }
                }
                break;

            case 'Remove Scene':
                foreach ( $_POST as $item=>$Value ) {
                    if ( strpos("-".$item, "eqSelected") == 1 ) {
                        echo "Id: ".substr( $item, strpos($item,"-")+1 )."<br>";
                        $device = eqLogic::byId(substr( $item, strpos($item,"-")+1 ));
                        $address = substr($device->getLogicalId(),8);
                        $EP = $device->getConfiguration('mainEP');
                        $client->publish('CmdAbeille/Ruche/removeScene',           'address='.$address.'&DestinationEndPoint='.$EP.'&groupID='.$_POST['groupIdScene2'].'&sceneID='.$_POST['sceneID'], 0);
                    }
                }
                break;

            case 'Get Scene Membership':
                echo "Get Scene Membership<br>";
                foreach ( $_POST as $item=>$Value ) {
                    if ( strpos("-".$item, "eqSelected") == 1 ) {
                        echo "Id: ".substr( $item, strpos($item,"-")+1 )."<br>";
                        $device = eqLogic::byId(substr( $item, strpos($item,"-")+1 ));
                        $address = substr($device->getLogicalId(),8);
                        $EP = $device->getConfiguration('mainEP');
                        $client->publish('CmdAbeille/Ruche/getSceneMembership',     'address='.$address.'&DestinationEndPoint='.$EP.'&groupID='.$_POST['groupIdScene1'], 0);
                    }
                }
                break;

            case 'Remove All Scene':
                foreach ( $_POST as $item=>$Value ) {
                    if ( strpos("-".$item, "eqSelected") == 1 ) {
                        echo "Id: ".substr( $item, strpos($item,"-")+1 )."<br>";
                        $device = eqLogic::byId(substr( $item, strpos($item,"-")+1 ));
                        $address = substr($device->getLogicalId(),8);
                        $EP = $device->getConfiguration('mainEP');
                        $client->publish('CmdAbeille/Ruche/removeSceneAll',         'address='.$address.'&DestinationEndPoint='.$EP.'&groupID='.$_POST['groupIdScene1'], 0);
                    }
                }
                break;

            // Template
            case 'Apply Template':
                foreach ( $_POST as $item=>$Value ) {
                    if ( strpos("-".$item, "eqSelected") == 1 ) {
                        $deviceId = substr( $item, strpos($item,"-")+1 );
                        // echo "Id: ".substr( $item, strpos($item,"-")+1 )."<br>";
                        // $device = eqLogic::byId(substr( $item, strpos($item,"-")+1 ));
                        // $address = substr($device->getLogicalId(),8);
                        // $EP = $device->getConfiguration('mainEP');
                        // $client->publish('CmdAbeille/Ruche/addGroup', 'address='.(substr( $item, strpos($item,"-")+1 )).'&DestinationEndPoint='.$EP.'&groupAddress='.$_POST['group'], 0);
                        abeille::updateConfigAbeille( $deviceId );
                        // abeille::updateConfigAbeille( );
                    }
                }
                break;

            case 'Get Infos from NE':
                foreach ( $_POST as $item=>$Value ) {
                    if ( strpos("-".$item, "eqSelected") == 1 ) {
                        // $deviceId = substr( $item, strpos($item,"-")+1 );
                        // echo "Id: ".substr( $item, strpos($item,"-")+1 )."<br>";
                        // $device = eqLogic::byId(substr( $item, strpos($item,"-")+1 ));
                        // $address = substr($device->getLogicalId(),8);
                        // $EP = $device->getConfiguration('mainEP');
                        // $client->publish('CmdAbeille/Ruche/addGroup', 'address='.(substr( $item, strpos($item,"-")+1 )).'&DestinationEndPoint='.$EP.'&groupAddress='.$_POST['group'], 0);
                        getInfosFromNe( $item, $Value, $client );
                        // abeille::updateConfigAbeille( );
                    }
                }
                break;
                
            case 'TxPower':
                echo "TxPower request processing";
                $client->publish('CmdAbeille/Ruche/TxPower', $_POST['TxPowerValue'], 0);
                break;
                
            case 'Set Channel Mask':
                echo "Set Channel Mask request processing";
                $client->publish('CmdAbeille/Ruche/setChannelMask', $_POST['channelMask'], 0);
                break;
                
            case 'Set Extended PANID':
                echo "Set Extended PANID request processing";
                $client->publish('CmdAbeille/Ruche/setExtendedPANID', $_POST['extendedPanId'], 0);
                break;
        }

        $client->loop();
        sleep(1);
        $client->loop();
        
        $client->disconnect();
        unset($client);

    } catch (Exception $e) {
        echo '<br>error: '.$e->getMessage();
    }
    echo "<br>Fin";
    sleep(1);
    header ("location:/index.php?v=d&m=Abeille&p=Abeille");

    ?>
