<?php
   require_once __DIR__ . '/../vendor/autoload.php'; // Load Composer dependencies

   use HubtelUssdFramework\UssdRequest;
   use HubtelUssdFramework\UssdResponse;
   use HubtelUssdFramework\UssdMenu;

   // Read JSON input
   $input = json_decode(file_get_contents('php://input'), true);

   // Initialize request
   $request = new UssdRequest($input);
   $sessionId = $request->getSessionId();
   $message = $request->getMessage();
   $type = $request->getType();
   $clientState = $request->getClientState() ?: '';

   // Initialize response
   $response = new UssdResponse();

   // Handle USSD flow
   if ($type === 'Initiation') {
       $menu = new UssdMenu();
       $menu->header('Welcome to OLAM Pay')
            ->createAndAddItem('1. Offering', 'offering')
            ->createAndAddItem('2. Seed', 'seed')
            ->createAndAddItem('3. New Jerusalem Event', 'nje')
            ->createAndAddItem('4. TOTA', 'tota')
            ->createAndAddItem('5. TV Ministry', 'tvministry')
            ->createAndAddItem('6. New Jerusalem Building', 'nje_building');
       $response->setSessionId($sessionId)
                ->setType('response')
                ->setMessage($menu->toString())
                ->setLabel('Main Menu')
                ->setClientState('main')
                ->setDataType('input')
                ->setFieldType('text');
   } elseif ($clientState === 'main') {
       switch ($message) {
           case '1':
               $response->setSessionId($sessionId)
                        ->setType('response')
                        ->setMessage('Enter amount for Offering:')
                        ->setLabel('Offering Amount')
                        ->setClientState('offering_amount')
                        ->setDataType('input')
                        ->setFieldType('decimal');
               break;
           case '2':
               $response->setSessionId($sessionId)
                        ->setType('response')
                        ->setMessage('Enter amount for Seed:')
                        ->setLabel('Seed Amount')
                        ->setClientState('seed_amount')
                        ->setDataType('input')
                        ->setFieldType('decimal');
               break;
           case '3':
               $menu = new UssdMenu();
               $menu->header('New Jerusalem Event')
                    ->createAndAddItem('1. Registration', 'nje_registration')
                    ->createAndAddItem('2. Donation', 'nje_donation')
                    ->createAndAddItem('3. Custom Amount', 'nje_custom');
               $response->setSessionId($sessionId)
                        ->setType('response')
                        ->setMessage($menu->toString())
                        ->setLabel('New Jerusalem Event Options')
                        ->setClientState('nje')
                        ->setDataType('input')
                        ->setFieldType('text');
               break;
           case '4':
               $response->setSessionId($sessionId)
                        ->setType('response')
                        ->setMessage('Enter amount for TOTA:')
                        ->setLabel('TOTA Amount')
                        ->setClientState('tota_amount')
                        ->setDataType('input')
                        ->setFieldType('decimal');
               break;
           case '5':
               $response->setSessionId($sessionId)
                        ->setType('response')
                        ->setMessage('Enter amount for TV Ministry:')
                        ->setLabel('TV Ministry Amount')
                        ->setClientState('tvministry_amount')
                        ->setDataType('input')
                        ->setFieldType('decimal');
               break;
           case '6':
               $response->setSessionId($sessionId)
                        ->setType('response')
                        ->setMessage('Enter amount for New Jerusalem Building:')
                        ->setLabel('Building Project Amount')
                        ->setClientState('nje_building_amount')
                        ->setDataType('input')
                        ->setFieldType('decimal');
               break;
           default:
               $menu = new UssdMenu();
               $menu->header('Invalid option. Try again.')
                    ->createAndAddItem('1. Offering', 'offering')
                    ->createAndAddItem('2. Seed', 'seed')
                    ->createAndAddItem('3. New Jerusalem Event', 'nje')
                    ->createAndAddItem('4. TOTA', 'tota')
                    ->createAndAddItem('5. TV Ministry', 'tvministry')
                    ->createAndAddItem('6. New Jerusalem Building', 'nje_building');
               $response->setSessionId($sessionId)
                        ->setType('response')
                        ->setMessage($menu->toString())
                        ->setLabel('Main Menu')
                        ->setClientState('main')
                        ->setDataType('input')
                        ->setFieldType('text');
       }
   } elseif ($clientState === 'nje') {
       switch ($message) {
           case '1':
               $response->setSessionId($sessionId)
                        ->setType('response')
                        ->setMessage('Enter amount for Event Registration:')
                        ->setLabel('Registration Amount')
                        ->setClientState('nje_registration_amount')
                        ->setDataType('input')
                        ->setFieldType('decimal');
               break;
           case '2':
               $response->setSessionId($sessionId)
                        ->setType('response')
                        ->setMessage('Enter amount for Event Donation:')
                        ->setLabel('Donation Amount')
                        ->setClientState('nje_donation_amount')
                        ->setDataType('input')
                        ->setFieldType('decimal');
               break;
           case '3':
               $response->setSessionId($sessionId)
                        ->setType('response')
                        ->setMessage('Enter custom amount for New Jerusalem Event:')
                        ->setLabel('Custom Amount')
                        ->setClientState('nje_custom_amount')
                        ->setDataType('input')
                        ->setFieldType('decimal');
               break;
           default:
               $menu = new UssdMenu();
               $menu->header('Invalid option. New Jerusalem Event')
                    ->createAndAddItem('1. Registration', 'nje_registration')
                    ->createAndAddItem('2. Donation', 'nje_donation')
                    ->createAndAddItem('3. Custom Amount', 'nje_custom');
               $response->setSessionId($sessionId)
                        ->setType('response')
                        ->setMessage($menu->toString())
                        ->setLabel('New Jerusalem Event Options')
                        ->setClientState('nje')
                        ->setDataType('input')
                        ->setFieldType('text');
       }
   } elseif (in_array($clientState, ['offering_amount', 'seed_amount', 'tota_amount', 'tvministry_amount', 'nje_building_amount', 'nje_registration_amount', 'nje_donation_amount', 'nje_custom_amount'])) {
       if (is_numeric($message) && floatval($message) > 0) {
           $itemName = [
               'offering_amount' => 'Offering',
               'seed_amount' => 'Seed',
               'tota_amount' => 'TOTA',
               'tvministry_amount' => 'TV Ministry',
               'nje_building_amount' => 'New Jerusalem Building',
               'nje_registration_amount' => 'New Jerusalem Event Registration',
               'nje_donation_amount' => 'New Jerusalem Event Donation',
               'nje_custom_amount' => 'New Jerusalem Event Custom'
           ][$clientState];
           $response->setSessionId($sessionId)
                    ->setType('AddToCart')
                    ->setMessage('Request submitted. Await payment prompt.')
                    ->setItem([
                        'ItemName' => $itemName,
                        'Qty' => 1,
                        'Price' => floatval($message)
                    ])
                    ->setLabel('Payment Request')
                    ->setDataType('display')
                    ->setFieldType('text');
       } else {
           $response->setSessionId($sessionId)
                    ->setType('response')
                    ->setMessage('Invalid amount. Enter a number (e.g., 150.50):')
                    ->setLabel('Amount')
                    ->setClientState($clientState)
                    ->setDataType('input')
                    ->setFieldType('decimal');
       }
   } else {
       $menu = new UssdMenu();
       $menu->header('Session error. Start again.')
            ->createAndAddItem('1. Offering', 'offering')
            ->createAndAddItem('2. Seed', 'seed')
            ->createAndAddItem('3. New Jerusalem Event', 'nje')
            ->createAndAddItem('4. TOTA', 'tota')
            ->createAndAddItem('5. TV Ministry', 'tvministry')
            ->createAndAddItem('6. New Jerusalem Building', 'nje_building');
       $response->setSessionId($sessionId)
                ->setType('response')
                ->setMessage($menu->toString())
                ->setLabel('Main Menu')
                ->setClientState('main')
                ->setDataType('input')
                ->setFieldType('text');
   }

   // Output JSON response
   header('Content-Type: application/json');
   echo json_encode($response->toArray());
   ?>