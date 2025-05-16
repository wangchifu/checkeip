<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use App\Models\StaffView;

class HomeController extends Controller
{
    public function index()
    {        
        $att = session('gsuite_login');
        $staffs = [];
        $teachers = [];
        $user = [];
        $check_users = [];
        if(!empty($att)){
            if($att['login']){
                $staffs = StaffView::where('staff_sid', session('gsuite_login.school_code'))       
                ->where('staff_kind','<>', '學生')     
                ->get();
                foreach($staffs as $staff) {
                    $teachers[$staff->staff_person_id]['name'] = $staff->staff_name;        
                    $teachers[$staff->staff_person_id]['title'] = $staff->staff_title; 	 
                    $teachers[$staff->staff_person_id]['gsuite_account'] = $staff->gsuite_account;
                }   
                
                if(is_file(storage_path('app/privacy/all.csv'))){
                    $csvFile = storage_path('app/privacy/all.csv');
                    if (($handle = fopen($csvFile, 'r')) !== false) {
                        while (($data = fgetcsv($handle, 1000, ',')) !== false) {                            
                            $data[1] = str_replace(' ', '', $data[1]);
                            $user[hash('sha256',$data[1])]['date'] = $data[0];
                            $user[hash('sha256',$data[1])]['pid'] = $data[1];
                            $user[hash('sha256',$data[1])]['agree'] = $data[4];
                        }
                        fclose($handle);
                    } else {
                        echo "無法開啟檔案";
                    }
                }
                foreach($teachers as $k => $v) {
                    if(isset($user[$k])){
                        $check_users[$k]['date'] = $user[$k]['date'];
                        
                        $first = substr($user[$k]['pid'], 0, 1);
                        $last3 = substr($user[$k]['pid'], -3);
                        $masked = str_repeat('*', strlen($user[$k]['pid']) - 4);
                    
                        $pid = $first . $masked . $last3;

                        $check_users[$k]['pid'] = $pid;
                        $check_users[$k]['agree'] = $user[$k]['agree'];
                        $check_users[$k]['name'] = $v['name'];
                        $check_users[$k]['title'] = $v['title'];
                        $check_users[$k]['gsuite_account'] = $v['gsuite_account'];
                    }
                }                
            }                            
        }
        
        
        $data = [
            'check_users' => $check_users            
        ];
        return view('index',$data);
    }

    public function glogin(){
        $key = rand(10000, 99999);
        session(['chaptcha' => $key]);

        $data = [

        ];
        return view('glogin',$data);
    }

    public function pic()
    {
        //$key = rand(10000, 99999);
        //session(['chaptcha' => $key]);
        $key = session('chaptcha');

        $back = rand(0, 9);
        /*
        $r = rand(0,255);
        $g = rand(0,255);
        $b = rand(0,255);
        */
        $r = 0;
        $g = 0;
        $b = 0;        

        //$cht = array(0=>"零",1=>"壹",2=>"貳",3=>"參",4=>"肆",5=>"伍",6=>"陸",7=>"柒",8=>"捌",9=>"玖");
        $cht = array(0 => "0", 1 => "1", 2 => "2", 3 => "3", 4 => "4", 5 => "5", 6 => "6", 7 => "7", 8 => "8", 9 => "9");
        $cht_key = "";
        for ($i = 0; $i < 5; $i++) $cht_key .= $cht[substr($key, $i, 1)];

        header("Content-type: image/gif");
        $im = imagecreatefromgif(asset('images/back.gif')) or die("無法建立GD圖片");
        $text_color = imagecolorallocate($im, $r, $g, $b);

        imagettftext($im, 25, 0, 5, 32, $text_color, public_path('font/AdobeGothicStd-Bold.otf'), $cht_key);
        imagegif($im);
        imagedestroy($im);
    }

    public function gauth(Request $request)
    {
        if ($request->input('chaptcha') != session('chaptcha')) {
            return back()->withErrors(['gsuite_error' => ['驗證碼錯誤！']]);
        }
        $username = explode('@', $request->input('username'));

        $data = array("email" => $username[0], "password" => $request->input('password'));
        $data_string = json_encode($data);
        $ch = curl_init(env('GSUITE_AUTH'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string)
            )
        );
        $result = curl_exec($ch);
        $obj = json_decode($result, true);        

        //學生禁止訪問
        if ($obj['success']) {

            if ($obj['kind'] == "學生") {
                return back()->withErrors(['errors' => ['學生禁止進入錯誤']]);
            }

            // 找出隸屬於哪一所學校 id 代號            
            $schools_id = config('ge.schools_id');
            $school_id = !isset($schools_id[$obj['code']]) ? 0 : $schools_id[$obj['code']];
                       
            $att['login'] = true;
            $att['name'] = $obj['name'];
            $att['school_code'] = $obj['code'];
            $att['school_name'] = $obj['school'];            
            $att['title'] = $obj['title'];            
            session(['gsuite_login' => $att]);            
            return redirect()->route('index');
        };        

        return back()->withErrors(['errors' => ['帳號密碼錯誤']]);
    }

    public function logout()
    {
        //Auth::logout();
        Session::flush();
        return redirect()->route('index');
    }

    public function upload_csv(Request $request)
    {
        $file = $request->file('csvFile');
        if ($file) {
            $filename = "all.csv";
            if(!is_dir(storage_path('app/privacy'))){
                mkdir(storage_path('app/privacy'), 0777, true);
            }
            $file->storeAs('privacy',$filename);
                        
            return redirect()->route('index');
        }
        
    }
    
}
