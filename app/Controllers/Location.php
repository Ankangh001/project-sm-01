<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\Location_Model;

class Location extends Controller
{

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        $this->session = \Config\Services::session();
        $this->session->start();

        $this->helpers = helper('SystemSettings');
        $this->Location_Model = new Location_Model();

        $this->db = \Config\Database::connect();
        $this->data['app_name'] = $this->db->table('tbl_settings')->where('type', 'app_name')->get()->getResult();
        $this->data['app_logo'] = $this->db->table('tbl_settings')->where('type', 'app_logo')->get()->getResult();
        $this->data['languages'] = $this->db->table('tbl_languages')->where('status', 1)->get()->getResult();
        
    }

    public function index()
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect('/');
        } else {
            return view('location', $this->data);
        }
    }

    public function store()
    {
        if (is_modification_allowed()) {
            $this->session->setFlashdata('error', DEMO_VERSION_MSG);
        } else {
            $this->Location_Model->insert([
                'location_name' => $this->request->getVar('location_name'),
                'latitude' => $this->request->getVar('latitude'),
                'longitude' => $this->request->getVar('longitude'),
            ]);
            $this->session->setFlashdata('success', 'Data inserted successfully.!');
        }
        return redirect('location');
    }

    public function update()
    {
        if (is_modification_allowed()) {
            $this->session->setFlashdata('error', DEMO_VERSION_MSG);
        } else {
            $id = $this->request->getVar('edit_id');
            $data = [
                'location_name' => $this->request->getVar('location_name'),
                'latitude' => $this->request->getVar('latitude'),
                'longitude' => $this->request->getVar('longitude'),
            ];
            $this->Location_Model->update($id, $data);
            $this->session->setFlashdata('success', 'Data Update successfully.!');
        }
        return redirect('location');
    }

    public function delete()
    {
        if (is_modification_allowed()) {
            return $this->response->setJSON(FALSE);
        } else {
            $id = $this->request->getVar('id');
            $this->Location_Model->where('id', $id)->delete();
            return $this->response->setJSON(TRUE);
        }
    }

}