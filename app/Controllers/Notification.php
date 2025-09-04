<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\Category_Model;
use App\Models\Subcategory_Model;
use App\Models\Notification_Model;
use App\Models\Language_Model;
use App\Models\Location_Model;

class Notification extends Controller {

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger) {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        $this->session = \Config\Services::session();
        $this->session->start();

        $this->helpers = helper('SystemSettings');
        date_default_timezone_set(get_system_timezone());
        $this->toDate = date('Y-m-d H:i:s');

        $this->Category_Model = new Category_Model();
        $this->Subcategory_Model = new Subcategory_Model();
        $this->Notification_Model = new Notification_Model();
        $this->Location_Model = new Location_Model();

        $this->db = \Config\Database::connect();
        $this->data['app_name'] = $this->db->table('tbl_settings')->where('type', 'app_name')->get()->getResult();
        $this->data['app_logo'] = $this->db->table('tbl_settings')->where('type', 'app_logo')->get()->getResult();
        $this->data['languages'] = $this->db->table('tbl_languages')->where('status', 1)->get()->getResult();

        $this->target_path = 'public/images/notification';
        
    }

    public function index() {
        if (!$this->session->get('isLoggedIn')) {
            return redirect('/');
        } else {
            $this->data['cate'] = $this->Category_Model->orderBy('id', 'DESC')->findAll();
            $this->data['location'] = $this->Location_Model->orderBy('id', 'DESC')->findAll();
            return view('notification', $this->data);
        }
    }

    public function store() {
        if (is_modification_allowed()) {
            $this->session->setFlashdata('error', DEMO_VERSION_MSG);
        } else {
            $image = $this->request->getFile('file');
            $language_id = $this->request->getVar('language');
            $location_id = $this->request->getVar('location_id') ?? 0;
            $newMsg = array();
            if ($image->getClientName() == '') {
                $data = [
                    'title' => $this->request->getVar('title'),
                    'message' => $this->request->getVar('message'),
                    'type' => $this->request->getVar('type'),
                    'category_id' => ($this->request->getVar('category_id')) ? $this->request->getVar('category_id') : 0,
                    'subcategory_id' => ($this->request->getVar('subcategory_id')) ? $this->request->getVar('subcategory_id') : 0,
                    'news_id' => ($this->request->getVar('news_id')) ? $this->request->getVar('news_id') : 0,
                    'date_sent' => $this->toDate,
                    'language_id' => $this->request->getVar('language'),
                    'location_id' => ($this->request->getVar('location_id')) ? $this->request->getVar('location_id') : 0,
                ];
                $fcmMsg = array(
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'title' => $this->request->getVar('title'),
                    'message' => $this->request->getVar('message'),
                    'type' => $this->request->getVar('type'),
                    'category_id' => ($this->request->getVar('category_id')) ? $this->request->getVar('category_id') : 0,
                    'subcategory_id' => ($this->request->getVar('subcategory_id')) ? $this->request->getVar('subcategory_id') : 0,
                    'news_id' => ($this->request->getVar('news_id')) ? $this->request->getVar('news_id') : 0,
                    'image' => null,
                    'language_id' => $this->request->getVar('language'),
                    'body' => $this->request->getVar('message'),
                    'sound' => 'default'
                );
                $this->Notification_Model->insert($data);
             
                    $this->send_notification($fcmMsg, $language_id, $location_id);
               
                
                $this->session->setFlashdata('success', 'Notification Sent Successfully.!');
            } else {
                if (!is_dir($this->target_path)) {
                    mkdir($this->target_path, 0777, TRUE);
                }

                $newName = microtime(TRUE) . '.' . $image->getClientExtension();
                if ($image->isValid() && !$image->hasMoved()) {
                    $image->move($this->target_path, $newName);
                }
                if (!$image->hasMoved()) {
                    $this->session->setFlashdata('error', 'Something went wrong, please try again.!');
                    return redirect('notification');
                } else {
                    $data = [
                        'title' => $this->request->getVar('title'),
                        'message' => $this->request->getVar('message'),
                        'type' => $this->request->getVar('type'),
                        'category_id' => ($this->request->getVar('category_id')) ? $this->request->getVar('category_id') : 0,
                        'subcategory_id' => ($this->request->getVar('subcategory_id')) ? $this->request->getVar('subcategory_id') : 0,
                        'news_id' => ($this->request->getVar('news_id')) ? $this->request->getVar('news_id') : 0,
                        'image' => $newName,
                        'date_sent' => $this->toDate,
                        'language_id' => $this->request->getVar('language'),
                        'location_id' => ($this->request->getVar('location_id')) ? $this->request->getVar('location_id') : 0,
                    ];

                    $fcmMsg = array(
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'title' => $this->request->getVar('title'),
                        'body' => $this->request->getVar('message'),
                        'message' => $this->request->getVar('message'),
                        'type' => $this->request->getVar('type'),
                        'category_id' => ($this->request->getVar('category_id')) ? $this->request->getVar('category_id') : 0,
                        'subcategory_id' => ($this->request->getVar('subcategory_id')) ? $this->request->getVar('subcategory_id') : 0,
                        'news_id' => ($this->request->getVar('news_id')) ? $this->request->getVar('news_id') : 0,
                        'image' => base_url() . '/' . $this->target_path . '/' . $newName,
                        'language_id' => $this->request->getVar('language'),
                        'sound' => 'default'
                    );
                }
                $this->Notification_Model->insert($data);
                $this->send_notification($fcmMsg,$language_id, $location_id);
                $this->session->setFlashdata('success', 'Notification Sent Successfully.!');
            } 
        }
        //print_r($fcmMsg);
        return redirect('notification');
    }

    public function delete() {
        if (is_modification_allowed()) {
            return $this->response->setJSON(FALSE);
        } else {
            $id = $this->request->getVar('id');
            $image_url = $this->request->getVar('image_url');
            if (file_exists($image_url)) {
                unlink($image_url);
            }
            $this->Notification_Model->where('id', $id)->delete();
            return $this->response->setJSON(TRUE);
        }
    }

    public function get_news_by_category($id) {
        if (!$this->session->get('isLoggedIn')) {
            return redirect('/');
        } else {
            $data = $this->db->table('tbl_news')->where('category_id', $id)->orderBy('id', 'DESC')->get()->getResult();

            $options = '<option value="">Select News</option>';
            foreach ($data as $option) {
                $options .= "<option value=" . $option->id . ">" . $option->title . "</option>";
            }
            echo $options;
        }
    }

    public function get_news_by_subcategory($id) {
        if (!$this->session->get('isLoggedIn')) {
            return redirect('/');
        } else {
            $data = $this->db->table('tbl_news')->where('subcategory_id', $id)->orderBy('id', 'DESC')->get()->getResult();

            $options = '<option value="">Select News</option>';
            foreach ($data as $option) {
                $options .= "<option value=" . $option->id . ">" . $option->title . "</option>";
            }
            echo $options;
        }
    }

    public function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        // Convert degrees to radians
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);
        
        // Radius of the Earth in kilometers
        $earthRadius = 6371; // You can also use 3959 for miles
        
        // Haversine formula
        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;
        $a = sin($dlat/2) * sin($dlat/2) + cos($lat1) * cos($lat2) * sin($dlon/2) * sin($dlon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;
       
        return $distance;
    }
    public function send_notification($fcmMsg, $language_id, $location_id) {
        if($location_id !== 0){
        $location = $this->db->table('tbl_location')->where('id', $location_id)->get()->getRow();
        $news_lat = $location->latitude;
        $news_long = $location->longitude;
        
        $data = $this->db->table('tbl_settings')->where('type', 'fcm_sever_key')->get()->getResult();
        define('API_ACCESS_KEY', $data[0]->message);

        $devicetoken1 = array();
        $filteredTokens = array();
        $devicetoken = $this->db->table('tbl_token')->where('language_id', $language_id)->get()->getResult();
        foreach ($devicetoken as $value) {
            $devicetoken1[] = $value->token;
            $device_lat = $value->latitude;   // Latitude of the device
            $device_long = $value->longitude; // Longitude of the device
            if(!empty($device_lat)){
                $distance = $this->calculateDistance($news_lat, $news_long, $device_lat, $device_long);
    
                // Check if the distance is less than 100 kilometers
                if ($distance < 10) {
                    // If the distance is less than 100 km, add the token to the filtered list
                    $filteredTokens[] = $value->token;
                }
            }
        }
}else{
     $data = $this->db->table('tbl_settings')->where('type', 'fcm_sever_key')->get()->getResult();
        define('API_ACCESS_KEY', $data[0]->message);

        $filteredTokens = array();
        $devicetoken = $this->db->table('tbl_token')->get()->getResult();
        foreach ($devicetoken as $value) {
            $filteredTokens[] = $value->token;
        }
}
        $registrationIDs_chunks = array_chunk($filteredTokens, 1000);
        $success = $failure = 0;

             foreach ($registrationIDs_chunks as $registrationIDs) {
            $fcmFields = array(
                'registration_ids' => $registrationIDs, // expects an array of ids
                'priority' => 'high',
                'notification' => $fcmMsg,
                'data' => $fcmMsg
            );

            $headers = array(
                'Authorization: key=' . API_ACCESS_KEY,
                'Content-Type: application/json'
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmFields));
            $result = curl_exec($ch);

            if ($result === FALSE) {
                die('Curl failed: ' . curl_error($ch));
            }
            //Now close the connection
            curl_close($ch);
        }
    }

}
