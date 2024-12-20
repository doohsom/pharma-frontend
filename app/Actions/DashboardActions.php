<?php

namespace App\Actions;

class DashboardActions{
    
    public function index($user)
    {
        $role = strtolower($user->role);
        $methodName = $role . 'Data';
        
        if (method_exists($this, $methodName)) {
            $data = $this->$methodName();
        } else {
            $data = 'der'; // or handle the case when the method doesn't exist
        }

        $viewPage = $role . '.dashboard';
        $dashboardView = 'roles.' . $role . '.dashboard';

        return [
            'data' => $data,
            'viewPage' => $viewPage,
            'dashboardView' => $dashboardView
        ];
    }
    public function der()
    {
        return 'der';
    }

    public function manufacturerData()
    {
        return 'manufacturer';
    }

    public function vendorData()
    {
        return 'vendor';
    }

    public function regulatorData()
    {
        return 'regulator';
    }

    public function logisticsData()
    {
        return 'logistics';
    }


}